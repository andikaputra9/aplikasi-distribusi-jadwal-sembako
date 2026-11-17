CREATE DATABASE IF NOT EXISTS `distribusi_sembako`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `distribusi_sembako`;

DROP TABLE IF EXISTS `detail_jadwal`;
DROP TABLE IF EXISTS `jadwal`;
DROP TABLE IF EXISTS `detail_pemesanan`;
DROP TABLE IF EXISTS `pemesanan`;
DROP TABLE IF EXISTS `sembako`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id_pengguna` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL COMMENT 'Nama lengkap pengguna atau nama toko pelanggan',
  `username` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Nama akun unik untuk login',
  `password` VARCHAR(255) NOT NULL COMMENT 'Password yang di-hash menggunakan bcrypt',
  `peran` ENUM('PEMASOK', 'PELANGGAN', 'KURIR') NOT NULL COMMENT 'Peran pengguna',
  `no_hp` VARCHAR(15) NOT NULL COMMENT 'Nomor telepon/HP yang dapat dihubungi',
  `alamat` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Alamat pengguna; alamat kirim bawaan pelanggan',
  `latitude` DECIMAL(10, 8) NULL DEFAULT NULL COMMENT 'Titik koordinat lintang lokasi pelanggan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `sembako` (
  `id_sembako` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_sembako` VARCHAR(100) NOT NULL COMMENT 'Nama item sembako',
  `harga` DECIMAL(12, 2) NOT NULL COMMENT 'Harga per satuan dalam Rupiah (>= 0)',
  `unit` VARCHAR(20) NOT NULL COMMENT 'Satuan penjualan (kg, liter, sak, dus, pak)',
  `aktif` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status tampil di katalog (1 = aktif, 0 = nonaktif)',
  CONSTRAINT `chk_sembako_harga` CHECK (`harga` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `pemesanan` (
  `id_pemesanan` INT AUTO_INCREMENT PRIMARY KEY,
  `id_pelanggan` INT NOT NULL COMMENT 'ID pelanggan pembuat pesanan',
  `tanggal_pesan` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pesanan dikirim',
  `total` DECIMAL(12, 2) NOT NULL COMMENT 'Total harga seluruh item pada pesanan (Rupiah)',
  `status` ENUM('MENUNGGU', 'TERJADWAL') NOT NULL DEFAULT 'MENUNGGU' COMMENT 'Status pesanan',
  `alamat_kirim` VARCHAR(255) NOT NULL COMMENT 'Alamat pengiriman pesanan',
  CONSTRAINT `fk_pemesanan_pelanggan` FOREIGN KEY (`id_pelanggan`)
    REFERENCES `users` (`id_pengguna`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `detail_pemesanan` (
  `id_detail_pemesanan` INT AUTO_INCREMENT PRIMARY KEY,
  `id_pemesanan` INT NOT NULL COMMENT 'ID pesanan induk',
  `id_sembako` INT NOT NULL COMMENT 'ID sembako yang dipesan',
  `nama_sembako` VARCHAR(100) NOT NULL COMMENT 'Salinan nama sembako saat transaksi',
  `harga` DECIMAL(12, 2) NOT NULL COMMENT 'Salinan harga satuan saat transaksi',
  `unit` VARCHAR(20) NOT NULL COMMENT 'Salinan satuan sembako',
  `jumlah` INT NOT NULL COMMENT 'Kuantitas sembako yang dipesan (> 0)',
  `subtotal` DECIMAL(12, 2) NOT NULL COMMENT 'Subtotal (harga x jumlah)',
  CONSTRAINT `chk_detail_jumlah` CHECK (`jumlah` > 0),
  CONSTRAINT `fk_detail_pemesanan_induk` FOREIGN KEY (`id_pemesanan`)
    REFERENCES `pemesanan` (`id_pemesanan`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_detail_pemesanan_sembako` FOREIGN KEY (`id_sembako`)
    REFERENCES `sembako` (`id_sembako`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `jadwal` (
  `id_jadwal` INT AUTO_INCREMENT PRIMARY KEY,
  `tanggal` DATE NOT NULL COMMENT 'Tanggal pelaksanaan distribusi sembako',
  `sesi` ENUM('PAGI', 'SIANG') NOT NULL COMMENT 'Sesi pengantaran (PAGI / SIANG)',
  `id_kurir` INT NOT NULL COMMENT 'ID pengguna peran KURIR yang bertugas',
  `total_pemesanan` INT NOT NULL COMMENT 'Jumlah pesanan dalam jadwal',
  `estimasi_waktu` INT NOT NULL COMMENT 'Estimasi durasi pengantaran (menit = total_pemesanan x 30)',
  `status_jadwal` ENUM('DIRENCANAKAN', 'SELESAI') NOT NULL DEFAULT 'DIRENCANAKAN' COMMENT 'Status jadwal',
  `id_pembuat` INT NOT NULL COMMENT 'ID pengguna peran PEMASOK pembuat jadwal',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu jadwal pertama kali disimpan',
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu jadwal terakhir diperbarui',
  CONSTRAINT `fk_jadwal_kurir` FOREIGN KEY (`id_kurir`)
    REFERENCES `users` (`id_pengguna`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_jadwal_pembuat` FOREIGN KEY (`id_pembuat`)
    REFERENCES `users` (`id_pengguna`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `detail_jadwal` (
  `id_detail_jadwal` INT AUTO_INCREMENT PRIMARY KEY,
  `id_jadwal` INT NOT NULL COMMENT 'ID jadwal induk',
  `id_pemesanan` INT NOT NULL UNIQUE COMMENT 'ID pesanan yang dijadwalkan (unik)',
  `urutan_antar` INT NOT NULL COMMENT 'Nomor urut pengantaran pesanan',
  `status_distribusi` ENUM('BELUM_DIANTAR', 'SAMPAI') NOT NULL DEFAULT 'BELUM_DIANTAR' COMMENT 'Status distribusi',
  CONSTRAINT `fk_detail_jadwal_induk` FOREIGN KEY (`id_jadwal`)
    REFERENCES `jadwal` (`id_jadwal`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_detail_jadwal_pemesanan` FOREIGN KEY (`id_pemesanan`)
    REFERENCES `pemesanan` (`id_pemesanan`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
