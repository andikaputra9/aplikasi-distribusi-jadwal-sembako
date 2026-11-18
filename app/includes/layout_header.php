<?php
// Variabel yang diisi halaman sebelum include:
//   $judul   (string)       judul di topbar
//   $kembali (string|null)  URL tombol kembali
//   $logout  (bool)         tampilkan link Logout
?><!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($judul) ?> — Distribusi Sembako</title>
  <link rel="stylesheet" href="assets/css/beranda.css">
  <link rel="stylesheet" href="assets/css/halaman.css">
</head>
<body>
  <div class="app">
    <header class="topbar">
      <?php if (!empty($kembali)): ?>
        <a class="back" href="<?= e($kembali) ?>" aria-label="Kembali">&#8249;</a>
      <?php endif; ?>
      <h1><?= e($judul) ?></h1>
      <?php if (!empty($logout)): ?>
        <a href="logout.php">Logout</a>
      <?php endif; ?>
    </header>
    <main class="content">
      <?php if ($pesan = flash()): ?>
        <div class="ok"><?= e($pesan) ?></div>
      <?php endif; ?>
