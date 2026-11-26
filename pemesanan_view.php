<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/controllers/pemesanancontroller.php';

wajib_peran('PELANGGAN');

$controller = new pemesanancontroller();

$idPelanggan = (int) pengguna()['id'];
$_SESSION['keranjang'] = $_SESSION['keranjang'] ?? [];

$keranjang = &$_SESSION['keranjang'];

$dataKeranjang = $controller->muatDataKeranjang(
    $idPelanggan,
    $keranjang
);

$katalog = $dataKeranjang['katalog'];
$isi = $dataKeranjang['isi'];
$total = $dataKeranjang['total'];
$alamat = $dataKeranjang['alamat'];

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();

    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        try {
            $controller->tambahItem(
                $keranjang,
                (int) ($_POST['id_sembako'] ?? 0),
                (int) ($_POST['jumlah'] ?? 0)
            );

            header('Location: pemesanan_view.php');
            exit;

        } catch (InvalidArgumentException $ex) {
            $error = $ex->getMessage();
        }

    } elseif ($aksi === 'hapus') {
        $controller->hapusItem(
            $keranjang,
            (int) ($_POST['id_sembako'] ?? 0)
        );

        header('Location: pemesanan_view.php');
        exit;

    } elseif ($aksi === 'kirim') {
        try {
            $controller->kirimPesanan($idPelanggan, $keranjang);

            $_SESSION['keranjang'] = [];

            flash('Pesanan berhasil dikirim');

            header('Location: beranda_pelanggan_view.php');
            exit;

        } catch (InvalidArgumentException $ex) {
            $error = $ex->getMessage();

        } catch (Throwable $ex) {
            error_log('pemesanan: ' . $ex->getMessage());
            $error = 'Terjadi kesalahan pada server, coba lagi';
        }
    }
}



$judul = 'Pesan Sembako';
$kembali = 'beranda_pelanggan_view.php';

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
<p class="meta">Dikirim ke alamat: <?= e($alamat) ?></p>

<?php if ($error): ?><div class="err"><?= e($error) ?></div><?php endif; ?>

<form method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="aksi" value="kirim">
  <button type="submit" class="btn btn-p">KIRIM PESANAN</button>
</form>
<?php include __DIR__ . '/app/includes/layout_footer.php'; ?>
