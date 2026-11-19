<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/models/PemesananModel.php';
require_once __DIR__ . '/app/models/UserModel.php';

wajib_peran('PELANGGAN');

$idPelanggan = (int)pengguna()['id'];
$_SESSION['keranjang'] = $_SESSION['keranjang'] ?? [];   // [id_sembako => jumlah]

// Katalog dari database, diindeks dengan id_sembako
$katalog = [];
foreach (PemesananModel::daftarSembako() as $s) {
    $katalog[(int)$s['id_sembako']] = $s;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $id     = (int)($_POST['id_sembako'] ?? 0);
        $jumlah = (int)($_POST['jumlah'] ?? 0);
        if (!isset($katalog[$id])) {
            $error = 'Sembako tidak tersedia';
        } elseif ($jumlah < 1 || $jumlah > 9999) {
            $error = 'Jumlah tidak valid';
        } else {
            $_SESSION['keranjang'][$id] = ($_SESSION['keranjang'][$id] ?? 0) + $jumlah;
            header('Location: pemesanan.php');
            exit;
        }
    } elseif ($aksi === 'hapus') {
        unset($_SESSION['keranjang'][(int)($_POST['id_sembako'] ?? 0)]);
        header('Location: pemesanan.php');
        exit;
    } elseif ($aksi === 'kirim') {
        try {
            PemesananModel::buat($idPelanggan, $_SESSION['keranjang']);
            $_SESSION['keranjang'] = [];
            flash('Pesanan berhasil dikirim');
            header('Location: beranda_pelanggan.php');
            exit;
        } catch (InvalidArgumentException $ex) {
            $error = $ex->getMessage();
        } catch (Throwable $ex) {
            error_log('pemesanan: ' . $ex->getMessage());
            $error = 'Terjadi kesalahan pada server, coba lagi';
        }
    }
}

// Susun isi keranjang dari harga di database; item yang sudah tidak aktif dibuang
$isi   = [];
$total = 0;
foreach ($_SESSION['keranjang'] as $id => $jumlah) {
    if (!isset($katalog[$id])) {
        unset($_SESSION['keranjang'][$id]);
        continue;
    }
    $s        = $katalog[$id];
    $subtotal = $s['harga'] * $jumlah;
    $total   += $subtotal;
    $isi[]    = ['id' => $id, 'nama' => $s['nama_sembako'], 'unit' => $s['unit'],
                 'harga' => $s['harga'], 'jumlah' => $jumlah, 'subtotal' => $subtotal];
}

$profil = UserModel::cariById($idPelanggan);

$judul   = 'Pesan Sembako';
$kembali = 'beranda_pelanggan.php';
include __DIR__ . '/app/includes/layout_header.php';
?>
<form method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="aksi" value="tambah">

  <label for="id_sembako">Pilih sembako</label>
  <select id="id_sembako" name="id_sembako">
    <?php foreach ($katalog as $id => $s): ?>
      <option value="<?= $id ?>"><?= e($s['nama_sembako']) ?> — <?= rupiah($s['harga']) ?>/<?= e($s['unit']) ?></option>
    <?php endforeach; ?>
  </select>

  <div class="add-row">
    <div>
      <label for="jumlah">Jumlah</label>
      <input type="number" id="jumlah" name="jumlah" min="1" max="9999" value="1">
    </div>
    <button type="submit" class="btn btn-p">TAMBAH</button>
  </div>
</form>

<h3 style="margin:24px 0 10px">Daftar Pesanan</h3>
<?php foreach ($isi as $it): ?>
  <div class="card">
    <div class="row"><b><?= e($it['nama']) ?></b><span><?= rupiah($it['subtotal']) ?></span></div>
    <div class="row">
      <span class="meta"><?= (int)$it['jumlah'] ?> x <?= rupiah($it['harga']) ?></span>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="hapus">
        <input type="hidden" name="id_sembako" value="<?= (int)$it['id'] ?>">
        <button class="del">Hapus</button>
      </form>
    </div>
  </div>
<?php endforeach; ?>
<?php if (!$isi): ?><p class="meta">Belum ada item.</p><?php endif; ?>

<div class="total"><span>Total</span><span><?= rupiah($total) ?></span></div>
<p class="meta">Dikirim ke alamat: <?= e($profil['alamat'] ?? '-') ?></p>

<?php if ($error): ?><div class="err"><?= e($error) ?></div><?php endif; ?>

<form method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="aksi" value="kirim">
  <button type="submit" class="btn btn-p">KIRIM PESANAN</button>
</form>
<?php include __DIR__ . '/app/includes/layout_footer.php'; ?>
