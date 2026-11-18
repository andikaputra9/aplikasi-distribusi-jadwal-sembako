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
}