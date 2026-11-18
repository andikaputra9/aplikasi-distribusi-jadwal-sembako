<?php
require_once __DIR__ . '/../../config/database.php';

class JadwalModel
{
    /** Kurir yang bisa dipilih di form jadwal */
    public static function daftarKurir(): array
    {
        return Database::getConnection()
            ->query("SELECT id_pengguna, nama FROM users WHERE peran = 'KURIR' ORDER BY nama")
            ->fetchAll();
    }

    /**
     * Pesanan yang bisa dipilih: yang MENUNGGU,
     * ditambah yang sudah ada di jadwal ini (saat edit).
     */
    public static function pesananTersedia(?int $idJadwal = null): array
    {
        $st = Database::getConnection()->prepare(
            "SELECT p.id_pemesanan, p.total, p.status, u.nama AS nama_pelanggan,
                    (SELECT COUNT(*) FROM detail_pemesanan d WHERE d.id_pemesanan = p.id_pemesanan) AS jumlah_item
             FROM pemesanan p
             JOIN users u ON u.id_pengguna = p.id_pelanggan
             WHERE p.status = 'MENUNGGU'
                OR p.id_pemesanan IN (SELECT id_pemesanan FROM detail_jadwal WHERE id_jadwal = ?)
             ORDER BY p.tanggal_pesan"
        );
        $st->execute([$idJadwal ?? 0]);
        return $st->fetchAll();
    }

    /** Satu jadwal beserta daftar id pesanannya (untuk form edit) */
    public static function cari(int $idJadwal): ?array
    {
        $db = Database::getConnection();
        $st = $db->prepare('SELECT * FROM jadwal WHERE id_jadwal = ?');
        $st->execute([$idJadwal]);
        $j = $st->fetch();
        if (!$j) {
            return null;
        }
        $st = $db->prepare('SELECT id_pemesanan FROM detail_jadwal WHERE id_jadwal = ? ORDER BY urutan_antar');
        $st->execute([$idJadwal]);
        $j['pesanan'] = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
        return $j;
    }

    /**
     * Tambah jadwal (idJadwal null) atau ubah jadwal.
     * $data: tanggal (Y-m-d), sesi (PAGI/SIANG), id_kurir, pesanan (array id_pemesanan)
     * Mengembalikan id_jadwal.
     */
    public static function simpan(int $idPembuat, array $data, ?int $idJadwal = null): int
    {
        $tanggal = $data['tanggal'] ?? '';
        $sesi    = $data['sesi'] ?? '';
        $idKurir = (int)($data['id_kurir'] ?? 0);
        $pesanan = array_values(array_unique(array_map('intval', $data['pesanan'] ?? [])));

        // ---- Validasi input ----
        $d = DateTime::createFromFormat('Y-m-d', $tanggal);
        if (!$d || $d->format('Y-m-d') !== $tanggal) {
            throw new InvalidArgumentException('Tanggal tidak valid');
        }
        if ($idJadwal === null && $tanggal < date('Y-m-d')) {
            throw new InvalidArgumentException('Tanggal tidak boleh sebelum hari ini');
        }
        if (!in_array($sesi, ['PAGI', 'SIANG'], true)) {
            throw new InvalidArgumentException('Sesi wajib dipilih');
        }
        if (!$pesanan) {
            throw new InvalidArgumentException('Pilih minimal satu pesanan');
        }

        $db = Database::getConnection();

        try {
            $db->beginTransaction();

            // Kurir harus benar-benar user berperan KURIR
            $st = $db->prepare("SELECT 1 FROM users WHERE id_pengguna = ? AND peran = 'KURIR'");
            $st->execute([$idKurir]);
            if (!$st->fetchColumn()) {
                throw new InvalidArgumentException('Kurir wajib dipilih');
            }

            // Saat edit: kunci jadwal dan ambil pesanan lamanya
            $pesananLama = [];
            if ($idJadwal !== null) {
                $st = $db->prepare('SELECT status_jadwal FROM jadwal WHERE id_jadwal = ? FOR UPDATE');
                $st->execute([$idJadwal]);
                $status = $st->fetchColumn();
                if ($status === false) {
                    throw new InvalidArgumentException('Jadwal tidak ditemukan');
                }
                if ($status === 'SELESAI') {
                    throw new InvalidArgumentException('Jadwal yang sudah selesai tidak dapat diubah');
                }
                $st = $db->prepare('SELECT id_pemesanan FROM detail_jadwal WHERE id_jadwal = ?');
                $st->execute([$idJadwal]);
                $pesananLama = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
            }

            // Setiap pesanan harus MENUNGGU atau memang milik jadwal ini
            $st = $db->prepare('SELECT status FROM pemesanan WHERE id_pemesanan = ? FOR UPDATE');
            foreach ($pesanan as $idP) {
                $st->execute([$idP]);
                $statusP = $st->fetchColumn();
                if ($statusP === false) {
                    throw new InvalidArgumentException("Pesanan #$idP tidak ditemukan");
                }
                if ($statusP !== 'MENUNGGU' && !in_array($idP, $pesananLama, true)) {
                    throw new InvalidArgumentException("Pesanan #$idP sudah terjadwal di jadwal lain");
                }
            }

            $total    = count($pesanan);
            $estimasi = $total * 30; // menit

            if ($idJadwal === null) {
                $db->prepare(
                    'INSERT INTO jadwal (tanggal, sesi, id_kurir, total_pemesanan, estimasi_waktu, id_pembuat)
                     VALUES (?, ?, ?, ?, ?, ?)'
                )->execute([$tanggal, $sesi, $idKurir, $total, $estimasi, $idPembuat]);
                $idJadwal = (int)$db->lastInsertId();
            } else {
                $db->prepare(
                    'UPDATE jadwal SET tanggal = ?, sesi = ?, id_kurir = ?, total_pemesanan = ?, estimasi_waktu = ?
                     WHERE id_jadwal = ?'
                )->execute([$tanggal, $sesi, $idKurir, $total, $estimasi, $idJadwal]);

                // Pesanan yang dilepas kembali ke MENUNGGU
                $dilepas = array_diff($pesananLama, $pesanan);
                if ($dilepas) {
                    $in = implode(',', array_fill(0, count($dilepas), '?'));
                    $db->prepare("UPDATE pemesanan SET status = 'MENUNGGU' WHERE id_pemesanan IN ($in)")
                       ->execute(array_values($dilepas));
                }
                // detail_jadwal.id_pemesanan UNIQUE, jadi hapus dulu lalu isi ulang
                $db->prepare('DELETE FROM detail_jadwal WHERE id_jadwal = ?')->execute([$idJadwal]);
            }

            // Isi detail jadwal + tandai pesanan TERJADWAL
            $stDetail = $db->prepare(
                'INSERT INTO detail_jadwal (id_jadwal, id_pemesanan, urutan_antar) VALUES (?, ?, ?)'
            );
            $stStatus = $db->prepare("UPDATE pemesanan SET status = 'TERJADWAL' WHERE id_pemesanan = ?");
            foreach ($pesanan as $i => $idP) {
                $stDetail->execute([$idJadwal, $idP, $i + 1]);
                $stStatus->execute([$idP]);
            }

            $db->commit();
            return $idJadwal;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}