<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/controllers/jadwalcontroller.php';

wajib_peran('KURIR');

$controller = new JadwalController();

$daftarJadwal = $controller->muatDaftarKurir(
    (int) pengguna()['id']
);

$judul  = 'Jadwal Saya';
$logout = true;
include __DIR__ . '/app/includes/layout_header.php';
?>
<p class="lead">Jadwal distribusi yang ditugaskan kepada Anda</p>

<?php foreach ($daftarJadwal as $j): $selesai = $j['status_jadwal'] === 'SELESAI'; ?>
  <div class="card">
    <h3><?= e(tgl_id($j['tanggal'])) ?> — Sesi <?= e(label_sesi($j['sesi'])) ?></h3>
    <div class="meta"><?= (int)$j['total_pemesanan'] ?> pesanan | Estimasi <?= (int)$j['estimasi_waktu'] ?> menit</div>
    <span class="badge <?= $selesai ? 'b-done' : 'b-plan' ?>"><?= $selesai ? 'Selesai' : 'Direncanakan' ?></span>
  </div>
<?php endforeach; ?>
<?php if (!$daftarJadwal): ?><p class="meta">Belum ada jadwal untuk Anda.</p><?php endif; ?>

<p class="note">Mode lihat saja — pembaruan status pengantaran di luar lingkup versi ini</p>
<?php include __DIR__ . '/app/includes/layout_footer.php'; ?>
