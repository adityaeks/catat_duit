# Catat Duit 💰

Aplikasi pencatatan keuangan dan manajemen anggaran (*budgeting*) modern berbasis web dengan pendekatan **Mobile-First** dan dukungan **Progressive Web App (PWA)**. Dirancang khusus untuk mempermudah pencatatan transaksi harian dan monitoring batas kuota pengeluaran dengan rentang tanggal fleksibel (custom date range).

---

## 📱 Fitur Utama

1. **Desain Mobile-First & App-Like**:
   - Tampilan responsif yang dioptimalkan untuk layar ponsel (dengan *Bottom Navigation Bar*).
   - Tombol cepat **(+)** untuk mencatat transaksi secara instan dari halaman manapun.
   - Format otomatis Rupiah saat mengetik nominal (`150000` -> `Rp 150.000`).
   - Ikon vektor SVG modern dan bersih (**Lucide**) tanpa emoji berlebih.

2. **Sistem Anggaran Fleksibel (Custom Date Range Budgeting)**:
   - Tentukan batas pengeluaran dari **Tanggal Mulai** s/d **Tanggal Selesai** (contoh: siklus gajian *25 Januari – 24 Februari*, anggaran liburan, atau mingguan).
   - Pilihan cepat (*Preset*): **Bulan Ini**, **Siklus Gajian (25–24)**, dan **7 Hari**.
   - Cakupan anggaran bisa **Semua Pengeluaran (Global)** atau **Spesifik Kategori** (misal: khusus *Makanan & Minuman*).
   - **Progress Bar Real-time**:
     - 🟢 **Hijau**: Pemakaian aman (< 75%).
     - 🟡 **Kuning**: Waspada mendekati batas kuota (75% – 99%).
     - 🔴 **Merah**: Melebihi anggaran / *Overbudget* (≥ 100%).
   - **Rekomendasi Pintar**: Rekomendasi batas aman belanja harian berdasarkan sisa kuota uang dan sisa hari.

3. **Pencatatan & Riwayat Transaksi**:
   - Filter transaksi lengkap berdasarkan: Tipe (*Semua / Pemasukan / Pengeluaran*), Kategori, Rentang Tanggal, dan Pencarian teks.
   - Ringkasan total pemasukan, pengeluaran, dan selisih bersih hasil filter secara real-time.
   - Edit dan Hapus transaksi langsung via modal.

4. **Kategori Finansial**:
   - Tersedia 14 kategori standar bawaan (Makanan & Minuman, Transportasi, Belanja Harian, Tagihan, Gaji, Bonus, dll.).
   - Tambah dan sesuaikan kategori dengan pilihan ikon SVG dan palet warna.

5. **Autentikasi Pengguna Tunggal & Sesi Permanen**:
   - Proteksi login akun tunggal (tanpa formulir register publik).
   - Sesi login diatur bertahan selama **1 tahun** (*Persistent Remember Me*), sehingga tidak perlu login ulang setiap membuka aplikasi.

---

## 📲 Catatan & Panduan PWA (Progressive Web App)

Aplikasi ini sudah mendukung standar PWA penuh, memungkinkan aplikasi dipasang langsung di layar utama smartphone (*Home Screen*) dan dibuka dalam mode *standalone* (layar penuh tanpa address bar browser).

### 1. Cara Pasang di Smartphone

#### Android (Google Chrome / Edge / Brave):
1. Buka URL aplikasi di browser Chrome HP (misal: `http://[IP-Komputer]:8000` atau domain aplikasi Anda).
2. Banner **"Pasang Aplikasi Catat Duit"** akan muncul di bagian atas layar. Klik tombol **Pasang**.
3. Jika banner tidak muncul, tekan ikon **Menu titik tiga (⋮)** di pojok kanan atas browser > pilih **"Install app"** atau **"Tambahkan ke Layar Utama" (Add to Home screen)**.
4. Ikon **Catat Duit** akan muncul di menu dan beranda HP Anda.

#### iOS / iPhone & iPad (Safari):
1. Buka URL aplikasi di browser **Safari**.
2. Tekan tombol **Share** (ikon kotak dengan panah mengarah ke atas di bar bawah).
3. Gulir ke bawah dan pilih **"Add to Home Screen" (Tambahkan ke Layar Utama)**.
4. Tekan **Add** di pojok kanan atas.

