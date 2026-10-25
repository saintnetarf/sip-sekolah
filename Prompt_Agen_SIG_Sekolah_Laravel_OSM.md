# PROMPT AGEN: Aplikasi SIG Pendataan Sekolah (Laravel + MySQL + OpenStreetMap)

> Cara pakai: simpan sebagai `CLAUDE.md` (Claude Code) atau `AGENTS.md` / `.cursor/rules/sig-sekolah.mdc` (Cursor), atau tempel utuh ke chat agen pada folder kerja kosong. Jika modul praktikum (Word) dilampirkan, modul adalah acuan utama dan prompt ini adalah ringkasan eksekusinya.

---

## 1. Peran dan Tujuan

Anda adalah software engineer senior yang membangun aplikasi **SIG Sekolah**: aplikasi pendataan sekolah beserta titik koordinatnya, dengan peta digital OpenStreetMap. Kerjakan dari nol sampai berjalan, mengikuti urutan **Praktikum 1 sampai 9** di bawah, dan berhenti hanya setelah seluruh **Kriteria Penerimaan (bagian 7)** terpenuhi dan terbukti lewat perintah yang benar-benar dijalankan.

Aplikasi punya dua bagian:

- **Backend (petugas):** CRUD data sekolah, koordinat dipilih dengan mengeklik atau menggeser marker pada peta.
- **Frontend (publik):** peta dengan marker semua sekolah, popup informasi, dan filter jenjang. Data diambil dari endpoint GeoJSON.

## 2. Batasan Teknis (wajib dipatuhi)

- Laravel versi terbaru yang stabil (Laravel 13) lewat `composer create-project laravel/laravel sig-sekolah`. PHP 8.3 atau lebih baru.
- Database **MySQL**, nama database `sig_sekolah`. Konfigurasi di `.env` (`DB_CONNECTION=mysql`). Jangan memakai SQLite untuk aplikasi; SQLite hanya boleh dipakai oleh test otomatis (konfigurasi `phpunit.xml` bawaan).
- Tampilan: Blade + Tailwind CSS bawaan Laravel (Vite). Tanpa starter kit, tanpa Livewire, Inertia, atau Filament.
- Peta: **Leaflet 1.9.4** dari CDN `https://unpkg.com/leaflet@1.9.4/dist/` dan tile `https://tile.openstreetmap.org/{z}/{x}/{y}.png`. Wajib atribusi `&copy; OpenStreetMap contributors`. Tanpa API key.
- Tanpa package Composer atau npm tambahan di luar bawaan Laravel.
- Seluruh teks antarmuka dalam **Bahasa Indonesia**. Kode, nama kelas, dan komentar teknis boleh Inggris atau Indonesia, tetapi nama tabel, kolom, dan route sesuai spesifikasi di bawah.
- Halaman admin **tidak** memakai login (disengaja untuk modul praktikum). Jangan menambahkan autentikasi kecuali diminta.

## 3. Aturan Kerja

1. Kerjakan berurutan per praktikum. Setelah tiap praktikum selesai, jalankan perintah verifikasinya dan perbaiki sampai lulus sebelum lanjut.
2. Jangan mengarang hasil. Jika sebuah perintah gagal, tampilkan pesan error, cari penyebabnya, perbaiki, ulangi.
3. Jangan mengubah spesifikasi (nama kolom, nama route, urutan koordinat) tanpa alasan kuat. Jika harus menyimpang, catat alasannya di laporan akhir.
4. Jangan menanyakan hal yang bisa diputuskan sendiri. Ambil asumsi paling masuk akal, lalu catat di laporan akhir. Bertanya hanya jika terhalang, misalnya kredensial MySQL tidak bisa ditemukan.
5. Jangan menyimpan password atau rahasia di kode. Semua konfigurasi lewat `.env`; sertakan `.env.example` yang rapi.
6. Kode harus rapi, mengikuti konvensi Laravel (PSR-12), tanpa kode mati atau debugging yang tertinggal.

## 4. Spesifikasi

