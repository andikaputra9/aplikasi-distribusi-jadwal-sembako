<?php
require_once __DIR__ . '/../../config/database.php';

/** Entity DetailPemesanan: rincian item dan subtotal pada sebuah pesanan. */
class DetailPemesanan
{
    /** Membentuk snapshot item dari data katalog; harga tidak dipercaya dari input form. */
    public static function siapkanItem(int $idSembako, int $jumlah): array
    {
        if ($jumlah < 1) {
            throw new InvalidArgumentException('Jumlah harus lebih dari 0');
        }
        require_once __DIR__ . '/sembako.php';
        $barang = Sembako::findByIdAktif($idSembako);
        if (!$barang) {
            throw new InvalidArgumentException('Sembako tidak tersedia');
        }
        $barang['jumlah'] = $jumlah;
        $barang['subtotal'] = (float) $barang['harga'] * $jumlah;
        return $barang;
    }

    /** Menyimpan beberapa detail; pemanggil mengelola transaksi database. */
    public static function insertBatchDetail(int $idPemesanan, array $daftarItem): void
    {
        $db = Database::getConnection();
        $st = $db->prepare(
            'INSERT INTO detail_pemesanan
             (id_pemesanan, id_sembako, nama_sembako, harga, unit, jumlah, subtotal)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($daftarItem as $item) {
            $st->execute([
                $idPemesanan,
                (int) $item['id_sembako'],
                $item['nama_sembako'],
                $item['harga'],
                $item['unit'],
                (int) $item['jumlah'],
                $item['subtotal'],
            ]);
        }
    }

    public static function getByPemesanan(int $idPemesanan): array
    {
        $st = Database::getConnection()->prepare(
            'SELECT * FROM detail_pemesanan WHERE id_pemesanan = ? ORDER BY id_detail_pemesanan'
        );
        $st->execute([$idPemesanan]);
        return $st->fetchAll();
    }
}
