# Aplikasi SIG Sekolah (Laravel + MySQL + OpenStreetMap)

Aplikasi Sistem Informasi Geografis (SIG) untuk pendataan sekolah beserta titik koordinatnya dengan visualisasi peta interaktif berbasis OpenStreetMap dan Leaflet.

---

## 1. Fitur Utama

- **Peta Publik (`/`)**:
  - Peta digital OpenStreetMap Leaflet interaktif.
  - Menampilkan sebaran seluruh sekolah di Kota Banjarmasin dan sekitarnya.
  - Filter jenjang pendidikan (Semua jenjang, SD, SMP, SMA, SMK).
  - Popup detail sekolah: Nama, NPSN, Jenjang, Status, Alamat, Kecamatan, Telepon, dan Koordinat.
  - Proteksi XSS (escape HTML pada popup peta).
- **Endpoint GeoJSON (`/data/sekolah`)**:
  - Format standar GeoJSON `FeatureCollection`.
  - Koordinat standar `[longitude, latitude]`.
  - Filter parameter `?jenjang=`.
- **Manajemen Data Sekolah / Admin (`/admin/sekolah`)**:
  - Daftar data sekolah dengan pencarian dinamis (`?q=`) berdasarkan nama, NPSN, atau kecamatan.
  - Paginasi data (10 data per halaman).
  - Formulir tambah dan ubah data dilengkapi peta interaktif (`map-picker`):
    - Klik pada peta untuk mengisi koordinat secara otomatis.
    - Drag marker untuk menyesuaikan lokasi.
    - Input koordinat manual yang langsung memusatkan marker pada peta.
  - Hapus data dengan konfirmasi.
  - Validasi ketat dan pesan kesalahan dalam Bahasa Indonesia.

---

## 2. Persyaratan Sistem

- PHP >= 8.3 (Diuji pada PHP 8.4)
- MySQL Server (misal melalui DBngin, Homebrew, atau XAMPP)
- Composer
- Node.js & npm

---

## 3. Langkah Instalasi

1. **Masuk ke folder proyek:**
   ```bash
   cd sig-sekolah
   ```

2. **Buat Database MySQL:**
   Pastikan MySQL berjalan, lalu buat database:
   ```sql
   CREATE DATABASE sig_sekolah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Konfigurasi Environment:**
   Salin `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   Pastikan konfigurasi database di `.env` sesuai:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sig_sekolah
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Instal Dependensi PHP:**
   ```bash
   composer install
   ```

5. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

6. **Migrasi Database & Seeder:**
   Jalankan migrasi tabel `sekolahs` dan data awal 8 sekolah contoh:
   ```bash
   php artisan migrate --seed
   ```

7. **Instal dan Build Dependensi Frontend (Tailwind CSS & Vite):**
   ```bash
   npm install
   npm run build
   ```

---

## 4. Menjalankan Aplikasi

Jalankan local development server Laravel:
```bash
php artisan serve
```

Buka browser dan akses URL berikut:
- **Peta Publik:** [http://127.0.0.1:8000/](http://127.0.0.1:8000/)
- **Kelola Data Sekolah (Admin):** [http://127.0.0.1:8000/admin/sekolah](http://127.0.0.1:8000/admin/sekolah)
- **Endpoint Data GeoJSON:** [http://127.0.0.1:8000/data/sekolah](http://127.0.0.1:8000/data/sekolah)

---

## 5. Menjalankan Pengujian Otomatis

Jalankan test suite menggunakan PHPUnit:
```bash
php artisan test
```

Semua 12 pengujian mencakup fungsionalitas CRUD, validasi input, GeoJSON koordinat, filter jenjang, dan respons HTTP.

---

## 6. Catatan Keamanan

> [!WARNING]
> Halaman admin pada modul praktikum ini sengaja dibuat tanpa autentikasi / login untuk keperluan pembelajaran praktikum SIG. Jangan mempublikasikan aplikasi ini ke internet publik sebelum menambahkan mekanisme autentikasi (misalnya Laravel Breeze, Fortify, atau middleware auth).
