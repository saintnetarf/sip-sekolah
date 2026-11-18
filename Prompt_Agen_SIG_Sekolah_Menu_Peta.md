# PROMPT AGEN LANJUTAN: Menu Peta Interaktif pada Frontend SIG Sekolah

> Cara pakai: simpan sebagai `CLAUDE.md` (Claude Code) atau `AGENTS.md` / `.cursor/rules/sig-sekolah-menu-peta.mdc` (Cursor), atau tempel utuh ke chat agen pada folder proyek `sig-sekolah` yang sudah berjalan. Prompt ini **melanjutkan** prompt agen sebelumnya (Praktikum 1 sampai 18). Jika modul praktikum lanjutan (Word) dilampirkan, modul adalah acuan utama dan prompt ini adalah ringkasan eksekusinya.

---

## 1. Peran dan Tujuan

Anda adalah software engineer senior. Tambahkan **tujuh menu peta** pada frontend publik aplikasi SIG Sekolah yang sudah ada, seluruhnya memakai data tabel `sekolahs` (dan `profil_sekolahs`), mengikuti **Praktikum 19 sampai 27** di bawah. Berhenti hanya setelah seluruh **Kriteria Penerimaan (bagian 7)** terpenuhi dan terbukti lewat perintah yang benar-benar dijalankan.

| Menu | URL | Nama route | Fungsi |
|---|---|---|---|
| Peta Sekolah | `/` | `peta.index` | Marker, popup, filter jenjang |
| Pencarian | `/pencarian` | `peta.cari` | Cari nama, NPSN, kecamatan; daftar hasil dapat diklik |
| Klaster | `/klaster` | `peta.klaster` | Marker berdekatan digabung |
| Peta Panas | `/peta-panas` | `peta.panas` | Kepadatan sekolah atau siswa |
| Wilayah | `/wilayah` | `peta.wilayah` | Poligon sebaran sekolah per kecamatan |
| Rute | `/rute` | `peta.rute` | Rute antar sekolah pilihan |
| Cek Lokasi | `/cek-alamat` | `peta.alamat` | Alamat sebuah titik dan tiga sekolah terdekat |

## 2. Prasyarat dan Batasan Teknis

- **Periksa dulu** (hentikan dan laporkan jika tidak terpenuhi): `php artisan route:list` memuat `peta.index`, `peta.data`, `login`, `dashboard`; tabel `sekolahs` dan `profil_sekolahs` ada; `GET /data/sekolah` mengembalikan `FeatureCollection` yang `properties`-nya memuat `jumlah_siswa`, `kepala_sekolah`, dan `akreditasi`.
- Frontend publik memakai **Blade, CSS biasa, dan JavaScript biasa**. **Tanpa Vite, Tailwind, Alpine, Livewire, dan Filament** pada halaman publik. Tanpa paket Composer atau npm baru.
- Pustaka peta dimuat dari CDN unpkg dengan versi tetap: Leaflet `1.9.4`; `leaflet.markercluster@1.4.1`; `leaflet.heat@0.2.0`; `leaflet-polylinedecorator@1.6.0`. Tile: `https://tile.openstreetmap.org/{z}/{x}/{y}.png` dengan atribusi OpenStreetMap contributors. Tanpa API key.
- Layanan eksternal yang diizinkan hanya: tile OpenStreetMap, Nominatim (`https://nominatim.openstreetmap.org/reverse`), dan OSRM demo (`https://router.project-osrm.org`).
- Seluruh teks antarmuka dalam **Bahasa Indonesia**. Gunakan nama route, view, kelas, dan fungsi persis seperti spesifikasi.
- Jangan mengubah backend (login, RBAC, CRUD sekolah, profil) selain yang disebut di bagian 4.

## 3. Aturan Kerja

1. Kerjakan berurutan per praktikum. Setelah tiap praktikum, jalankan verifikasinya dan perbaiki sampai lulus sebelum lanjut.
2. Jangan mengarang hasil. Jika perintah gagal, tampilkan error, cari penyebab, perbaiki, ulangi.
3. Jangan menyimpang dari spesifikasi tanpa alasan kuat; catat setiap penyimpangan di laporan akhir.
4. Ambil keputusan sendiri untuk hal kecil dan catat asumsinya. Bertanya hanya jika benar-benar terhalang.
5. Kode JavaScript yang dipakai lebih dari satu halaman **wajib** berada di `public/js/peta.js`, tidak disalin ke tiap halaman.
6. Kode rapi, mengikuti konvensi Laravel (PSR-12) dan gaya JavaScript yang konsisten, tanpa kode mati atau `console.log` yang tertinggal (`console.error` pada blok `catch` diperbolehkan).
7. Jangan menjalankan `migrate:fresh` atau menghapus data yang ada tanpa persetujuan.

