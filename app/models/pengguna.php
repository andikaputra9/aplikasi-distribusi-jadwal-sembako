
<?php
require_once __DIR__ . '/../../config/database.php';

/** Entity Pengguna: akses data akun pada tabel users. */
class Pengguna
{
    public static function findByUsername(string $username): ?array
    {
        $st = Database::getConnection()->prepare(
            'SELECT id_pengguna, nama, username, password, peran, no_hp, alamat, latitude
             FROM users WHERE username = ? LIMIT 1'
        );

        $st->execute([$username]);

        return $st->fetch() ?: null;
    }

    public static function verifikasiPassword(
        string $password,
        string $passwordHash
    ): bool {
        return password_verify($password, $passwordHash);
    }

    public static function cariById(int $id): ?array
    {
        $st = Database::getConnection()->prepare(
            'SELECT id_pengguna, nama, username, peran, no_hp, alamat, latitude
             FROM users WHERE id_pengguna = ? LIMIT 1'
        );

        $st->execute([$id]);

        return $st->fetch() ?: null;
    }

    public static function daftarKurir(): array
    {
        return Database::getConnection()
            ->query("SELECT id_pengguna, nama FROM users WHERE peran = 'KURIR' ORDER BY nama")
            ->fetchAll();
    }

    public static function getPeran(int $idPengguna): ?string
    {
        $st = Database::getConnection()->prepare(
            'SELECT peran FROM users WHERE id_pengguna = ?'
        );

        $st->execute([$idPengguna]);

        $peran = $st->fetchColumn();

        return $peran === false ? null : (string) $peran;
    }
}
