<?php
require_once __DIR__ . '/../../config/database.php';

/** Entity Pemesanan: mengelola data pesanan induk. */
class Pemesanan
{
    /**
     * Menyimpan data pesanan induk.
     */
    public static function insertPemesanan(array $data): int
    {
        $db = Database::getConnection();

        $st = $db->prepare(
            'INSERT INTO pemesanan
             (id_pelanggan, total, status, alamat_kirim)
             VALUES (?, ?, ?, ?)'
        );

        $st->execute([
            (int) $data['id_pelanggan'],
            (float) $data['total'],
            $data['status'] ?? 'MENUNGGU',
            $data['alamat_kirim'],
        ]);

        return (int) $db->lastInsertId();
    }

    /**
     * Mencari pesanan berdasarkan ID.
     */
    public static function findById(int $idPemesanan): ?array
    {
        $st = Database::getConnection()->prepare(
            'SELECT * FROM pemesanan WHERE id_pemesanan = ?'
        );

        $st->execute([$idPemesanan]);

        return $st->fetch() ?: null;
    }

    /**
     * Mengambil riwayat pesanan milik pelanggan.
     */
    public static function milikPelanggan(int $idPelanggan): array
    {
        $st = Database::getConnection()->prepare(
            "SELECT
                p.id_pemesanan,
                p.tanggal_pesan,
                p.total,
                p.status,
                (
                    SELECT COUNT(*)
                    FROM detail_pemesanan dp
                    WHERE dp.id_pemesanan = p.id_pemesanan
                ) AS jumlah_item,
                j.tanggal AS tanggal_jadwal,
                j.sesi AS sesi_jadwal
             FROM pemesanan p
             LEFT JOIN detail_jadwal dj
                ON dj.id_pemesanan = p.id_pemesanan
             LEFT JOIN jadwal j
                ON j.id_jadwal = dj.id_jadwal
             WHERE p.id_pelanggan = ?
             ORDER BY p.tanggal_pesan DESC"
        );

        $st->execute([$idPelanggan]);

        return $st->fetchAll();
    }

    /**
     * Mengambil pesanan yang tersedia untuk dijadwalkan.
     */
    public static function pesananTersedia(?int $idJadwal = null): array
    {
        $st = Database::getConnection()->prepare(
            "SELECT
                p.id_pemesanan,
                p.total,
                p.status,
                u.nama AS nama_pelanggan,
                (
                    SELECT COUNT(*)
                    FROM detail_pemesanan dp
                    WHERE dp.id_pemesanan = p.id_pemesanan
                ) AS jumlah_item
             FROM pemesanan p
             JOIN users u
                ON u.id_pengguna = p.id_pelanggan
             WHERE p.status = 'MENUNGGU'
                OR p.id_pemesanan IN (
                    SELECT id_pemesanan
                    FROM detail_jadwal
                    WHERE id_jadwal = ?
                )
             ORDER BY p.tanggal_pesan"
        );

        $st->execute([$idJadwal ?? 0]);

        return $st->fetchAll();
    }
}