### 4.1 Struktur Data

Tabel `sekolahs` (model `App\Models\Sekolah`):

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint | primary key |
| npsn | string(8), unique | 8 digit |
| nama | string(255) | |
| jenjang | enum: SD, SMP, SMA, SMK | |
| status | enum: Negeri, Swasta | |
| alamat | text | |
| kecamatan | string(100) | |
| telepon | string(20), nullable | |
| latitude | decimal(10,7) | -90 sampai 90 |
| longitude | decimal(10,7) | -180 sampai 180 |
| timestamps | | |

Model: `$fillable` untuk semua kolom isian; `casts()` mengubah `latitude` dan `longitude` menjadi `float`.

### 4.2 Route (`routes/web.php`)

| Method | URL | Nama route | Penanganan |
|---|---|---|---|
| GET | `/` | `peta.index` | `PetaController@index` (view `peta`) |
| GET | `/data/sekolah` | `peta.data` | `PetaController@geojson` |
| resource (tanpa `show`) | `/admin/sekolah` | `admin.sekolah.*` | `SekolahController` |

Route admin dibuat dengan `Route::prefix('admin')->name('admin.')->group(...)`. Hapus route welcome bawaan.

### 4.3 Validasi (`SekolahController`)

Satu method privat `rules(?int $id = null)` dipakai `store` dan `update`:

- `npsn`: required, `digits:8`, `Rule::unique('sekolahs','npsn')->ignore($id)`
- `nama`: required, string, max 255
- `jenjang`: required, `Rule::in(['SD','SMP','SMA','SMK'])`
- `status`: required, `Rule::in(['Negeri','Swasta'])`
- `alamat`: required, string
- `kecamatan`: required, string, max 100
- `telepon`: nullable, string, max 20
- `latitude`: required, numeric, `between:-90,90`
- `longitude`: required, numeric, `between:-180,180`

Setelah simpan, ubah, dan hapus: redirect ke `admin.sekolah.index` dengan flash `success` berbahasa Indonesia. `index` mendukung pencarian `?q=` (nama, NPSN, kecamatan), urut nama, `paginate(10)->withQueryString()`.

### 4.4 Endpoint GeoJSON (`GET /data/sekolah`)

Mengembalikan `FeatureCollection`. Setiap sekolah adalah `Feature` bergeometri `Point` dengan **`coordinates` = `[longitude, latitude]`** (urutan GeoJSON). `properties` berisi: `npsn`, `nama`, `jenjang`, `status`, `alamat`, `kecamatan`, `telepon`. Parameter opsional `?jenjang=SD|SMP|SMA|SMK` memfilter hasil. Nilai koordinat harus berupa angka (bukan string).

### 4.5 Tampilan

**Layout admin** `resources/views/layouts/app.blade.php`: `@vite(['resources/css/app.css','resources/js/app.js'])`, CSS Leaflet di `<head>`, JS Leaflet sebelum `@stack('scripts')`, navigasi (Peta Publik memakai `url('/')`, Data Sekolah), dan blok flash `success`.

**Halaman admin** di `resources/views/admin/sekolah/`:

- `index.blade.php`: judul, tombol Tambah, kolom pencarian, tabel (No, NPSN, Nama, Jenjang, Status, Kecamatan, Koordinat, Aksi), tombol Ubah, tombol Hapus dengan `confirm()` dan `@method('DELETE')`, pesan "Belum ada data." bila kosong, link paginasi.
- `_form.blade.php`: field NPSN, Nama, Kecamatan, Telepon (teks), Jenjang dan Status (select), Alamat (textarea), serta input Latitude dan Longitude (`type="number" step="any"`). Setiap field menampilkan pesan error (`@error`) dan memakai `old()` dengan nilai awal dari model.
- `create.blade.php` dan `edit.blade.php`: memuat `@csrf` (dan `@method('PUT')` pada edit) lalu `@include` form.

**Pemilih koordinat** (di `_form.blade.php`, lewat `@push('scripts')`), elemen peta berid `map-picker`:

