
<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/jadwaldistribusi.php';
require_once __DIR__ . '/../models/detailjadwal.php';
require_once __DIR__ . '/../models/pengguna.php';
require_once __DIR__ . '/../models/pemesanan.php';

class JadwalController
{
    /**
 * Memuat seluruh jadwal.
 */
public function muatDaftar(): array
{
    return JadwalDistribusi::semua();
}

/**
 * Memuat daftar jadwal berdasarkan kurir.
 */
public function muatDaftarKurir(int $idKurir): array
{
    return JadwalDistribusi::untukKurir($idKurir);
}

    /**
     * Memuat data untuk form tambah atau edit jadwal.
     */
    public function muatForm(?int $idJadwal = null): array
    {
        $jadwal = null;

        if ($idJadwal !== null) {
            $jadwal = JadwalDistribusi::cari($idJadwal);

            if (!$jadwal) {
                throw new InvalidArgumentException('Jadwal tidak ditemukan');
            }

            if ($jadwal['status_jadwal'] === 'SELESAI') {
                throw new InvalidArgumentException(
                    'Jadwal yang sudah selesai tidak dapat diubah'
                );
            }

            $detail = DetailJadwal::getByJadwal($idJadwal);

            $jadwal['pesanan'] = array_map(
                'intval',
                array_column($detail, 'id_pemesanan')
            );
        }

        return [
            'jadwal' => $jadwal,
            'kurir' => Pengguna::daftarKurir(),
            'pesanan' => Pemesanan::pesananTersedia($idJadwal),
        ];
    }

    /**
     * Memvalidasi data jadwal.
     */
    public function validasiJadwal(
        array $data,
        ?int $idJadwal = null
    ): void {
        $tanggal = $data['tanggal'] ?? '';
        $sesi = $data['sesi'] ?? '';
        $idKurir = (int) ($data['id_kurir'] ?? 0);

        $pesanan = array_values(array_unique(
            array_map('intval', $data['pesanan'] ?? [])
        ));

        $tanggalObj = DateTime::createFromFormat('!Y-m-d', $tanggal);

        if (
            !$tanggalObj ||
            $tanggalObj->format('Y-m-d') !== $tanggal
        ) {
            throw new InvalidArgumentException('Tanggal tidak valid');
        }

        if ($tanggal < date('Y-m-d')) {
    throw new InvalidArgumentException(
        'Tanggal tidak boleh sebelum hari ini'
    );
}

        if (!in_array($sesi, ['PAGI', 'SIANG'], true)) {
            throw new InvalidArgumentException('Sesi wajib dipilih');
        }

        if (!$pesanan || in_array(0, $pesanan, true)) {
            throw new InvalidArgumentException(
                'Pilih minimal satu pesanan yang valid'
            );
        }

        if (Pengguna::getPeran($idKurir) !== 'KURIR') {
            throw new InvalidArgumentException('Kurir wajib dipilih');
        }
    }

    /**
     * Menyimpan jadwal baru atau memperbarui jadwal.
     * Transaksi dikelola oleh Controller.
     */
    public function simpan(
        int $idPembuat,
        array $data,
        ?int $idJadwal = null
    ): int {
        $this->validasiJadwal($data, $idJadwal);

        $db = Database::getConnection();

        try {
            Database::beginTransaction();

            $pesanan = array_values(array_unique(
                array_map('intval', $data['pesanan'] ?? [])
            ));

            $pesananLama = [];

            if ($idJadwal !== null) {
                $st = $db->prepare(
                    'SELECT status_jadwal
                     FROM jadwal
                     WHERE id_jadwal = ?
                     FOR UPDATE'
                );

                $st->execute([$idJadwal]);
                $statusJadwal = $st->fetchColumn();

                if ($statusJadwal === false) {
                    throw new InvalidArgumentException(
                        'Jadwal tidak ditemukan'
                    );
                }

                if ($statusJadwal === 'SELESAI') {
                    throw new InvalidArgumentException(
                        'Jadwal yang sudah selesai tidak dapat diubah'
                    );
                }

                $st = $db->prepare(
                    'SELECT id_pemesanan
                     FROM detail_jadwal
                     WHERE id_jadwal = ?'
                );

                $st->execute([$idJadwal]);

                $pesananLama = array_map(
                    'intval',
                    $st->fetchAll(PDO::FETCH_COLUMN)
                );
            }

            foreach ($pesanan as $idPemesanan) {
                $st = $db->prepare(
                    'SELECT status
                     FROM pemesanan
                     WHERE id_pemesanan = ?
                     FOR UPDATE'
                );

                $st->execute([$idPemesanan]);
                $statusPesanan = $st->fetchColumn();

                if ($statusPesanan === false) {
                    throw new InvalidArgumentException(
                        "Pesanan #$idPemesanan tidak ditemukan"
                    );
                }

                if (
                    $statusPesanan !== 'MENUNGGU' &&
                    !in_array($idPemesanan, $pesananLama, true)
                ) {
                    throw new InvalidArgumentException(
                        "Pesanan #$idPemesanan sudah terjadwal di jadwal lain"
                    );
                }
            }

            $data['pesanan'] = $pesanan;
            $data['id_pembuat'] = $idPembuat;

            $idJadwal = JadwalDistribusi::saveJadwal(
                $data,
                $idJadwal
            );

            DetailJadwal::syncDetailDanStatus(
                $idJadwal,
                $pesanan,
                $pesananLama
            );

            Database::commit();

            return $idJadwal;

        } catch (Throwable $e) {
            if ($db->inTransaction()) {
    Database::rollBack();
}

            throw $e;
        }
    }
}
