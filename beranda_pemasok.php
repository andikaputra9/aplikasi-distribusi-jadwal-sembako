<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/models/JadwalModel.php';

wajib_peran('PEMASOK');

$jadwal = JadwalModel::semua();
$pesan  = flash();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jadwal Distribusi — Distribusi Sembako</title>
  <link rel="stylesheet" href="assets/css/beranda.css">
  <link rel="stylesheet" href="assets/css/halaman.css">
</head>
<body>
  <div class="app">
    <header class="topbar">
      <h1>Jadwal Distribusi</h1>
      <a href="logout.php">Logout</a>
    </header>

    <main class="content">
      <?php if ($pesan): ?><div class="ok"><?= e($pesan) ?></div><?php endif; ?>
      <p class="lead">Daftar jadwal distribusi sembako</p>

      <?php foreach ($jadwal as $j): ?>
        <?php $selesai = $j['status_jadwal'] === 'SELESAI'; ?>
        <article class="card">
          <h3><?= e(tgl_id($j['tanggal'])) ?> — Sesi <?= e(label_sesi($j['sesi'])) ?></h3>
          <div class="meta">Kurir: <?= e($j['nama_kurir']) ?> | <?= (int)$j['total_pemesanan'] ?> pesanan</div>
          <div class="row">
            <span class="badge <?= $selesai ? 'b-done' : 'b-plan' ?>">
              <?= $selesai ? 'Selesai' : 'Direncanakan' ?>
            </span>
            <?php if (!$selesai): ?>
              <a class="btn-edit" href="jadwal_form.php?id=<?= (int)$j['id_jadwal'] ?>">Edit</a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </main>

    <a class="fab" href="jadwal_form.php">+ Tambah Jadwal</a>
  </div>
</body>
</html>