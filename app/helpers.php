<?php
/**
 * Cukup: require_once __DIR__ . '/app/helpers.php';  (session otomatis dimulai)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

/** Escape output HTML */
function e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** <input hidden> berisi token CSRF, taruh di dalam setiap <form method="post"> */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

/** Validasi token CSRF untuk request POST */
function cek_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Token CSRF tidak valid');
    }
}

/** Halaman beranda sesuai peran */
function beranda_untuk(string $peran): string
{
    $peta = [
        'PEMASOK'   => 'beranda_pemasok_view.php',
        'PELANGGAN' => 'beranda_pelanggan_view.php',
        'KURIR'     => 'beranda_kurir_view.php',
    ];
    return $peta[$peran] ?? 'login_view.php';
}

/** Pengguna yang sedang login (atau null) */
function pengguna(): ?array
{
    if (empty($_SESSION['user'])) {
        return null;
    }

    $user = $_SESSION['user'];

    $user['peran'] = $_SESSION['peran']
        ?? ($user['peran'] ?? null);

    return $user;
}

/** Batasi halaman untuk satu peran; selain itu dialihkan */
function wajib_peran(string $peran): void
{
    $u = pengguna();
    if (!$u) {
        header('Location: login_view.php');
        exit;
    }
    if ($u['peran'] !== $peran) {
        header('Location: ' . beranda_untuk($u['peran']));
        exit;
    }
}

/** Pesan sekali tampil: flash('teks') untuk menyimpan, flash() untuk mengambil */
function flash(?string $pesan = null): ?string
{
    if ($pesan !== null) {
        $_SESSION['flash'] = $pesan;
        return null;
    }
    $x = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $x;
}

/** 75000 -> "Rp 75.000" */
function rupiah($n): string
{
    return 'Rp ' . number_format((float)$n, 0, ',', '.');
}

/** 2026-10-05 -> "Senin, 5 Okt 2026" */
function tgl_id(string $tanggal): string
{
    $h = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $b = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($tanggal);
    return $h[date('w', $t)] . ', ' . date('j', $t) . ' ' . $b[(int)date('n', $t)] . ' ' . date('Y', $t);
}

/** 'PAGI' -> 'Pagi' */
function label_sesi(string $sesi): string
{
    return $sesi === 'PAGI' ? 'Pagi' : 'Siang';
}
