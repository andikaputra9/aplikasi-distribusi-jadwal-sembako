<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/models/JadwalModel.php';

wajib_peran('PEMASOK');

// ?id=... -> ubah jadwal, tanpa id -> tambah jadwal
$idJadwal = (int)($_GET['id'] ?? 0);
$edit     = $idJadwal > 0;
$today    = date('Y-m-d');

$j = ['tanggal' => '', 'sesi' => 'PAGI', 'id_kurir' => 0, 'pesanan' => []];

if ($edit) {
    $ada = JadwalModel::cari($idJadwal);
    if (!$ada) {
        flash('Jadwal tidak ditemukan');
        header('Location: beranda_pemasok.php');
        exit;
    }
    if ($ada['status_jadwal'] === 'SELESAI') {
        flash('Jadwal yang sudah selesai tidak dapat diubah');
        header('Location: beranda_pemasok.php');
        exit;
    }
    $j = [
        'tanggal'  => $ada['tanggal'],
        'sesi'     => $ada['sesi'],
        'id_kurir' => (int)$ada['id_kurir'],
        'pesanan'  => $ada['pesanan'],
    ];
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $j = [
        'tanggal'  => $_POST['tanggal'] ?? '',
        'sesi'     => $_POST['sesi'] ?? '',
        'id_kurir' => (int)($_POST['id_kurir'] ?? 0),
        'pesanan'  => array_map('intval', (array)($_POST['id_pemesanan'] ?? [])),
    ];
    try {
        JadwalModel::simpan((int)pengguna()['id'], $j, $edit ? $idJadwal : null);
        flash('Jadwal berhasil disimpan');
        header('Location: beranda_pemasok.php');
        exit;
    } catch (InvalidArgumentException $ex) {
        $error = $ex->getMessage();            // kesalahan input -> tampilkan ke pengguna
    } catch (Throwable $ex) {
        error_log('jadwal_form: ' . $ex->getMessage());
        $error = 'Terjadi kesalahan pada server, coba lagi';
    }
}

$daftarKurir  = JadwalModel::daftarKurir();
$daftarPesanan = JadwalModel::pesananTersedia($edit ? $idJadwal : null);

$judul   = $edit ? 'Ubah Jadwal' : 'Tambah Jadwal';
$kembali = 'beranda_pemasok.php';
include __DIR__ . '/app/includes/layout_header.php';
?>
<form method="post">
  <?= csrf_field() ?>

  <label for="tanggal">Tanggal distribusi</label>
  <input type="date" id="tanggal" name="tanggal" required
         <?= $edit ? '' : 'min="' . e($today) . '"' ?>
         value="<?= e($j['tanggal']) ?>">

  <label for="sesi">Sesi</label>
  <select id="sesi" name="sesi">
    <option value="PAGI"  <?= $j['sesi'] === 'PAGI'  ? 'selected' : '' ?>>Pagi</option>
    <option value="SIANG" <?= $j['sesi'] === 'SIANG' ? 'selected' : '' ?>>Siang</option>
  </select>

  <label for="id_kurir">Kurir</label>
  <select id="id_kurir" name="id_kurir" required>
    <option value="">Pilih kurir</option>
    <?php foreach ($daftarKurir as $k): ?>
      <option value="<?= (int)$k['id_pengguna'] ?>"
              <?= (int)$j['id_kurir'] === (int)$k['id_pengguna'] ? 'selected' : '' ?>>
        <?= e($k['nama']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <label>Pilih pesanan yang dijadwalkan</label>
  <?php foreach ($daftarPesanan as $p): ?>
    <label class="card check">
      <input type="checkbox" name="id_pemesanan[]" value="<?= (int)$p['id_pemesanan'] ?>"
             <?= in_array((int)$p['id_pemesanan'], $j['pesanan'], true) ? 'checked' : '' ?>>
      <span class="info">
        <b><?= e($p['nama_pelanggan']) ?></b>
        <div class="meta"><?= (int)$p['jumlah_item'] ?> item | <?= rupiah($p['total']) ?></div>
      </span>
      <span class="badge b-gray"><?= $p['status'] === 'TERJADWAL' ? 'Terjadwal' : 'Menunggu' ?></span>
    </label>
  <?php endforeach; ?>
  <?php if (!$daftarPesanan): ?>
    <p class="meta">Belum ada pesanan yang menunggu untuk dijadwalkan.</p>
  <?php endif; ?>

  <p>Total pesanan dipilih: <span id="total_dipilih"><?= count($j['pesanan']) ?></span></p>

  <?php if ($error): ?><div class="err"><?= e($error) ?></div><?php endif; ?>

  <div class="btns">
    <a class="btn btn-o" href="beranda_pemasok.php">BATAL</a>
    <button type="submit" class="btn btn-p"><?= $edit ? 'PERBARUI' : 'SIMPAN' ?></button>
  </div>
</form>
<script>
  // hitung pesanan yang dicentang (tampilan saja; validasi sebenarnya di server)
  var cek = document.querySelectorAll('input[name="id_pemesanan[]"]');
  var out = document.getElementById('total_dipilih');
  cek.forEach(function (c) {
    c.addEventListener('change', function () {
      out.textContent = document.querySelectorAll('input[name="id_pemesanan[]"]:checked').length;
    });
  });
</script>
<?php include __DIR__ . '/app/includes/layout_footer.php'; ?>
