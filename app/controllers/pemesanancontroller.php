
<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/pengguna.php';
require_once __DIR__ . '/../models/sembako.php';
require_once __DIR__ . '/../models/pemesanan.php';
require_once __DIR__ . '/../models/detailpemesanan.php';

/**
 * Controller Pemesanan:
 * mengatur proses katalog, keranjang, dan pengiriman pesanan.
 */
class PemesananController
{
    /**
     * Memuat katalog sembako aktif.
     * Data diindeks berdasarkan id_sembako.
     */
    public function muatKatalog(): array
    {
        $katalog = [];

        foreach (Sembako::getAllAktif() as $item) {
            $katalog[(int) $item['id_sembako']] = $item;
        }

        return $katalog;
    }

    /**
     * Memuat daftar pesanan milik pelanggan.
     */
    public function muatPesananSaya(int $idPelanggan): array
    {
        return Pemesanan::milikPelanggan($idPelanggan);
    }

    /**
     * Memuat data keranjang dan alamat pelanggan.
     */
    public function muatDataKeranjang(
        int $idPelanggan,
        array &$keranjang
    ): array {
        $katalog = $this->muatKatalog();

        $isi = [];
        $total = 0;

        foreach ($keranjang as $id => $jumlah) {
            if (!isset($katalog[$id])) {
                unset($keranjang[$id]);
                continue;
            }

            $s = $katalog[$id];
            $subtotal = $s['harga'] * $jumlah;
            $total += $subtotal;

            $isi[] = [
                'id' => $id,
                'nama' => $s['nama_sembako'],
                'unit' => $s['unit'],
                'harga' => $s['harga'],
                'jumlah' => $jumlah,
                'subtotal' => $subtotal,
            ];
        }

        $profil = Pengguna::cariById($idPelanggan);

        return [
            'katalog' => $katalog,
            'isi' => $isi,
            'total' => $total,
            'alamat' => $profil['alamat'] ?? '-',
        ];
    }

/**
 * Memvalidasi jumlah pesanan.
 */
public function validasiInput(int $jumlah): void
{
    if ($jumlah < 1 || $jumlah > 9999) {
        throw new InvalidArgumentException('Jumlah tidak valid');
    }
}

    /**
     * Menambahkan item ke keranjang.
     */
    public function tambahItem(
        array &$keranjang,
        int $idSembako,
        int $jumlah
    ): void {
        $this->validasiInput($jumlah);

if (!Sembako::findByIdAktif($idSembako)) {
    throw new InvalidArgumentException('Sembako tidak tersedia');
}

        $jumlahBaru = ($keranjang[$idSembako] ?? 0) + $jumlah;

        if ($jumlahBaru > 9999) {
            throw new InvalidArgumentException('Jumlah maksimal adalah 9999');
        }

        $keranjang[$idSembako] = $jumlahBaru;
    }

    /**
     * Menghapus item dari keranjang.
     */
    public function hapusItem(array &$keranjang, int $idSembako): void
    {
        unset($keranjang[$idSembako]);
    }

    /**
     * Menyiapkan data pesanan dan detailnya sebelum disimpan.
     */
    public function siapkanDataPesanan(
        int $idPelanggan,
        array $keranjang,
        string $status = 'MENUNGGU'
    ): array {
        if (!$keranjang) {
            throw new InvalidArgumentException('Pesanan masih kosong');
        }

        $pelanggan = Pengguna::cariById($idPelanggan);

        if (!$pelanggan || $pelanggan['peran'] !== 'PELANGGAN') {
            throw new InvalidArgumentException('Pelanggan tidak ditemukan');
        }

        if (empty($pelanggan['alamat'])) {
            throw new InvalidArgumentException('Alamat pelanggan belum diisi');
        }

        if ($status !== 'MENUNGGU') {
            throw new InvalidArgumentException('Status pesanan tidak valid');
        }

        $daftarItem = [];
        $total = 0;

        foreach ($keranjang as $idSembako => $jumlah) {
            $idSembako = (int) $idSembako;
            $jumlah = (int) $jumlah;

            $this->validasiInput($jumlah);

if (!Sembako::findByIdAktif($idSembako)) {
    throw new InvalidArgumentException('Sembako tidak tersedia');
}

            $item = DetailPemesanan::siapkanItem(
                $idSembako,
                $jumlah
            );

            $total += $item['subtotal'];
            $daftarItem[] = $item;
        }

        return [
            'data_pemesanan' => [
                'id_pelanggan' => $idPelanggan,
                'total' => $total,
                'status' => $status,
                'alamat_kirim' => $pelanggan['alamat'],
            ],
            'detail_pemesanan' => $daftarItem,
        ];
    }

    /**
     * Mengirim pesanan dengan transaksi database.
     */
    public function kirimPesanan(int $idPelanggan, array $keranjang): int
    {
        $db = Database::getConnection();

        try {
            $db->beginTransaction();

            $data = $this->siapkanDataPesanan(
                $idPelanggan,
                $keranjang,
                'MENUNGGU'
            );

            $idPemesanan = Pemesanan::insertPemesanan(
                $data['data_pemesanan']
            );

            DetailPemesanan::insertBatchDetail(
                $idPemesanan,
                $data['detail_pemesanan']
            );

            $db->commit();

            return $idPemesanan;

        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw $e;
        }
    }
}
