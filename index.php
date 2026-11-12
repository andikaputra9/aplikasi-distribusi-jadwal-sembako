<?php
session_start();

// Akun demo (sementara, nanti diganti query ke tabel users)
$akun = [
    'pemasok'   => ['password' => 'pemasok123',   'peran' => 'PEMASOK'],
    'pelanggan' => ['password' => 'pelanggan123', 'peran' => 'PELANGGAN'],
    'kurir'     => ['password' => 'kurir123',     'peran' => 'KURIR'],
];

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Token CSRF
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$error = null;
$berhasil = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Token CSRF tidak valid');
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (isset($akun[$username]) && $akun[$username]['password'] === $password) {
        $_SESSION['user'] = ['username' => $username, 'peran' => $akun[$username]['peran']];
        $berhasil = 'Login berhasil sebagai ' . $akun[$username]['peran'];
    } else {
        $error = 'Username atau password salah';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — Distribusi Sembako</title>
  <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
  <div class="app">
    <main class="login">
      <div class="logo">UD</div>
      <h2>Penjadwalan Distribusi Sembako</h2>
      <p>UD. Kuning Mas Palangka Raya</p>

      <form method="post" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

        <label for="username">Username</label>
        <input type="text" id="username" name="username"
               placeholder="Masukkan username" required
               value="<?= e($_POST['username'] ?? '') ?>">

        <label for="password">Password</label>
                <div class="pw">
          <input type="password" id="password" name="password" required>
          <button type="button" class="pw-toggle" data-toggle-pw="password"
                  aria-label="Tampilkan password" title="Tampilkan password">
            <svg class="ico-show" viewBox="0 0 24 24" width="22" height="22" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
            <svg class="ico-hide" viewBox="0 0 24 24" width="22" height="22" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19C5 19 1 12 1 12a18.5 18.5 0 0 1 5.06-5.94"/>
              <path d="M9.9 4.24A10.9 10.9 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
              <path d="M14.12 14.12A3 3 0 1 1 9.88 9.88"/>
              <line x1="1" y1="1" x2="23" y2="23"/>
            </svg>
          </button>
        </div>

        <?php if ($error): ?>
          <div class="err"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($berhasil): ?>
          <div class="ok"><?= e($berhasil) ?></div>
        <?php endif; ?>

        <button type="submit" class="btn btn-p">LOGIN</button>
      </form>

      <p class="note">Demo: pemasok/pemasok123 · pelanggan/pelanggan123 · kurir/kurir123</p>
    </main>
  </div>
  <script src="assets/js/index.js"></script>
</body>
</html>