<?php
session_start();

// Hanya pemasok yang boleh membuka halaman ini
if (($_SESSION['user']['peran'] ?? '') !== 'PEMASOK') {
    header('Location: index.php');
    exit;
}
// DATA DUMMY (sementara) — nanti diganti query ke tabel jadwal
$jadwal = [
    ['id' => 1, 'tanggal' => 'Senin, 5 Okt 2026',  'sesi' => 'Pagi',  'kurir' => 'Slamet', 'pesanan' => 4, 'status' => 'DIRENCANAKAN'],
    ['id' => 2, 'tanggal' => 'Senin, 5 Okt 2026',  'sesi' => 'Siang', 'kurir' => 'Joko',   'pesanan' => 3, 'status' => 'DIRENCANAKAN'],
    ['id' => 3, 'tanggal' => 'Selasa, 6 Okt 2026', 'sesi' => 'Pagi',  'kurir' => 'Slamet', 'pesanan' => 5, 'status' => 'DIRENCANAKAN'],
    ['id' => 4, 'tanggal' => 'Kamis, 1 Okt 2026',  'sesi' => 'Pagi',  'kurir' => 'Joko',   'pesanan' => 2, 'status' => 'SELESAI'],
];

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jadwal Distribusi — Distribusi Sembako</title>
  <link rel="stylesheet" href="assets/css/beranda.css">
</head>
<body>
  <div class="app">
    <header class="topbar">
      <h1>Jadwal Distribusi</h1>
      <a href="index.php">Logout</a>
    </header>

    <main class="content">
      <p class="lead">Daftar jadwal distribusi sembako</p>

      <?php foreach ($jadwal as $j): ?>
        <?php $selesai = $j['status'] === 'SELESAI'; ?>
        <article class="card">
          <h3><?= e($j['tanggal']) ?> — Sesi <?= e($j['sesi']) ?></h3>
          <div class="meta">Kurir: <?= e($j['kurir']) ?> | <?= (int)$j['pesanan'] ?> pesanan</div>
          <div class="row">
            <span class="badge <?= $selesai ? 'b-done' : 'b-plan' ?>">
              <?= $selesai ? 'Selesai' : 'Direncanakan' ?>
            </span>
            <a class="btn-edit" href="#">Edit</a>
          </div>
        </article>
      <?php endforeach; ?>
    </main>

    <a class="fab" href="#">+ Tambah Jadwal</a>
  </div>
</body>
</html>