## 4. Spesifikasi

### 4.1 Layout Publik

`resources/views/layouts/publik.blade.php`:

- Struktur halaman satu layar penuh: header bernavigasi 56 px, lalu `<main>` berposisi relatif yang mengisi sisa tinggi (flex kolom). CSS ditulis di dalam layout (tanpa Vite).
- Memuat CSS Leaflet di `<head>`, lalu `@stack('styles')`.
- Menu navigasi dibangun dari larik `route name => label` dalam urutan: `peta.index` Peta Sekolah, `peta.cari` Pencarian, `peta.klaster` Klaster, `peta.panas` Peta Panas, `peta.wilayah` Wilayah, `peta.rute` Rute, `peta.alamat` Cek Lokasi. Setiap menu dibungkus `Route::has($nama)` agar tautan muncul hanya bila route sudah ada. Menu aktif diberi kelas `aktif` dengan `request()->routeIs($nama)`.
- Di kanan header: `@auth` tautan Dashboard (`route('dashboard')`), `@else` tautan Masuk (`route('login')`).
- Sebelum `</body>`: skrip Leaflet, lalu `window.PETA_URL = { data: route('peta.data'), wilayah: route('peta.data.wilayah') }` (alamat `wilayah` ditambahkan pada Praktikum 24), lalu `asset('js/peta.js')`, lalu `@stack('scripts')`.
- Kelas CSS bersama: `.toolbar` (baris filter), `.peta` (elemen peta, `flex: 1`, tinggi minimum 300 px), `.panel` (kotak melayang `position: absolute`, `z-index: 1000`).
- Halaman peta memakai `<div id="peta" class="peta">`.

### 4.2 Kode Bersama (`public/js/peta.js`)

Objek global `Peta` dengan fungsi berikut. Fungsi yang dapat dipakai sebagai callback (`latlng`, `popup`, `esc`) **tidak boleh** memakai `this`; rujuk `Peta.` secara eksplisit.

| Fungsi | Perilaku |
|---|---|
| `Peta.pusat` | `[-3.3194, 114.5908]` (urutan `[lat, lng]`) |
| `Peta.buat(idElemen, zoom = 12)` | Membuat `L.map`, menambah lapisan tile OSM dengan atribusi, mengembalikan peta |
| `Peta.esc(teks)` | Meng-escape HTML lewat `textContent` (nilai null menjadi string kosong) |
| `Peta.ambilSekolah(jenjang = '')` | `fetch` ke `window.PETA_URL.data` dengan `?jenjang=` (di-`encodeURIComponent`); melempar `Error` bila respons tidak ok; mengembalikan `features` |
| `Peta.latlng(fitur)` | Mengubah `[lng, lat]` GeoJSON menjadi `[lat, lng]` Leaflet |
| `Peta.popup(fitur)` | HTML popup: nama (tebal), NPSN, jenjang dan status, alamat dan kecamatan, telepon, kepala sekolah, akreditasi, siswa dan guru, koordinat. Semua teks lewat `Peta.esc`; tanda `-` bila kosong |
| `Peta.jarakKm(a, b)` | Jarak Haversine (R = 6371 km) antara dua titik `[lat, lng]` |
| `Peta.ikonAngka(angka, warna)` | `L.divIcon` bulat 30 px berisi angka |

### 4.3 Controller dan Route

Ubah route publik menjadi satu grup:

```php
Route::controller(PetaController::class)->group(function () {
    Route::get('/', 'index')->name('peta.index');
    Route::get('/pencarian', 'cari')->name('peta.cari');
    Route::get('/klaster', 'klaster')->name('peta.klaster');
    Route::get('/peta-panas', 'panas')->name('peta.panas');
    Route::get('/wilayah', 'wilayah')->name('peta.wilayah');
    Route::get('/rute', 'rute')->name('peta.rute');
    Route::get('/cek-alamat', 'alamat')->name('peta.alamat');
    Route::get('/data/sekolah', 'geojson')->name('peta.data');
    Route::get('/data/wilayah', 'geojsonWilayah')->name('peta.data.wilayah');
});
```

