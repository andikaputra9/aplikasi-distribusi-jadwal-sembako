# Aplikasi Distribusi Jadwal Sembako

Aplikasi Distribusi Jadwal Sembako merupakan aplikasi berbasis Android yang dikembangkan untuk membantu proses pengelolaan dan penjadwalan distribusi sembako secara terstruktur, terorganisir, dan efisien.

Proyek ini bertujuan untuk mengatasi permasalahan proses distribusi sembako yang masih dilakukan secara manual, khususnya dalam pengelolaan data pemasok, pelanggan, jadwal pengiriman, serta koordinasi antara pihak-pihak yang terlibat dalam proses distribusi.

Dengan adanya aplikasi ini, proses distribusi diharapkan dapat berjalan lebih terencana, meminimalkan kesalahan pencatatan, serta memudahkan setiap pengguna dalam memperoleh informasi terkait jadwal dan status distribusi sembako.

## Anggota Tim

Berikut merupakan anggota tim pengembang aplikasi Distribusi Jadwal Sembako:

| Nama | Peran dan Tanggung Jawab |
|---|---|
| Andika | Project Manager & Fullstack Developer |
| Yuspa | Frontend Developer |
| Falih | Frontend Developer |
| Abi | Backend Developer |
| Arif | Backend Developer |

## Teknologi yang Digunakan

Proyek ini menggunakan beberapa teknologi berikut:

| Teknologi | Kegunaan |
|---|---|
| PHP | Bahasa pemrograman untuk pengembangan backend dan logika aplikasi. |
| MySQL / MariaDB | Sistem manajemen basis data untuk menyimpan informasi aplikasi. |
| HTML | Membuat struktur halaman antarmuka. |
| CSS | Mengatur tampilan dan desain antarmuka. |
| Bootstrap | Framework CSS untuk membantu pengembangan antarmuka responsif. |
| Android | Platform target aplikasi. |

## Dokumentasi Proyek

Repository ini juga menyediakan dokumentasi teknis sebagai bagian dari proses perancangan dan pengujian perangkat lunak.

| Dokumentasi | Keterangan |
|---|---|
| SDD (Software Design Document) | Berisi dokumentasi perancangan sistem, arsitektur perangkat lunak, desain database, desain antarmuka, serta spesifikasi teknis aplikasi. |
| STD (Software Test Document) | Berisi dokumentasi perencanaan dan pelaksanaan pengujian perangkat lunak untuk memastikan sistem berjalan sesuai kebutuhan. |

Lokasi dokumentasi:

- `docs/SDD/` — Dokumentasi desain perangkat lunak.
- `docs/STD/` — Dokumentasi pengujian perangkat lunak.

## Instalasi dan Konfigurasi

Ikuti langkah-langkah berikut untuk menyiapkan proyek di lingkungan lokal.

### 1. Clone Repository

Unduh repository ke perangkat lokal menggunakan Git:

```bash
git clone <URL_REPOSITORY>
```

Masuk ke direktori proyek:

```bash
cd aplikasi-distribusi-jadwal-sembako
```

### 2. Persiapkan Lingkungan Pengembangan

Pastikan PHP, MySQL/MariaDB, dan web server telah terpasang dan berjalan.

Alternatif yang dapat digunakan adalah XAMPP untuk lingkungan pengembangan lokal.

### 3. Buat Database

- Jalankan MySQL melalui XAMPP atau layanan database yang digunakan.
- Buka phpMyAdmin.
- Buat database baru sesuai nama database yang ditentukan dalam konfigurasi proyek.
- Import skrip database dari direktori `database/`.

### 4. Konfigurasi Database

Sesuaikan konfigurasi koneksi database pada file konfigurasi proyek.

Parameter yang perlu disesuaikan:

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Jalankan Aplikasi

Jalankan aplikasi melalui web server lokal sesuai struktur dan konfigurasi implementasi proyek.

Petunjuk menjalankan aplikasi secara lengkap akan ditambahkan setelah proses pengembangan selesai.

## Catatan

<details>
<summary><strong>Informasi Tahapan Pengembangan</strong></summary>

Proyek ini dikembangkan secara bertahap melalui beberapa tahapan:

- 

</details>

Status pengembangan akan diperbarui mengikuti progres pengerjaan masing-masing anggota tim.
