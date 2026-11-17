USE `distribusi_sembako`;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `detail_jadwal`;
TRUNCATE TABLE `jadwal`;
TRUNCATE TABLE `detail_pemesanan`;
TRUNCATE TABLE `pemesanan`;
TRUNCATE TABLE `sembako`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;


-- 1. Seed Tabel users
-- Password hash menggunakan bcrypt (PHP password_hash(..., PASSWORD_BCRYPT))
-- Akun demo:
-- - pemasok   : pemasok123   (Peran: PEMASOK)
-- - pelanggan : pelanggan123 (Peran: PELANGGAN)
-- - pelanggan2: pelanggan123 (Peran: PELANGGAN)
-- - kurir     : kurir123     (Peran: KURIR)
-- - joko      : kurir123     (Peran: KURIR)

INSERT INTO `users` (`id_pengguna`, `nama`, `username`, `password`, `peran`, `no_hp`, `alamat`, `latitude`) VALUES
(1, 'Pengelola UD. Kuning Mas', 'pemasok', '$2y$12$v0ah3/hl78PHjpfOpT65w.xMP7cSMuhcc0GTsNiWRvBCsIuhsiNfS', 'PEMASOK', '081234567890', 'Jl. RTA Milono Km. 4 Palangka Raya', -2.216667),
(2, 'Toko Berkah Ibu (Pelanggan 1)', 'pelanggan', '$2y$12$Di/AsS7.7OAo6UIouDWcVucYK9ZkW1HrJ5iJO5iwrPbt4PGaPJimG', 'PELANGGAN', '082155551234', 'Jl. Yos Sudarso No. 12 Palangka Raya', -2.209123),
(3, 'Toko Sembako Sejahtera (Pelanggan 2)', 'pelanggan2', '$2y$12$Di/AsS7.7OAo6UIouDWcVucYK9ZkW1HrJ5iJO5iwrPbt4PGaPJimG', 'PELANGGAN', '082177778888', 'Jl. Tjilik Riwut Km. 2 Palangka Raya', -2.201456),
(4, 'Slamet', 'kurir', '$2y$12$ElXgn1YKRfV55w53qFn.ouhbTVJsdS5B2zYRT2fT0qi.4ovtmm3Sy', 'KURIR', '085211223344', 'Jl. G. Obos Palangka Raya', -2.223456),
(5, 'Joko', 'joko', '$2y$12$ElXgn1YKRfV55w53qFn.ouhbTVJsdS5B2zYRT2fT0qi.4ovtmm3Sy', 'KURIR', '085255667788', 'Jl. Seth Adji Palangka Raya', -2.231234);


INSERT INTO `sembako` (`id_sembako`, `nama_sembako`, `harga`, `unit`, `aktif`) VALUES
(1, 'Beras Ramos 5 kg', 75000.00, 'sak', 1),
(2, 'Beras Pandan Wangi 10 kg', 145000.00, 'sak', 1),
(3, 'Minyak Goreng 2 Liter', 38000.00, 'pouch', 1),
(4, 'Gula Pasir 1 kg', 17500.00, 'kg', 1),
(5, 'Tepung Terigu Segitiga Biru 1 kg', 13000.00, 'kg', 1),
(6, 'Telur Ayam 1 Rak (30 butir)', 55000.00, 'rak', 1),
(7, 'Mi Instan Goreng 1 Dus (40 bks)', 115000.00, 'dus', 1),
(8, 'Susu Kental Manis 370 gr', 12000.00, 'kaleng', 1);


INSERT INTO `pemesanan` (`id_pemesanan`, `id_pelanggan`, `tanggal_pesan`, `total`, `status`, `alamat_kirim`) VALUES
(1, 2, '2026-10-01 09:30:00', 113000.00, 'TERJADWAL', 'Jl. Yos Sudarso No. 12 Palangka Raya'),
(2, 3, '2026-10-01 10:15:00', 200000.00, 'TERJADWAL', 'Jl. Tjilik Riwut Km. 2 Palangka Raya'),
(3, 2, '2026-10-02 08:00:00', 73000.00,  'MENUNGGU',  'Jl. Yos Sudarso No. 12 Palangka Raya'),
(4, 3, '2026-10-02 11:30:00', 150000.00, 'MENUNGGU',  'Jl. Tjilik Riwut Km. 2 Palangka Raya');

INSERT INTO `detail_pemesanan` (`id_detail_pemesanan`, `id_pemesanan`, `id_sembako`, `nama_sembako`, `harga`, `unit`, `jumlah`, `subtotal`) VALUES
(1, 1, 1, 'Beras Ramos 5 kg', 75000.00, 'sak', 1, 75000.00),
(2, 1, 3, 'Minyak Goreng 2 Liter', 38000.00, 'pouch', 1, 38000.00),
(3, 2, 2, 'Beras Pandan Wangi 10 kg', 145000.00, 'sak', 1, 145000.00),
(4, 2, 6, 'Telur Ayam 1 Rak (30 butir)', 55000.00, 'rak', 1, 55000.00),
(5, 3, 4, 'Gula Pasir 1 kg', 17500.00, 'kg', 2, 35000.00),
(6, 3, 3, 'Minyak Goreng 2 Liter', 38000.00, 'pouch', 1, 38000.00),
(7, 4, 1, 'Beras Ramos 5 kg', 75000.00, 'sak', 2, 150000.00);


INSERT INTO `jadwal` (`id_jadwal`, `tanggal`, `sesi`, `id_kurir`, `total_pemesanan`, `estimasi_waktu`, `status_jadwal`, `id_pembuat`) VALUES
(1, '2026-10-05', 'PAGI', 4, 2, 60, 'DIRENCANAKAN', 1);

INSERT INTO `detail_jadwal` (`id_detail_jadwal`, `id_jadwal`, `id_pemesanan`, `urutan_antar`, `status_distribusi`) VALUES
(1, 1, 1, 1, 'BELUM_DIANTAR'),
(2, 1, 2, 2, 'BELUM_DIANTAR');