- Peta Leaflet berpusat di `[-3.3194, 114.5908]` (zoom 13) bila belum ada koordinat; bila ada (mode ubah) berpusat di koordinat itu (zoom 16).
- Satu marker `draggable`. Klik peta memindahkan marker dan mengisi input; menggeser marker (`dragend`) mengisi input; mengubah input manual memindahkan marker dan peta. Nilai diisi dengan `toFixed(7)`.
- Koordinat Leaflet memakai urutan `[lat, lng]`.

**Peta publik** `resources/views/peta.blade.php` (halaman mandiri, CSS biasa, tanpa Vite):

- Header berisi judul, select filter jenjang (Semua jenjang, SD, SMP, SMA, SMK), teks jumlah sekolah, dan tautan "Kelola Data" ke `admin.sekolah.index`.
- Elemen peta berid `map`, memenuhi sisa tinggi layar. Pusat awal `[-3.3194, 114.5908]`, zoom 12.
- `L.geoJSON` dengan popup per marker: nama (tebal), NPSN, jenjang dan status, alamat dan kecamatan, telepon (atau `-`), serta koordinat sebagai `lat, lng`.
- Semua teks dari database **wajib di-escape** (fungsi `esc()` berbasis `textContent`) sebelum dimasukkan ke HTML popup, untuk mencegah XSS.
- Data diambil dengan `fetch` dari `route('peta.data')`; fungsi `muatData(jenjang)` membersihkan lapisan, menambah data, memperbarui jumlah, dan memanggil `fitBounds(...pad(0.1))` bila ada data. Mengubah filter memanggil ulang `muatData`.

### 4.6 Data Contoh (seeder)

`SekolahSeeder` memakai `updateOrCreate` (kunci `npsn`) agar aman dijalankan ulang. Isi 8 sekolah fiktif di sekitar Banjarmasin (latitude -3.28 sampai -3.36, longitude 114.57 sampai 114.61), dua per jenjang (satu Negeri, satu Swasta), NPSN `30000001` sampai `30000008`, nama berpola "SD Negeri Contoh 1" dan seterusnya. Panggil dari `DatabaseSeeder` tanpa menghapus kode bawaan.

## 5. Langkah Kerja (ikuti urutan)

**Praktikum 1, Instalasi Laravel**
- Periksa `php -v`, `composer -V`, `node -v`, `npm -v`.
- Buat proyek `sig-sekolah`, jalankan `php artisan serve` sebentar untuk memastikan halaman awal terbuka, lalu hentikan.
- Verifikasi: `php artisan --version` menampilkan Laravel 13.x.

**Praktikum 2, Konfigurasi MySQL**
- Buat database `sig_sekolah` (utf8mb4, `utf8mb4_unicode_ci`), ubah `.env` dan `.env.example`.
- Jalankan `php artisan migrate`.
- Verifikasi: `php artisan migrate:status` semua `Ran`.

**Praktikum 3, Migration dan Model**
- `php artisan make:model Sekolah -m`, isi migration dan model sesuai bagian 4.1, lalu `php artisan migrate`.
- Verifikasi: tabel `sekolahs` ada dengan kolom dan tipe sesuai spesifikasi (cek lewat `php artisan db:table sekolahs`).

**Praktikum 4, Seeder**
- Buat dan daftarkan `SekolahSeeder` (bagian 4.6), jalankan `php artisan db:seed`.
- Verifikasi: jumlah baris `sekolahs` = 8; menjalankan seeder dua kali tetap 8.

**Praktikum 5, Route dan Controller**
- Buat `SekolahController` (bagian 4.3) dan route admin (bagian 4.2).
- Verifikasi: `php artisan route:list --path=admin` menampilkan enam route (index, create, store, edit, update, destroy).

**Praktikum 6, Layout dan Daftar**
- Jalankan `npm install` lalu `npm run build` (atau `npm run dev` di proses terpisah saat menguji manual).
- Buat layout admin dan `index.blade.php` (bagian 4.5).
- Verifikasi: `GET /admin/sekolah` berstatus 200 dan memuat 8 data seeder.

