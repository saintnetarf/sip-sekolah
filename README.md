# Aplikasi SIG Sekolah (Laravel + MySQL + OpenStreetMap + RBAC)

Aplikasi Sistem Informasi Geografis (SIG) untuk pendataan sekolah beserta titik koordinatnya dengan visualisasi peta interaktif berbasis OpenStreetMap dan Leaflet, dilengkapi dengan fitur Autentikasi dan Kontrol Akses Berbasis Peran (*Role-Based Access Control* / RBAC).

---

## 1. Fitur Utama

- **Menu Peta Interaktif Publik (Praktikum 19-27)**:
  - **Peta Sekolah (`/`)**: Marker sebaran sekolah, popup profil sekolah lengkap, dan filter jenjang (SD, SMP, SMA, SMK).
  - **Pencarian (`/pencarian`)**: Pencarian instan (nama, NPSN, kecamatan) dengan *debounce* 300 ms, filter jenjang, dan panel hasil interaktif yang dapat diklik untuk terbang ke lokasi sekolah.
  - **Klaster (`/klaster`)**: Pengelompokan marker berdekatan menjadi klaster interaktif menggunakan pustaka `Leaflet.markercluster`.
  - **Peta Panas (`/peta-panas`)**: Visualisasi kepadatan sekolah atau populasi siswa dengan pengaturan slider radius dinamis (10-60 px) menggunakan pustaka `Leaflet.heat`.
  - **Wilayah (`/wilayah`)**: Estimasi area sebaran sekolah per kecamatan menggunakan algoritma Convex Hull (*Andrew's Monotone Chain*), dilengkapi gradasi warna relatif terhadap kecamatan dengan sekolah terbanyak.
  - **Rute Perjalanan (`/rute`)**: Perencanaan rute optimal antar 2 hingga 10 sekolah menggunakan OSRM API, dilengkapi panah penunjuk arah (`Leaflet.polylineDecorator`), penanda bernomor, estimasi jarak dan durasi, serta jalur cadangan otomatis (*fallback* garis lurus Haversine).
  - **Cek Lokasi (`/cek-alamat`)**: Titik klik interaktif untuk reverse geocoding alamat via Nominatim API dan penghitungan otomatis 3 sekolah terdekat beserta jarak garis lurus dan garis penghubung.
- **Endpoint GeoJSON Publik (`/data/sekolah`, `/data/wilayah`)**:
  - `/data/sekolah`: Format standar GeoJSON `FeatureCollection` dengan koordinat `[longitude, latitude]`. Properti publik memuat detail profil sekolah tanpa mengekspos data pribadi seperti email dan website.
  - `/data/wilayah`: Format GeoJSON Polygon poligon sebaran sekolah per kecamatan dengan properti `kecamatan`, `jumlah_sekolah`, dan `jumlah_siswa`.
- **Autentikasi Manual Bawaan Laravel (`/login`, `/logout`)**:
  - Halaman login mandiri dengan desain modern.
  - Proteksi *brute-force* dengan rate limiting (`throttle:5,1`).
  - Session regeneration dan CSRF protection.
- **Dashboard Berbasis Peran (`/dashboard`)**:
  - **Admin**: Statistik jumlah akun per peran (*admin*, *dinas*, *sekolah*).
  - **Dinas**: Jumlah total sekolah terdata dalam sistem.
  - **Sekolah**: Informasi detail sekolah dan bilah persentase kelengkapan profil sekolah (dihitung dari 8 kolom profil).
- **Manajemen Pengguna (`/admin/users`)**:
  - Khusus peran **Admin**.
  - CRUD pengguna (tambah, ubah, hapus akun), filter berdasarkan peran.
  - Relasi dinamis ke sekolah untuk peran sekolah.
  - Proteksi diri: Admin tidak dapat menghapus atau menurunkan peran akunnya sendiri.
- **Manajemen Data Sekolah (`/dinas/sekolah`)**:
  - Khusus peran **Dinas Pendidikan**.
  - Tambah dan hapus sekolah, serta mengubah data dan profil sekolah mana pun.
  - Pemilih koordinat interaktif (`map-picker`) dengan marker yang dapat digeser.
- **Kelola Profil & Data Sekolah Sendiri (`/sekolah/{sekolah}/edit`, `/sekolah/{sekolah}/profil`)**:
  - Khusus peran **Sekolah** (terikat ke sekolahnya sendiri) dan **Dinas**.
  - Navigasi tab: Data Sekolah dan Profil Sekolah.
  - Proteksi Policy: Akun sekolah hanya dapat mengakses dan mengubah sekolahnya sendiri.
  - Proteksi NPSN: NPSN terkunci (*readonly*) bagi operator sekolah dan diabaikan saat pembaruan data di sisi server.

---

## 2. Matriks Hak Akses (RBAC)

| Fitur | Admin | Dinas Pendidikan | Sekolah | Publik |
|---|:---:|:---:|:---:|:---:|
| Melihat peta publik (`/`) & GeoJSON (`/data/sekolah`) | ✅ | ✅ | ✅ | ✅ |
| Login & Dashboard (`/dashboard`) | ✅ | ✅ | ✅ | ❌ |
| Kelola Pengguna (`/admin/users`) | ✅ | ❌ (403) | ❌ (403) | ❌ (302) |
| Tambah & Hapus Sekolah (`/dinas/sekolah/*`) | ❌ (403) | ✅ | ❌ (403) | ❌ (302) |
| Ubah Data Sekolah (`/sekolah/{id}/edit`) | ❌ (403) | Semua Sekolah | Sekolah Sendiri Saja | ❌ (302) |
| Ubah Profil Sekolah (`/sekolah/{id}/profil`) | ❌ (403) | Semua Sekolah | Sekolah Sendiri Saja | ❌ (302) |
| Mengubah NPSN Sekolah | ❌ | ✅ | ❌ (Terkunci) | ❌ |

---

## 3. Akun Demo untuk Pengujian

Semua akun demo di bawah ini menggunakan kata sandi bawaan: `password123`

| Peran | Nama Akun | Email Login | Kata Sandi | Keterangan Akses |
|---|---|---|---|---|
| **Admin** | Admin Sistem | `admin@sig.test` | `password123` | Akses penuh manajemen pengguna sistem. |
| **Dinas** | Dinas Pendidikan | `dinas@sig.test` | `password123` | Akses penuh seluruh data sekolah dan profil sekolah. |
| **Sekolah 1** | Operator Sekolah 1 | `sekolah1@sig.test` | `password123` | Akses data & profil sekolah pertama (`id: 1`). |
| **Sekolah 2** | Operator Sekolah 2 | `sekolah2@sig.test` | `password123` | Akses data & profil sekolah kedua (`id: 2`). |

> [!NOTE]
> Kata sandi `password123` hanya digunakan untuk keperluan praktikum dan pengembangan lokal.

---

## 4. Persyaratan Sistem

- PHP >= 8.3 (Diuji pada PHP 8.4)
- MySQL Server (misal via DBngin, Homebrew, atau XAMPP)
- Composer
- Node.js & npm

---

## 5. Langkah Instalasi

1. **Clone repository dan masuk ke direktori:**
   ```bash
   cd sig-sekolah
   ```

2. **Buat Database MySQL:**
   ```sql
   CREATE DATABASE sig_sekolah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Konfigurasi Environment:**
   Salin `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   Pastikan pengaturan koneksi database di `.env` sesuai dengan lingkungan Anda:
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

6. **Jalankan Migrasi & Seeder Database:**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```
   *Perintah ini akan membuat struktur tabel `sekolahs`, `users`, `profil_sekolahs` serta menyuntikkan data sekolah awal (8 sekolah) dan empat akun demo.*

7. **(Opsional) Suntikkan Data Demo Skala Penuh (106 Sekolah):**
   Untuk menguji fitur peta visual interaktif (klaster, peta panas, poligon wilayah 5 kecamatan), jalankan seeder demo khusus:
   ```bash
   php artisan db:seed --class=SekolahDemoSeeder
   ```
   *Seeder ini bersifat idempoten (`updateOrCreate`) dan menghasilkan 106 sekolah demo tambahan dengan NPSN berawalan angka 9 (`9xxxxxxx`) di 5 kecamatan Kota Banjarmasin.*

   Untuk menghapus seluruh data demo tersebut tanpa mengganggu data utama:
   ```sql
   DELETE FROM sekolahs WHERE npsn LIKE '9%';
   ```

8. **Instal dan Build Dependensi Frontend (Tailwind CSS & Vite untuk Admin/Dashboard):**
   ```bash
   npm install
   npm run build
   ```
   *(Catatan: Frontend peta publik bekerja secara mandiri dengan Vanilla JS & Leaflet CDN tanpa dependensi bundler Vite)*

---

## 6. Menjalankan Aplikasi

Jalankan server lokal Laravel:
```bash
php artisan serve
```

Akses tautan utama pada browser:
- **Peta Sekolah:** [http://127.0.0.1:8000/](http://127.0.0.1:8000/)
- **Pencarian Sekolah:** [http://127.0.0.1:8000/pencarian](http://127.0.0.1:8000/pencarian)
- **Klaster Marker:** [http://127.0.0.1:8000/klaster](http://127.0.0.1:8000/klaster)
- **Peta Panas (Heatmap):** [http://127.0.0.1:8000/peta-panas](http://127.0.0.1:8000/peta-panas)
- **Wilayah Sebaran (Convex Hull):** [http://127.0.0.1:8000/wilayah](http://127.0.0.1:8000/wilayah)
- **Rute Perjalanan:** [http://127.0.0.1:8000/rute](http://127.0.0.1:8000/rute)
- **Cek Lokasi & Alamat:** [http://127.0.0.1:8000/cek-alamat](http://127.0.0.1:8000/cek-alamat)
- **Halaman Login:** [http://127.0.0.1:8000/login](http://127.0.0.1:8000/login)
- **Dashboard:** [http://127.0.0.1:8000/dashboard](http://127.0.0.1:8000/dashboard)

---

## 7. Menjalankan Pengujian Otomatis

Aplikasi ini dilengkapi pengujian otomatis lengkap menggunakan PHPUnit dan database in-memory SQLite:

```bash
php artisan test
```

Uji fitur mencakup:
- **`SekolahTest`**: Pengujian fungsionalitas CRUD sekolah oleh dinas, validasi koordinat dan NPSN, GeoJSON formatting, dan filter jenjang.
- **`RbacTest`**: Pengujian autentikasi, redirect tamu, pembatasan hak akses tiap peran (Admin, Dinas, Sekolah), proteksi NPSN, penyimpanan profil, perlindungan penghapusan diri admin, pembatasan rate limiter login (429), dan proteksi privasi kontak GeoJSON.
- **`PetaMenuTest`**: Pengujian 10 skenario menu peta interaktif: status 200 ketujuh menu, navigasi publik, hak akses menu, endpoint GeoJSON numerik `[lng, lat]`, filter jenjang, isolasi privasi profil, algoritma Geometri Convex Hull (persegi & collinear), pembentukan poligon tertutup `/data/wilayah`, dan filtering poligon wilayah.

---

## 8. Catatan Batasan Layanan Eksternal & Keamanan

1. **OpenStreetMap Basemap Tile**: Menggunakan tile publik `tile.openstreetmap.org` dengan atribusi resmi OpenStreetMap contributors. Tidak memerlukan API key.
2. **Nominatim Reverse Geocoding**: Menggunakan server publik Nominatim (`nominatim.openstreetmap.org/reverse`) dengan batas laju ketat pada sisi klien (minimal jeda 1200 ms per permintaan) dan parameter `accept-language=id`. Layanan publik ini hanya diperuntukkan bagi penggunaan interaktif wajar; untuk skala produksi disarankan memasang instance Nominatim lokal.
3. **OSRM Routing Demo**: Menggunakan server demo publik `router.project-osrm.org`. Aplikasi dilengkapi mekanisme *fallback* (jalur cadangan) otomatis berupa perhitungan garis lurus (Haversine) per ruas jalan apabila server routing OSRM mengalami gangguan atau kelebihan beban.
4. **Pustaka Peta via CDN (unpkg)**: Pustaka Leaflet (`1.9.4`), `leaflet.markercluster` (`1.4.1`), `leaflet.heat` (`0.2.0`), dan `leaflet-polylinedecorator` (`1.6.0`) dimuat dari CDN publik unpkg. Untuk lingkungan produksi mandiri (*offline/intranet*), aset skrip dan CSS ini dapat diunduh ke direktori `public/vendor`.
5. **Poligon Wilayah**: Poligon sebaran kecamatan dihasilkan dari algoritma matematis *Convex Hull* (Monotone Chain) berdasarkan sebaran koordinat titik sekolah terdata. Poligon ini berfungsi sebagai estimasi jangkauan area sebaran sekolah dan **bukan** merupakan batas wilayah administrasi resmi pemerintah.
6. **Kata Sandi Demo**: Seluruh kata sandi demo (`password123`) wajib diganti sebelum aplikasi dipublikasikan ke server produksi.
