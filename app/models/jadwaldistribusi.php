
<?php
require_once __DIR__ . '/../../config/database.php';

/** Entity JadwalDistribusi: mengelola data induk jadwal distribusi. */
class JadwalDistribusi
{
    /**
     * Mengambil seluruh jadwal distribusi.
     */
    public static function semua(): array
    {
        return Database::getConnection()->query(
            "SELECT j.id_jadwal, j.tanggal, j.sesi, j.total_pemesanan,
                    j.estimasi_waktu, j.status_jadwal, u.nama AS nama_kurir
             FROM jadwal j
             JOIN users u ON u.id_pengguna = j.id_kurir
             ORDER BY j.status_jadwal ASC, j.tanggal ASC,
                      FIELD(j.sesi, 'PAGI', 'SIANG')"
        )->fetchAll();
    }

    /**
     * Mengambil jadwal milik kurir tertentu.
     */
    public static function untukKurir(int $idKurir): array
    {
        $st = Database::getConnection()->prepare(
            "SELECT id_jadwal, tanggal, sesi, total_pemesanan,
                    estimasi_waktu, status_jadwal
             FROM jadwal
             WHERE id_kurir = ?
             ORDER BY tanggal ASC, FIELD(sesi, 'PAGI', 'SIANG')"
        );

        $st->execute([$idKurir]);

        return $st->fetchAll();
    }

    /**
     * Mencari jadwal berdasarkan ID.
     */
    public static function cari(int $idJadwal): ?array
    {
        $st = Database::getConnection()->prepare(
            'SELECT * FROM jadwal WHERE id_jadwal = ?'
        );

        $st->execute([$idJadwal]);

        return $st->fetch() ?: null;
    }

    /**
     * Menyimpan data induk jadwal.
     * Relasi detail dan transaksi dikelola oleh Controller.
     */
    public static function saveJadwal(
        array $data,
        ?int $idJadwal = null
    ): int {
        $db = Database::getConnection();

        $jumlah = count($data['pesanan'] ?? []);
        $estimasi = $jumlah * 30;

        if ($idJadwal === null) {
            $st = $db->prepare(
                'INSERT INTO jadwal
                 (tanggal, sesi, id_kurir, total_pemesanan,
                  estimasi_waktu, id_pembuat)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );

            $st->execute([
                $data['tanggal'],
                $data['sesi'],
                $data['id_kurir'],
                $jumlah,
                $estimasi,
                $data['id_pembuat'],
            ]);

            return (int) $db->lastInsertId();
        }

        $st = $db->prepare(
            'UPDATE jadwal
             SET tanggal = ?, sesi = ?, id_kurir = ?,
                 total_pemesanan = ?, estimasi_waktu = ?
             WHERE id_jadwal = ?'
        );

        $st->execute([
            $data['tanggal'],
            $data['sesi'],
            $data['id_kurir'],
            $jumlah,
            $estimasi,
            $idJadwal,
        ]);

        return $idJadwal;
    }
}
