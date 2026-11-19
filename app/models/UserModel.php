<?php
require_once __DIR__ . '/../../config/database.php';

class UserModel {
    public static function cariByUsername(string $username): ?array {
        $st = Database::getConnection()->prepare(
            'SELECT id_pengguna, nama, username, password, peran FROM users WHERE username = ?'
        );
        $st->execute([$username]);
        return $st->fetch() ?: null;
    }

    public static function cariById(int $id): ?array {
        $st = Database::getConnection()->prepare(
            'SELECT id_pengguna, nama, username, peran, no_hp, alamat FROM users WHERE id_pengguna = ?'
        );
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
}