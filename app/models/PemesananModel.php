<?php
require_once __DIR__ . '/../../config/database.php';

class PemesananModel
{
    /** Daftar sembako aktif untuk katalog pelanggan */
    public static function daftarSembako(): array
    {
        return Database::getConnection()
            ->query('SELECT id_sembako, nama_sembako, harga, unit FROM sembako WHERE aktif = 1 ORDER BY nama_sembako')
            ->fetchAll();
    }

    /**
     * Membuat pesanan baru.
     * $keranjang berbentuk [id_sembako => jumlah]
     * Mengembalikan id_pemesanan yang baru dibuat.
     */
    public static function buat(int $idPelanggan, array $keranjang): int
    {
        if (!$keranjang) {
            throw new InvalidArgumentException('Pesanan masih kosong');
        }

        $db = Database::getConnection();

        try {
            $db->beginTransaction();

            // Alamat kirim diambil dari profil pelanggan
            $st = $db->prepare('SELECT alamat FROM users WHERE id_pengguna = ? AND peran = ?');
            $st->execute([$idPelanggan, 'PELANGGAN']);
            $user = $st->fetch();
            if (!$user) {
                throw new InvalidArgumentException('Pelanggan tidak ditemukan');
            }
            if (empty($user['alamat'])) {
                throw new InvalidArgumentException('Alamat pelanggan belum diisi');
            }

            // Harga selalu diambil dari database, bukan dari input pengguna
            $stSembako = $db->prepare(
                'SELECT id_sembako, nama_sembako, harga, unit FROM sembako WHERE id_sembako = ? AND aktif = 1'
            );
            $baris = [];
            $total = 0;
            foreach ($keranjang as $idSembako => $jumlah) {
                $jumlah = (int)$jumlah;
                if ($jumlah < 1) {
                    throw new InvalidArgumentException('Jumlah harus lebih dari 0');
                }
                $stSembako->execute([(int)$idSembako]);
                $s = $stSembako->fetch();
                if (!$s) {
                    throw new InvalidArgumentException('Sembako tidak tersedia');
                }
                $subtotal = $s['harga'] * $jumlah;
                $total += $subtotal;
                $baris[] = [$s, $jumlah, $subtotal];
            }

            // Simpan pesanan induk
            $db->prepare(
                'INSERT INTO pemesanan (id_pelanggan, total, status, alamat_kirim) VALUES (?, ?, ?, ?)'
            )->execute([$idPelanggan, $total, 'MENUNGGU', $user['alamat']]);
            $idPemesanan = (int)$db->lastInsertId();

            // Simpan detail (nama, harga, unit disalin saat transaksi)
            $stDetail = $db->prepare(
                'INSERT INTO detail_pemesanan
                 (id_pemesanan, id_sembako, nama_sembako, harga, unit, jumlah, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($baris as [$s, $jumlah, $subtotal]) {
                $stDetail->execute([
                    $idPemesanan, $s['id_sembako'], $s['nama_sembako'],
                    $s['harga'], $s['unit'], $jumlah, $subtotal,
                ]);
            }

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