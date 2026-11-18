<?php
session_start();
require_once __DIR__ . '/app/models/JadwalModel.php';

// Hanya pemasok yang boleh membuka halaman ini
if (($_SESSION['user']['peran'] ?? '') !== 'PEMASOK') {
    header('Location: index.php');
    exit;
}

$jadwal = JadwalModel::semua();

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function tgl_id($d) {
    $h = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $b = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $t = strtotime($d);
    return $h[date('w', $t)] . ', ' . date('j', $t) . ' ' . $b[(int)date('n', $t)] . ' ' . date('Y', $t);
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
        <?php $selesai = $j['status_jadwal'] === 'SELESAI'; ?>
          <article class="card">
            <h3><?= e(tgl_id($j['tanggal'])) ?> — Sesi <?= $j['sesi'] === 'PAGI' ? 'Pagi' : 'Siang' ?></h3>
            <div class="meta">Kurir: <?= e($j['nama_kurir']) ?> | <?= (int)$j['total_pemesanan'] ?> pesanan</div>
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