**Praktikum 7, Form dengan Pemilih Koordinat**
- Buat `_form`, `create`, `edit` sesuai bagian 4.5, termasuk skrip pemilih koordinat.
- Verifikasi: tambah, ubah, dan hapus sekolah berfungsi; pelanggaran validasi menampilkan pesan error dan mempertahankan input lama.

**Praktikum 8, Peta Publik**
- Buat `PetaController`, route `/` dan `/data/sekolah`, serta `peta.blade.php` (bagian 4.4 dan 4.5).
- Verifikasi: `GET /data/sekolah` mengembalikan `FeatureCollection` berisi 8 fitur dan titik pertama berbentuk `[114.xxx, -3.xxx]`; `GET /` berstatus 200.

**Praktikum 9, Pengujian**
- Tulis feature test (PHPUnit atau Pest, ikuti bawaan proyek) dengan `RefreshDatabase`, minimal:
  1. simpan sekolah valid menambah satu baris;
  2. nama kosong ditolak;
  3. NPSN ganda ditolak;
  4. latitude 120 ditolak;
  5. longitude 200 ditolak;
  6. ubah data (NPSN sendiri tidak dianggap ganda);
  7. hapus data;
  8. `/data/sekolah` mengembalikan `FeatureCollection` dengan `coordinates` berurutan `[longitude, latitude]` dan bertipe angka;
  9. `/data/sekolah?jenjang=SMA` hanya mengembalikan fitur berjenjang SMA;
  10. `/` dan `/admin/sekolah` berstatus 200.
- Jalankan `php artisan test` sampai hijau.

## 6. Larangan

- Jangan menukar urutan koordinat: GeoJSON `[longitude, latitude]`, Leaflet `[latitude, longitude]`.
- Jangan memakai `localStorage` untuk data sekolah; semua data di MySQL.
- Jangan memuat library peta selain Leaflet, dan jangan memakai tile yang memerlukan API key.
- Jangan menjalankan `migrate:fresh` atau menghapus data pada database yang sudah berisi data pengguna tanpa persetujuan.
- Jangan mengklaim "selesai" tanpa menjalankan verifikasi tiap praktikum dan `php artisan test`.

## 7. Kriteria Penerimaan

Pekerjaan selesai jika semua butir berikut benar dan dibuktikan dengan keluaran perintah:

1. `php artisan serve` berjalan tanpa error; `/` menampilkan peta OpenStreetMap dengan 8 marker.
2. Klik marker menampilkan popup berisi nama, NPSN, jenjang, status, alamat, kecamatan, telepon, dan koordinat.
3. Filter jenjang mengubah jumlah marker dan jumlah sekolah di header.
4. `/admin/sekolah` menampilkan daftar, pencarian, dan paginasi yang berfungsi.
5. Form tambah dan ubah: klik peta atau geser marker mengisi latitude dan longitude; mengetik koordinat memindahkan marker.
6. Sekolah baru yang disimpan langsung muncul sebagai marker di `/` pada lokasi yang diklik.
7. Seluruh aturan validasi pada bagian 4.3 bekerja dan menampilkan pesan error berbahasa Indonesia.
8. Teks berisi HTML (misalnya nama sekolah `<b>x</b>`) tampil sebagai teks biasa pada popup.
9. `php artisan test` hijau.
10. `.env.example` memuat konfigurasi MySQL; `README.md` singkat menjelaskan cara instalasi dan menjalankan aplikasi (langkah, perintah, URL).

## 8. Laporan Akhir

Setelah selesai, tulis laporan ringkas berisi:

1. Daftar file yang dibuat atau diubah.
2. Keluaran ringkas verifikasi tiap praktikum dan hasil `php artisan test`.
3. Asumsi yang diambil dan penyimpangan dari spesifikasi (jika ada), beserta alasannya.
4. Langkah menjalankan aplikasi dari nol.
5. Catatan keamanan: halaman admin belum memakai login dan belum layak dipublikasikan ke internet.
