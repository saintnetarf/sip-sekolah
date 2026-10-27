# Aplikasi SIG Sekolah (Laravel + MySQL + OpenStreetMap + RBAC)

Aplikasi Sistem Informasi Geografis (SIG) untuk pendataan sekolah beserta titik koordinatnya dengan visualisasi peta interaktif berbasis OpenStreetMap dan Leaflet, dilengkapi dengan fitur Autentikasi dan Kontrol Akses Berbasis Peran (*Role-Based Access Control* / RBAC).

---

## 1. Fitur Utama

- **Peta Digital Publik (`/`)**:
  - Peta interaktif Leaflet dengan basemap OpenStreetMap.
  - Sebaran sekolah di Kota Banjarmasin dan sekitarnya.
  - Filter jenjang pendidikan (SD, SMP, SMA, SMK).
  - Popup detail sekolah: Nama, NPSN, Jenjang, Status, Kepala Sekolah, Akreditasi, Siswa/Guru, Alamat, Telepon, dan Koordinat.
  - Proteksi XSS (escape HTML pada teks popup peta).
- **Endpoint GeoJSON Publik (`/data/sekolah`)**:
  - Format standar GeoJSON `FeatureCollection` dengan koordinat `[longitude, latitude]`.
  - Properti publik mencakup informasi profil penting (kepala sekolah, akreditasi, siswa, guru).
  - Data kontak sensitif/pribadi (email dan website) **tidak diekspos** di GeoJSON publik.
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
   *Perintah ini akan membuat struktur tabel `sekolahs`, `users`, `profil_sekolahs` serta menyuntikkan data sekolah awal dan empat akun demo.*

7. **Instal dan Build Dependensi Frontend (Tailwind CSS & Vite):**
   ```bash
   npm install
   npm run build
   ```

---

## 6. Menjalankan Aplikasi

Jalankan server lokal Laravel:
```bash
php artisan serve
```

Akses tautan utama pada browser:
- **Peta Publik:** [http://127.0.0.1:8000/](http://127.0.0.1:8000/)
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

---

## 8. Catatan Keamanan

1. **Kata Sandi Demo**: Seluruh kata sandi demo (`password123`) wajib diganti sebelum aplikasi dipublikasikan ke server produksi.
2. **Fitur Tambahan yang Direkomendasikan**: Untuk rilis publik skala penuh, tambahkan fitur lupa kata sandi (*password reset token*), verifikasi alamat email (*MustVerifyEmail*), dan audit log aktivitas (*activity logging*).