Tambahkan route per praktikum (jangan sekaligus di awal; `route:list` harus selalu valid). View berada di `resources/views/peta/`: `index`, `cari`, `klaster`, `panas`, `wilayah`, `rute`, `alamat` (`.blade.php`). View lama `resources/views/peta.blade.php` **dipindahkan** menjadi `peta/index.blade.php`. `PetaController::index()` mengembalikan `view('peta.index')`; method `cari`, `klaster`, `panas`, `wilayah`, `rute`, `alamat` masing-masing mengembalikan view sesuai namanya. `geojson()` tetap memuat `Sekolah::with('profil')`.

### 4.4 Spesifikasi per Menu

Semua halaman memakai `@extends('layouts.publik')`, `@section('title', ...)`, dan skrip halaman di `@push('scripts')` (plugin dimuat sebelum skrip halaman, CSS plugin di `@push('styles')`). Semua halaman memakai `Peta.buat('peta')`, `Peta.ambilSekolah()`, `Peta.latlng`, dan `Peta.popup` dari `peta.js`.

**Peta Sekolah (`/`).** Toolbar berisi select jenjang (Semua, SD, SMP, SMA, SMK) dan teks "N sekolah". Marker per sekolah di `L.layerGroup` dengan popup; mengganti filter memuat ulang data; `fitBounds` dengan `pad(0.1)` bila ada data; kegagalan memuat menampilkan "Gagal memuat data" (blok `try/catch`).

**Pencarian (`/pencarian`).** Toolbar: input pencarian, select jenjang, tombol Hapus, teks "N dari M sekolah". Data dimuat sekali; penyaringan di sisi klien pada nama, NPSN, dan kecamatan (tidak peka huruf besar), dengan *debounce* 300 ms pada input. Panel melayang berisi daftar hasil (dibuat dengan `textContent`); klik item memanggil `map.flyTo(latlng, 17)` lalu membuka popup marker terkait. Tombol Hapus mengosongkan input dan jenjang.

**Klaster (`/klaster`).** Plugin `leaflet.markercluster` (CSS `MarkerCluster.css` dan `MarkerCluster.Default.css`). `L.markerClusterGroup({ showCoverageOnHover: false, maxClusterRadius: 60, spiderfyOnMaxZoom: true, disableClusteringAtZoom: 17, chunkedLoading: true })`; marker ditambahkan dengan `addLayers([...])`; filter jenjang dan jumlah sekolah seperti menu pertama.

**Peta Panas (`/peta-panas`).** Plugin `leaflet.heat`. Toolbar: select bobot (`sekolah` dan `siswa`), select jenjang, slider radius 10 sampai 60 (awal 30), teks jumlah. Bobot mode `sekolah` = 0,35; mode `siswa` = `max(jumlah_siswa / maksimum, 0,1)` dengan `jumlah_siswa` kosong dianggap 0 dan maksimum minimal 1. Opsi `L.heatLayer`: `blur: 25`, `maxZoom: 17`, `max: 1.0`, gradien `0.2 #2563eb`, `0.4 #06b6d4`, `0.6 #84cc16`, `0.8 #facc15`, `1.0 #dc2626`. Mengubah bobot atau radius menggambar ulang tanpa memuat data lagi; mengubah jenjang memuat data. Legenda gradien "Rendah ke Tinggi" sebagai panel.

**Wilayah (`/wilayah`).** Kelas `App\Support\Geometri` (`app/Support/Geometri.php`) dengan `public static function convexHull(array $titik): array`: algoritma monotone chain pada titik `[x, y]`, menghapus duplikat, mengurutkan, mengembalikan titik hull berlawanan arah jarum jam tanpa titik penutup; bila kurang dari 3 titik, mengembalikan titik apa adanya. `PetaController::geojsonWilayah(Request)`: `Sekolah::with('profil')`, filter `?jenjang=`, `groupBy('kecamatan')`; untuk tiap kelompok hitung hull dari `[longitude, latitude]`; **lewati** kelompok yang hull-nya kurang dari 3 titik; tutup cincin (`$hull[] = $hull[0]`); `Feature` bergeometri `Polygon` dengan `properties`: `kecamatan`, `jumlah_sekolah`, `jumlah_siswa` (jumlah siswa dari profil, null dianggap 0). Respons `FeatureCollection`. Halaman: `L.geoJSON` dengan gaya per fitur berdasarkan rasio `jumlah_sekolah` terhadap kecamatan terbanyak (`>= 0,8` merah `#dc2626`, `>= 0,6` oranye `#ea580c`, `>= 0,4` kuning tua `#f59e0b`, selain itu biru `#3b82f6`), `fillOpacity: 0.3`, `weight: 3`; tooltip nama kecamatan; popup jumlah sekolah dan siswa; legenda relatif; filter jenjang; `fitBounds`. Halaman harus menyebut bahwa poligon adalah perkiraan sebaran, bukan batas administrasi resmi (cukup pada legenda atau teks kecil).

