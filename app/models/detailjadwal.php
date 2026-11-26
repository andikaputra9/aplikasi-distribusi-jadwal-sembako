<?php
require_once __DIR__ . '/../../config/database.php';

/** Entity DetailJadwal: relasi jadwal-pesanan dan sinkronisasi status pesanan. */
class DetailJadwal
{
    /** Sinkronkan detail jadwal dan status pesanan; pemanggil mengelola transaksi. */
    public static function syncDetailDanStatus(int $idJadwal, array $listIdPemesanan, array $idPesananLama = []): void
    {
        $db = Database::getConnection();
        $listIdPemesanan = array_values(array_unique(array_map('intval', $listIdPemesanan)));
        $idPesananLama = array_values(array_unique(array_map('intval', $idPesananLama)));

        $dilepas = array_values(array_diff($idPesananLama, $listIdPemesanan));
        if ($dilepas) {
            $in = implode(',', array_fill(0, count($dilepas), '?'));
            $db->prepare("UPDATE pemesanan SET status = 'MENUNGGU' WHERE id_pemesanan IN ($in)")
                ->execute($dilepas);
        }

        $db->prepare('DELETE FROM detail_jadwal WHERE id_jadwal = ?')->execute([$idJadwal]);
        $insert = $db->prepare(
            'INSERT INTO detail_jadwal (id_jadwal, id_pemesanan, urutan_antar) VALUES (?, ?, ?)'
        );
        $update = $db->prepare("UPDATE pemesanan SET status = 'TERJADWAL' WHERE id_pemesanan = ?");
        foreach ($listIdPemesanan as $index => $idPemesanan) {
            $insert->execute([$idJadwal, $idPemesanan, $index + 1]);
            $update->execute([$idPemesanan]);
        }
    }

    public static function getByJadwal(int $idJadwal): array
    {
        $st = Database::getConnection()->prepare(
            'SELECT dj.*, p.total, p.alamat_kirim, u.nama AS nama_pelanggan
             FROM detail_jadwal dj
             JOIN pemesanan p ON p.id_pemesanan = dj.id_pemesanan
             JOIN users u ON u.id_pengguna = p.id_pelanggan
             WHERE dj.id_jadwal = ? ORDER BY dj.urutan_antar'
        );
        $st->execute([$idJadwal]);
        return $st->fetchAll();
    }
}
