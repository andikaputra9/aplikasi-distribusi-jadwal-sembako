<?php
require_once __DIR__ . '/../../config/database.php';

/** Entity Sembako: data katalog barang yang dapat dipesan. */
class Sembako
{
    public static function getAllAktif(): array
    {
        return Database::getConnection()
            ->query('SELECT id_sembako, nama_sembako, harga, unit FROM sembako WHERE aktif = 1 ORDER BY nama_sembako')
            ->fetchAll();
    }

    public static function findByIdAktif(int $idSembako): ?array
    {
        $st = Database::getConnection()->prepare(
            'SELECT id_sembako, nama_sembako, harga, unit FROM sembako WHERE id_sembako = ? AND aktif = 1'
        );
        $st->execute([$idSembako]);
        return $st->fetch() ?: null;
    }
}