**Rute (`/rute`).** Plugin `leaflet-polylinedecorator`. Panel kiri: input cari sekolah, daftar centang (dibangun dengan DOM, bukan `innerHTML`), tombol Buat Rute dan Reset, serta teks urutan "1. A → 2. B". Urutan rute = urutan mencentang, **maksimal 10** (centang ke-11 dibatalkan dengan `alert`); minimal 2 (selain itu `alert`). Seluruh sekolah ditampilkan sebagai `circleMarker` abu-abu dengan tooltip sebagai konteks. `ambilRute(titik)` memanggil `https://router.project-osrm.org/route/v1/driving/{lng,lat;...}?overview=full&geometries=geojson`, membalik koordinat hasil menjadi `[lat, lng]`, dan mengembalikan `garis`, `jarak` (km), `durasi` (menit), serta `legs` (jarak dan durasi per ruas). **Cadangan wajib:** bila `fetch` gagal, respons tidak ok, atau tidak ada rute, kembalikan garis lurus antar titik dengan jarak Haversine per ruas (`sumber: 'Garis lurus'`). Gambar: polyline biru dengan panah `L.polylineDecorator` (`offset: '5%'`, `repeat: 100`), penanda bernomor `Peta.ikonAngka` (awal hijau `#16a34a`, akhir merah `#dc2626`, lainnya biru `#2563eb`) dengan popup nama, alamat, dan jarak dari titik sebelumnya, serta panel informasi (jumlah titik, total jarak, perkiraan waktu bila ada, dan sumber rute). Tombol Buat Rute dinonaktifkan selama permintaan. Reset mengosongkan pilihan, rute, dan panel.

**Cek Lokasi (`/cek-alamat`).** Klik peta menempatkan `circleMarker` merah, memanggil Nominatim `reverse` dengan parameter `format=jsonv2&addressdetails=1&accept-language=id&lat=..&lon=..` (**bahasa lewat parameter URL, bukan header khusus**), dan menampilkan panel: koordinat (6 desimal), alamat lengkap (`display_name`), rincian Jalan, Kelurahan/Desa (`suburb`, `neighbourhood`, atau `village`), Kota/Kabupaten (`city`, `town`, `municipality`, atau `county`), Provinsi, Kode pos (tanda `-` bila kosong), serta **tiga sekolah terdekat** menurut `Peta.jarakKm` dengan jarak "x,xx km (garis lurus)". Garis putus-putus biru dari titik ke ketiga sekolah. Klik item sekolah (event delegation pada panel) membuka popup sekolah dan menerbangkan peta ke sana. **Batas laju:** klik yang berjarak kurang dari 1200 ms dari permintaan sebelumnya diabaikan. Bila Nominatim gagal, tampilkan "Alamat tidak dapat dimuat." dan tetap tampilkan sekolah terdekat. Seluruh teks dari layanan luar melewati `Peta.esc`.

### 4.5 Data Demo (`SekolahDemoSeeder`)

`database/seeders/SekolahDemoSeeder.php` dengan `mt_srand(2026)` (hasil selalu sama) dan `updateOrCreate` pada `npsn` (idempoten). Lima kecamatan dengan pusat dan jumlah sekolah: Banjarmasin Utara (-3.2900, 114.5950, 30), Banjarmasin Timur (-3.3150, 114.6150, 22), Banjarmasin Tengah (-3.3250, 114.5900, 16), Banjarmasin Selatan (-3.3550, 114.5900, 26), Banjarmasin Barat (-3.3200, 114.5650, 12) = **106 sekolah**. NPSN `sprintf('9%07d', $nomor)`; jenjang dipilih acak dari `['SD','SD','SD','SMP','SMP','SMA','SMK']`; status Negeri (2 dari 3) atau Swasta; nama `"{jenjang} Demo {nomor}"`; koordinat pusat ditambah selisih acak `mt_rand(-100, 100) / 10000` (dibulatkan 7 desimal); setiap sekolah memiliki profil (`kepala_sekolah`, `akreditasi` A, B, atau C, `jumlah_siswa` 80 sampai 900, `jumlah_guru` 8 sampai 60) lewat `$sekolah->profil()->updateOrCreate([], [...])`. **Jangan** memanggilnya dari `DatabaseSeeder`; jalankan manual dengan `php artisan db:seed --class=SekolahDemoSeeder`. README mencatat cara menghapus data demo: `DELETE FROM sekolahs WHERE npsn LIKE '9%';`.

