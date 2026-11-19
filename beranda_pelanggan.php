<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/models/PemesananModel.php';

wajib_peran('PELANGGAN');

$saya    = pengguna();
$pesanan = PemesananModel::milikPelanggan((int)$saya['id']);

$judul  = 'Beranda';
$logout = true;
include __DIR__ . '/app/includes/layout_header.php';
?>
<h2 style="margin:4px 0">Halo, <?= e($saya['nama']) ?></h2>
<p class="lead">Pesan sembako dan pantau jadwal pengirimannya.</p>

<a class="btn btn-p" style="padding:22px" href="pemesanan.php">PESAN SEMBAKO</a>

<h2 style="margin:28px 0 12px;font-size:20px">Pesanan Saya</h2>
<?php foreach ($pesanan as $p): $terjadwal = $p['status'] === 'TERJADWAL'; ?>
  <div class="card">
    <h3>Pesanan <?= e(date('j M Y', strtotime($p['tanggal_pesan']))) ?></h3>
    <div class="meta"><?= rupiah($p['total']) ?> | <?= (int)$p['jumlah_item'] ?> item</div>
    <span class="badge <?= $terjadwal ? 'b-sched' : 'b-wait' ?>"><?= $terjadwal ? 'Terjadwal' : 'Menunggu' ?></span>
    <div class="meta" style="margin-top:6px">
      <?php if ($terjadwal && $p['tanggal_jadwal']): ?>
        Jadwal: <?= e(tgl_id($p['tanggal_jadwal'])) ?> — <?= e(label_sesi($p['sesi_jadwal'])) ?>
      <?php else: ?>
        Belum dijadwalkan
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
<?php if (!$pesanan): ?><p class="meta">Belum ada pesanan.</p><?php endif; ?>
<?php include __DIR__ . '/app/includes/layout_footer.php'; ?>
