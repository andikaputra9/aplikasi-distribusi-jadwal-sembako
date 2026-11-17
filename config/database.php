<?php
/**
 * Konfigurasi & Koneksi Database Gateway (PDO Singleton)
 * Sesuai Perancangan SDD Versi 1.0 (Bab 2.2 Tabel 2.2 & Bab 4.1 Tabel 4.1)
 * 
 * Penggunaan:
 *   require_once 'config/database.php';
 *   $db = Database::getConnection();
 */

class Database
{
    private static ?PDO $instance = null;

    // Parameter koneksi lokal (bawaan Laragon / XAMPP)
    private static string $host     = '127.0.0.1';
    private static int    $port     = 3306;
    private static string $dbname   = 'distribusi_sembako';
    private static string $username = 'root';
    private static string $password = '';
    private static string $charset  = 'utf8mb4';

    private function __construct()
    {
        // Singleton: cegah instansiasi langsung
    }

    private function __clone()
    {
        // Cegah kloning objek
    }

    /**
     * Mengambil instance tunggal koneksi PDO
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                self::$host,
                self::$port,
                self::$dbname,
                self::$charset
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, self::$username, self::$password, $options);
            } catch (PDOException $e) {
                error_log('Database connection error: ' . $e->getMessage());
                throw new PDOException('Gagal terhubung ke basis data: ' . $e->getMessage(), (int)$e->getCode());
            }
        }

        return self::$instance;
    }

    /**
     * Mengambil array konfigurasi koneksi (untuk keperluan skrip migrasi/tooling)
     */
    public static function getConfig(): array
    {
        return [
            'host'     => self::$host,
            'port'     => self::$port,
            'dbname'   => self::$dbname,
            'username' => self::$username,
            'password' => self::$password,
            'charset'  => self::$charset,
        ];
    }

    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    public static function rollBack(): bool
    {
        return self::getConnection()->rollBack();
    }
}