## 5. Langkah Kerja (ikuti urutan)

**Praktikum 19, Layout, Menu, dan Kode Bersama.** Bagian 4.1, 4.2, dan 4.5; jalankan seeder demo. Verifikasi: `public/js/peta.js` ada; tabel `sekolahs` bertambah 106 baris (NPSN berawalan 9) dan menjalankan seeder dua kali tidak menggandakan data; `profil_sekolahs` terisi.

**Praktikum 20, Menu Peta Sekolah.** Pindahkan view ke `peta/index.blade.php`, ubah route menjadi grup (bagian 4.3), ubah `index()`. Verifikasi: `GET /` berstatus 200, memuat navigasi, dan `GET /data/sekolah` mengembalikan 114 fitur (8 awal ditambah 106 demo, bila data awal tidak berubah).

**Praktikum 21, Menu Pencarian.** Route `peta.cari`, method, dan view. Verifikasi: `GET /pencarian` berstatus 200; `route:list` memuat `peta.cari`.

**Praktikum 22, Menu Klaster.** Route `peta.klaster`, method, dan view. Verifikasi: `GET /klaster` berstatus 200 dan memuat URL plugin markercluster versi 1.4.1.

**Praktikum 23, Menu Peta Panas.** Route `peta.panas`, method, dan view. Verifikasi: `GET /peta-panas` berstatus 200 dan memuat URL leaflet.heat versi 0.2.0.

**Praktikum 24, Menu Wilayah.** `Geometri`, `geojsonWilayah`, route `peta.wilayah` dan `peta.data.wilayah`, view, serta alamat `wilayah` pada `window.PETA_URL`. Verifikasi: `GET /data/wilayah` mengembalikan **5 fitur** `Polygon` dengan data demo; setiap cincin tertutup (titik pertama sama dengan titik terakhir); `?jenjang=` mengubah hasil; tanpa data demo (hanya 8 sekolah awal) mengembalikan 0 fitur tanpa error.

**Praktikum 25, Menu Rute.** Route `peta.rute`, method, dan view. Verifikasi: `GET /rute` berstatus 200 dan memuat URL polylinedecorator versi 1.6.0; kode memuat jalur cadangan garis lurus.

**Praktikum 26, Menu Cek Lokasi.** Route `peta.alamat`, method, dan view. Verifikasi: `GET /cek-alamat` berstatus 200; kode memuat pembatas laju 1200 ms dan parameter `accept-language` pada URL.

**Praktikum 27, Pengujian.** Tulis `tests/Feature/PetaMenuTest.php` (`RefreshDatabase`; `setUp()` memanggil `$this->withoutVite()` bila perlu; buat data lewat `Sekolah::create([...])` dan `profil()->create([...])`). Minimal sepuluh pengujian:

1. ketujuh halaman menu (`/`, `/pencarian`, `/klaster`, `/peta-panas`, `/wilayah`, `/rute`, `/cek-alamat`) berstatus 200 untuk tamu;
2. halaman memuat tautan ke keenam menu lain dan tautan Masuk bagi tamu;
3. pengguna yang login melihat tautan Dashboard, bukan Masuk;
4. `/data/sekolah` mengembalikan `FeatureCollection` dengan `coordinates` berurutan `[longitude, latitude]` bertipe angka;
5. `/data/sekolah?jenjang=SMA` hanya memuat fitur SMA;
6. `/data/sekolah` memuat `jumlah_siswa`, `kepala_sekolah`, `akreditasi` dan **tidak** memuat `email` maupun `website`;
7. `/data/wilayah` untuk tiga sekolah tidak segaris pada satu kecamatan mengembalikan satu `Polygon` dengan cincin tertutup dan `jumlah_sekolah` benar;
8. `/data/wilayah` untuk kecamatan yang hanya memiliki dua sekolah mengembalikan nol fitur;
9. `Geometri::convexHull` pada titik uji (persegi dengan satu titik di dalamnya) mengembalikan empat titik sudut, dan pada tiga titik segaris mengembalikan kurang dari tiga titik;
10. `/data/wilayah?jenjang=...` menyaring poligon sesuai jenjang.