---

### 2. Bagaimana Jika Ada Update Aplikasi? Apakah Perlu Reinstall?

> **TIDAK PERLU REINSTALL SAMA SEKALI!**

PWA bekerja layaknya aplikasi web modern yang selalu terhubung ke server:
* **Pembaruan Otomatis**: Setiap kali Anda membuka aplikasi dari beranda HP, Service Worker (`sw.js`) di latar belakang akan otomatis memeriksa ke server apakah ada perubahan kode/file baru.
* **Menerapkan Update**: Anda cukup menutup aplikasi (swipe close dari recent apps) lalu membukanya kembali, atau lakukan *pull-to-refresh* (tarik layar ke bawah untuk memuat ulang).
* **Manajemen Versi Cache (Untuk Developer)**:
  Jika Anda melakukan perubahan besar pada kode CSS/JS/Blade, Anda cukup memperbarui versi cache di file [`public/sw.js`](public/sw.js):
  ```javascript
  // Ubah versi dari v1 ke v2 saat rilis update baru
  const CACHE_NAME = 'catatduit-v2';
  ```
  Browser di perangkat HP pengguna akan otomatis membuang cache lama dan memuat versi terbaru tanpa perlu memasang ulang ikon aplikasi.

### 3. Fitur Bagikan Resi / Pembayaran ke Catat Duit (Web Share Target)

Aplikasi ini mendukung penerimaan resi atau teks transaksi dari aplikasi perbankan/e-wallet (*BCA, Mandiri Livin, GoPay, OVO, DANA, ShopeePay, Tokopedia, dll.*):
1. Di aplikasi bank/e-wallet Anda, klik tombol **"Bagikan / Share Resi"**.
2. Pilih **Catat Duit** dari daftar aplikasi (*Share Sheet*).
3. Catat Duit akan otomatis terbuka dan melakukan **Smart Parser**:
   - Nominal otomatis terekstrak dari format mata uang (misal `Rp 45.000` -> `45000`).
   - Kategori otomatis terdeteksi dari kata kunci (misal: *kopi/resto/makan* -> Kategori **Makanan & Minuman**).
   - Catatan/keterangan merchant terisi otomatis.
4. Anda cukup klik **"Simpan Transaksi"** (selesai dalam 1 detik!).

---

## 🛠️ Stack Teknologi

- **Backend**: Laravel 12 / 13 (PHP 8.3+)
- **Database**: MySQL
- **Frontend**: Blade Template + Tailwind CSS v4 + Vanilla JS
- **Asset Bundler**: Vite
- **Icons**: Lucide SVG Icons Component
- **PWA**: Web App Manifest (`manifest.json`) + Service Worker (`sw.js`)
- **Code Formatter & Testing**: Laravel Pint & PHPUnit (12 Tests, 32 Assertions)

---

## 🚀 Panduan Instalasi Lokal (Laragon / XAMPP)

### 1. Clone & Install Dependensi
```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Konfigurasi Environment & Database
Salin `.env.example` ke `.env` dan sesuaikan koneksi database MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=catat_duit
DB_USERNAME=root
DB_PASSWORD=

# Konfigurasi sesi permanen 1 tahun
SESSION_DRIVER=database
SESSION_LIFETIME=525600
```

### 3. Generate Key & Migrasi Database
```bash
php artisan key:generate
php artisan migrate --seed
```

*Seeder akan otomatis membuat 1 akun pengguna default dan 14 kategori finansial bawaan.*

### 4. Akun Login Default
* **Email**: `admin@catatduit.com`
* **Password**: `password`

### 5. Menjalankan Server Pengembangan
Jalankan perintah berikut:
```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Vite Asset Watcher (atau jalankan npm run build untuk produksi)
npm run build
```

Buka browser di **`http://localhost:8000`** atau domain lokal Laragon Anda.

---

## 🧪 Pengujian & Standarisasi Kode

Menjalankan pengujian fitur otomatis (PHPUnit):
```bash
php artisan test --compact
```

Memformat kode sesuai standar PSR-12 / Laravel (Pint):
```bash
vendor/bin/pint --format agent
```

---

## 📄 Lisensi

Aplikasi ini bersifat *open-source* di bawah lisensi [MIT](LICENSE).