Jalankan `php artisan test` sampai hijau. Pastikan konfigurasi test memakai database terpisah (SQLite di memori bawaan `phpunit.xml`); jika memakai MySQL, gunakan database khusus uji, bukan `sig_sekolah`. Keluarkan juga `php artisan route:list --path=peta` sebagai bukti.

## 6. Larangan

- Jangan menyalin fungsi bersama (membuat peta, mengambil data, popup, jarak) ke tiap halaman; gunakan `peta.js`.
- Jangan memasukkan teks dari database atau layanan luar ke `innerHTML` tanpa `Peta.esc` (atau memakai `textContent`).
- Jangan menukar urutan koordinat: GeoJSON `[longitude, latitude]`, Leaflet `[latitude, longitude]`, OSRM `longitude,latitude`.
- Jangan memakai layanan geocoding atau routing selain Nominatim dan OSRM demo, jangan memanggil Nominatim lebih dari satu kali per detik, dan jangan memanggilnya untuk pemrosesan massal.
- Jangan membiarkan fitur Rute tanpa cadangan bila OSRM gagal.
- Jangan memasukkan email atau website sekolah ke GeoJSON publik.
- Jangan menjelaskan poligon Wilayah sebagai batas administrasi resmi.
- Jangan memasang Vite, Tailwind, Alpine, atau paket tambahan pada halaman publik.
- Jangan mengklaim "selesai" tanpa menjalankan verifikasi tiap praktikum dan `php artisan test`.

## 7. Kriteria Penerimaan

Pekerjaan selesai jika semua butir berikut benar dan dibuktikan dengan keluaran perintah (untuk perilaku di browser yang tidak dapat Anda jalankan, jelaskan terus terang bahwa itu hanya diperiksa lewat kode dan keluaran HTTP, bukan secara visual):

1. Ketujuh URL pada bagian 1 berstatus 200 dan menu navigasi tampil di semua halaman dengan menu aktif ditandai.
2. `peta.js` menjadi satu-satunya tempat fungsi bersama; tidak ada duplikasi fungsi `buat`, `esc`, `latlng`, `popup`, atau `jarakKm` pada view.
3. Peta Sekolah, Klaster, dan Pencarian menampilkan 114 sekolah (dengan data demo); filter jenjang berfungsi.
4. Peta Panas menyediakan mode bobot sekolah dan siswa serta pengatur radius.
5. `/data/wilayah` mengembalikan lima poligon tertutup dengan data demo, dan nol poligon tanpa data demo, tanpa error.
6. Menu Rute: pilihan 2 sampai 10 sekolah, rute OSRM dengan panah dan penanda bernomor, serta cadangan garis lurus yang menandai sumbernya.
7. Menu Cek Lokasi: alamat, rincian, tiga sekolah terdekat dengan jarak, pembatas laju 1200 ms, dan pesan gagal yang jelas.
8. Semua teks dari database dan layanan luar aman dari XSS (di-escape atau `textContent`).
9. GeoJSON publik tidak memuat email dan website.
10. `php artisan test` hijau, termasuk sepuluh pengujian pada Praktikum 27.
11. `README.md` diperbarui: daftar menu, cara menjalankan seeder demo dan menghapusnya, serta catatan batasan layanan eksternal (Nominatim, OSRM demo, tile OSM).

## 8. Laporan Akhir

Setelah selesai, tulis laporan ringkas berisi:

1. Daftar file yang dibuat atau diubah.
2. Keluaran ringkas verifikasi tiap praktikum dan hasil `php artisan test`.
3. Asumsi yang diambil dan penyimpangan dari spesifikasi (jika ada), beserta alasannya.
4. Bagian yang belum dapat diuji secara visual di browser (misalnya tampilan peta, panah rute, dan respons layanan eksternal) beserta cara mengujinya secara manual.
5. Catatan batasan: poligon Wilayah adalah perkiraan sebaran; Nominatim dan OSRM demo hanya untuk pemakaian ringan sehingga aplikasi sungguhan memerlukan layanan sendiri; plugin dimuat dari unpkg sehingga produksi sebaiknya menyimpannya lokal.
