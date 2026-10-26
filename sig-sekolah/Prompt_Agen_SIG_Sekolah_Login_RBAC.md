# PROMPT AGEN LANJUTAN: Login dan RBAC pada Aplikasi SIG Sekolah

> Cara pakai: simpan sebagai `CLAUDE.md` (Claude Code) atau `AGENTS.md` / `.cursor/rules/sig-sekolah-rbac.mdc` (Cursor), atau tempel utuh ke chat agen pada folder proyek `sig-sekolah` yang sudah berjalan. Prompt ini **melanjutkan** prompt agen sebelumnya (Praktikum 1 sampai 9). Jika modul praktikum lanjutan (Word) dilampirkan, modul adalah acuan utama dan prompt ini adalah ringkasan eksekusinya.

---

## 1. Peran dan Tujuan

Anda adalah software engineer senior. Tambahkan **autentikasi (halaman login)** dan **RBAC dengan tiga role** pada aplikasi SIG Sekolah yang sudah ada, mengikuti **Praktikum 10 sampai 18** di bawah. Berhenti hanya setelah seluruh **Kriteria Penerimaan (bagian 7)** terpenuhi dan terbukti lewat perintah yang benar-benar dijalankan.

Tiga role dan hak aksesnya:

| Fitur | admin | dinas | sekolah | publik |
|---|---|---|---|---|
| Melihat peta publik (`/`) | ya | ya | ya | ya |
| Login dan dashboard | ya | ya | ya | tidak |
| Kelola pengguna (tambah, ubah, hapus) | ya | tidak | tidak | tidak |
| Daftar, tambah, hapus sekolah | tidak | ya | tidak | tidak |
| Ubah data sekolah (termasuk koordinat) | tidak | semua sekolah | hanya sekolahnya sendiri | tidak |
| Ubah profil sekolah | tidak | semua sekolah | hanya sekolahnya sendiri | tidak |

Keputusan desain yang harus dipatuhi: admin **tidak** mengelola data sekolah; role sekolah **tidak** dapat menambah atau menghapus sekolah dan **tidak** dapat mengubah NPSN; satu sekolah boleh punya lebih dari satu akun.

## 2. Prasyarat dan Batasan Teknis

- Proyek `sig-sekolah` (Laravel 13, MySQL, Blade + Tailwind, Leaflet) dari Praktikum 1 sampai 9 sudah ada. **Periksa dulu**: `php artisan route:list` memuat route `peta.index`, `peta.data`, dan resource `admin.sekolah.*`; tabel `sekolahs` ada. Jika tidak terpenuhi, hentikan dan laporkan.
- Autentikasi dibuat **manual** dengan session guard bawaan Laravel. **Dilarang** memasang starter kit atau paket tambahan (Breeze, Jetstream, Fortify, Spatie Permission, dan sejenisnya). Tanpa paket Composer atau npm baru.
- Tidak ada registrasi publik, tidak ada fitur "lupa kata sandi". Akun hanya dibuat oleh admin.
- Seluruh teks antarmuka dalam **Bahasa Indonesia**. Gunakan nama tabel, kolom, route, dan kelas persis seperti spesifikasi.
- **Jangan** menjalankan `migrate:fresh`, `migrate:reset`, atau menghapus data yang ada tanpa persetujuan. Gunakan `php artisan migrate` dan seeder yang idempoten.

## 3. Aturan Kerja

1. Kerjakan berurutan per praktikum. Setelah tiap praktikum, jalankan verifikasinya dan perbaiki sampai lulus sebelum lanjut.
2. Jangan mengarang hasil. Jika perintah gagal, tampilkan error, cari penyebab, perbaiki, ulangi.
3. Jangan menyimpang dari spesifikasi tanpa alasan kuat; catat setiap penyimpangan di laporan akhir.
4. Ambil keputusan sendiri untuk hal kecil dan catat asumsinya. Bertanya hanya jika benar-benar terhalang.
5. Keamanan ditegakkan **di sisi server**. Menyembunyikan menu bukan pengamanan.
6. Kode rapi, mengikuti konvensi Laravel (PSR-12), tanpa kode mati atau debugging yang tertinggal.

## 4. Spesifikasi

### 4.1 Struktur Data

**Tabel `users`** (migration baru `add_role_to_users_table`):

- `role`: `string(20)`, default `'sekolah'`, setelah `password`.
- `sekolah_id`: `foreignId` nullable, `constrained('sekolahs')`, `nullOnDelete()`, setelah `role`.
- `down()` harus membalik perubahan (`dropConstrainedForeignId('sekolah_id')`, `dropColumn('role')`).

**Tabel `profil_sekolahs`** (model `App\Models\ProfilSekolah`, migration `create_profil_sekolahs_table`):

| Kolom | Tipe |
|---|---|
| id | bigint |
| sekolah_id | foreignId unik, `constrained('sekolahs')`, `cascadeOnDelete()` |
| kepala_sekolah | string(150), nullable |
| email | string(150), nullable |
| website | string(255), nullable |
| tahun_berdiri | unsignedSmallInteger, nullable |
| akreditasi | string(25), nullable |
| jumlah_siswa | unsignedInteger, nullable |
| jumlah_guru | unsignedInteger, nullable |
| deskripsi | text, nullable |
| timestamps | |

**Model:**

- `ProfilSekolah`: `$fillable` semua kolom isian (termasuk `sekolah_id`); `belongsTo(Sekolah::class)`.
- `Sekolah`: tambahkan `profil(): HasOne` dan `users(): HasMany`.
- `User`: tambahkan `role` dan `sekolah_id` ke kolom yang boleh diisi massal; konstanta `ROLE_ADMIN='admin'`, `ROLE_DINAS='dinas'`, `ROLE_SEKOLAH='sekolah'`; relasi `sekolah(): BelongsTo`; method `isAdmin()`, `isDinas()`, `isSekolah()`, dan `roleLabel()` (Admin, Dinas Pendidikan, Sekolah). Pertahankan cast `'password' => 'hashed'`.

### 4.2 Seeder Akun Demo

`UserSeeder` dengan `updateOrCreate` (kunci `email`), kata sandi `password123` **tanpa** `Hash::make()` (cast `hashed` sudah meng-hash; hash ganda membuat login gagal):

| Nama | Email | Role | Sekolah |
|---|---|---|---|
| Admin Sistem | admin@sig.test | admin | kosong |
| Dinas Pendidikan | dinas@sig.test | dinas | kosong |
| Operator Sekolah 1 | sekolah1@sig.test | sekolah | sekolah pertama menurut `id` |
| Operator Sekolah 2 | sekolah2@sig.test | sekolah | sekolah kedua menurut `id` |

Ambil dua sekolah dengan `Sekolah::orderBy('id')->take(2)->get()` dan gunakan `->get(0)?->id` agar aman bila data kurang. Ubah `DatabaseSeeder::run()` menjadi hanya memanggil `SekolahSeeder` lalu `UserSeeder` (hapus pembuatan "Test User" bawaan). Jalankan dengan `php artisan db:seed --class=UserSeeder`.

### 4.3 Autentikasi

`AuthController`:

- `showLogin()` menampilkan `auth.login`.
- `login(Request)`: validasi `email` (required, email) dan `password` (required); `Auth::attempt($credentials, $request->boolean('remember'))`; jika berhasil `session()->regenerate()` lalu `redirect()->intended(route('dashboard'))`; jika gagal `back()->withErrors(['email' => 'Email atau kata sandi salah.'])->onlyInput('email')`. Pesan harus sama untuk email salah maupun sandi salah.
- `logout(Request)`: `Auth::logout()`, `session()->invalidate()`, `session()->regenerateToken()`, redirect ke `peta.index`.

Route: `GET /login` (nama `login`) dan `POST /login` di grup `guest`, POST login dengan `throttle:5,1`; `POST /logout` (nama `logout`) dan `GET /dashboard` (nama `dashboard`) di grup `auth`. Di `bootstrap/app.php` panggil `$middleware->redirectUsersTo('/dashboard')`.

`resources/views/auth/login.blade.php`: halaman mandiri (`@vite`), kartu di tengah layar, field email, kata sandi, checkbox "Ingat saya", tombol Masuk, pesan error `@error`, `@csrf`, dan tautan "Kembali ke peta publik".

`DashboardController` (invokable) mengirim ke view `dashboard`: untuk admin jumlah akun per role; untuk dinas jumlah sekolah; untuk sekolah data sekolahnya beserta **persentase kelengkapan profil** (kolom profil terisi dibagi 8, dibulatkan). Akun sekolah tanpa `sekolah_id` menampilkan peringatan "Akun Anda belum ditautkan ke sekolah. Hubungi admin."

### 4.4 Middleware Role

`App\Http\Middleware\RoleMiddleware::handle(Request $request, Closure $next, string ...$roles)`: jika tidak ada user atau `in_array($user->role, $roles, true)` salah, `abort(403, 'Anda tidak memiliki hak akses.')`. Daftarkan alias `role` di `bootstrap/app.php`. Buat `resources/views/errors/403.blade.php` berbahasa Indonesia (tanpa `@vite`, CSS inline) dengan tautan kembali ke dashboard (bila login) atau peta publik.

### 4.5 Policy

`App\Policies\SekolahPolicy::update(User $user, Sekolah $sekolah): bool`: `true` bila `isDinas()`; selain itu `true` hanya bila `isSekolah()`, `sekolah_id` tidak null, dan `(int) $user->sekolah_id === $sekolah->id`. Policy ditemukan otomatis; gunakan `Gate::authorize('update', $sekolah)` (base controller tidak memiliki `authorize()`).

### 4.6 Route Akhir (`routes/web.php`)

| Method | URL | Nama route | Middleware | Controller |
|---|---|---|---|---|
| GET | `/` | `peta.index` | publik | `PetaController@index` |
| GET | `/data/sekolah` | `peta.data` | publik | `PetaController@geojson` |
| GET, POST | `/login` | `login` | guest (+ throttle pada POST) | `AuthController` |
| POST | `/logout` | `logout` | auth | `AuthController@logout` |
| GET | `/dashboard` | `dashboard` | auth | `DashboardController` |
| resource (index, create, store, destroy) | `/dinas/sekolah` | `dinas.sekolah.*` | auth, `role:dinas` | `SekolahController` |
| GET | `/sekolah/{sekolah}/edit` | `sekolah.edit` | auth, `role:dinas,sekolah` + Policy | `SekolahController@edit` |
| PUT | `/sekolah/{sekolah}` | `sekolah.update` | auth, `role:dinas,sekolah` + Policy | `SekolahController@update` |
| GET | `/sekolah/{sekolah}/profil` | `sekolah.profil.edit` | auth, `role:dinas,sekolah` + Policy | `ProfilSekolahController@edit` |
| PUT | `/sekolah/{sekolah}/profil` | `sekolah.profil.update` | auth, `role:dinas,sekolah` + Policy | `ProfilSekolahController@update` |
| resource (tanpa show) | `/admin/users` | `admin.users.*` | auth, `role:admin` | `UserController` |

Route lama `admin.sekolah.*` **dihapus**. Urutan middleware wajib `auth` sebelum `role` agar tamu dialihkan ke login, bukan mendapat 403. Pada urutan pengerjaan, segera setelah `RoleMiddleware` ada (Praktikum 13), lindungi route sekolah lama dengan `['auth', 'role:dinas']` sebelum direstrukturisasi pada Praktikum 14.

### 4.7 Controller Sekolah dan Profil

`SekolahController`: pertahankan `index` (pencarian `q`, urut nama, `paginate(10)->withQueryString()`), `create`, `store`, `destroy`, dan `rules()`. Tambahkan:

- `edit(Sekolah)` dan `update(Request, Sekolah)` memanggil `Gate::authorize('update', $sekolah)` lebih dulu.
- Pada `update`, jika `$request->user()->isSekolah()` maka `unset($data['npsn'])` sebelum `update()` (NPSN tidak boleh berubah walau form dimanipulasi).
- Redirect setelah `store`, `update`, `destroy`: dinas ke `dinas.sekolah.index`, sekolah ke `dashboard`, dengan flash `success` berbahasa Indonesia.
- Nama folder view tetap `admin/sekolah`; hanya nama route yang berubah. Perbarui semua pemanggil di view: `admin.sekolah.create` ke `dinas.sekolah.create`, `admin.sekolah.edit` ke `sekolah.edit`, `admin.sekolah.destroy` ke `dinas.sekolah.destroy`, `admin.sekolah.store` ke `dinas.sekolah.store`, `admin.sekolah.update` ke `sekolah.update`. Tautan Batal pada `_form` menuju `dinas.sekolah.index` (dinas) atau `dashboard` (sekolah). Input NPSN diberi `@readonly` untuk role sekolah (bukan `disabled`, agar nilainya tetap terkirim). Telusuri seluruh proyek (`grep -rn "admin.sekolah"`) sampai tidak ada sisa.
- Tautan "Kelola Data" pada `peta.blade.php` diganti: `@auth` tautan ke `dashboard`, `@else` tautan ke `login` ("Masuk").

`ProfilSekolahController`:

- `edit(Sekolah)`: `Gate::authorize('update', $sekolah)`; kirim `$sekolah` dan `$profil` (`$sekolah->profil ?? new ProfilSekolah`) ke view `sekolah.profil`.
- `update(Request, Sekolah)`: `Gate::authorize`, validasi, lalu `$sekolah->profil()->updateOrCreate([], $data)`, `back()` dengan flash `success`.
- Validasi (semua nullable): `kepala_sekolah` string max 150; `email` email max 150; `website` url max 255; `tahun_berdiri` integer between 1800 dan tahun berjalan (`date('Y')`); `akreditasi` in `A`, `B`, `C`, `Belum Terakreditasi`; `jumlah_siswa` dan `jumlah_guru` integer min 0; `deskripsi` string max 2000.

### 4.8 Tampilan

- `layouts/app.blade.php`: navigasi sesuai role. Semua login: Peta Publik, Dashboard. Admin: **Pengguna** (`admin.users.index`). Dinas: **Data Sekolah** (`dinas.sekolah.index`). Sekolah dengan `sekolah_id`: **Sekolah Saya** (`sekolah.edit` untuk sekolahnya). Sisi kanan: nama dan `roleLabel()` serta tombol **Keluar** (form POST dengan `@csrf`). Tampilkan flash `success` (hijau) dan `error` (merah).
- `sekolah/_tab.blade.php`: tab "Data Sekolah" dan "Profil Sekolah" dengan penanda tab aktif (`request()->routeIs(...)`), dipasang pada `admin/sekolah/edit.blade.php` dan `sekolah/profil.blade.php`.
- `sekolah/profil.blade.php`: judul nama sekolah dan NPSN, tab, form `PUT` dengan field kepala sekolah, email, website, tahun berdiri, jumlah siswa, jumlah guru, akreditasi (select), deskripsi (textarea), `@error` per field, nilai awal `old(...)` dari `$profil`.
- `admin/users/`: `index` (filter role, tabel Nama, Email, Role, Sekolah, Aksi; tombol Hapus disembunyikan untuk akun sendiri dengan `$u->is(auth()->user())`; paginasi), `_form` (nama, email, role, select sekolah yang disembunyikan lewat JavaScript kecuali role `sekolah`, kata sandi dan konfirmasi; pada mode ubah ada petunjuk "Kosongkan jika tidak diganti"), `create`, `edit`.

### 4.9 Kelola Pengguna (`UserController`)

- `index` memuat `with('sekolah')`, filter `?role=`, `paginate(10)->withQueryString()`.
- Validasi: `name` required max 255; `email` required email max 255 unik (abaikan id sendiri saat ubah); `role` dalam tiga konstanta; `sekolah_id` **wajib** jika role sekolah (`Rule::requiredIf`), nullable, `exists:sekolahs,id`; `password` required saat tambah, nullable saat ubah, min 8, `confirmed`.
- Jika role bukan sekolah, paksa `sekolah_id = null`. Saat ubah, kata sandi kosong tidak diubah (`unset`).
- Admin tidak boleh menurunkan role akunnya sendiri (kembalikan error pada field `role`) dan tidak boleh menghapus akunnya sendiri (flash `error`).

### 4.10 Profil pada Peta Publik

Ubah `PetaController@geojson`: `Sekolah::with('profil')` dan tambahkan pada `properties`: `kepala_sekolah`, `akreditasi`, `jumlah_siswa`, `jumlah_guru` (memakai `?->`). **Jangan** memasukkan email dan website ke GeoJSON publik. Pada popup `peta.blade.php`, tampilkan kepala sekolah, akreditasi, serta siswa dan guru (tanda `-` bila kosong). Teks tetap di-escape dengan `esc()`; angka tidak perlu.

## 5. Langkah Kerja (ikuti urutan)

**Praktikum 10, Database untuk Role dan Profil.** Buat dua migration, model `ProfilSekolah`, relasi, dan perubahan `User` (bagian 4.1). Jalankan `php artisan migrate`. Verifikasi: `php artisan db:table users` memuat `role` dan `sekolah_id`; tabel `profil_sekolahs` ada.

**Praktikum 11, Seeder Akun Demo.** Bagian 4.2. Verifikasi: tabel `users` memuat empat akun; kolom `password` berisi hash; menjalankan seeder dua kali tidak menggandakan akun.

**Praktikum 12, Login dan Logout.** Bagian 4.3. Pada tahap ini tambahkan tombol Keluar sementara pada layout. Verifikasi: tamu membuka `/dashboard` dialihkan ke `/login`; login `admin@sig.test` berhasil; login dengan sandi salah menampilkan pesan; logout mengembalikan ke peta.

**Praktikum 13, Middleware Role.** Bagian 4.4 dan lindungi route sekolah lama dengan `['auth','role:dinas']`. Jalankan `php artisan optimize:clear`. Verifikasi: dinas dapat membuka daftar sekolah, akun sekolah mendapat 403, tamu dialihkan ke login.

**Praktikum 14, Policy dan Pembatasan Data Sekolah.** Bagian 4.5, 4.6 (kecuali route profil dan users), dan 4.7 untuk `SekolahController`, penggantian nama route pada view, tautan peta, serta layout dengan navigasi per role. Verifikasi: `php artisan route:list` tidak memuat `admin.sekolah`; akun sekolah1 dapat membuka `/sekolah/{idnya}/edit` tetapi mendapat 403 untuk id sekolah lain; NPSN terkunci.

**Praktikum 15, Profil Sekolah.** Bagian 4.7 (`ProfilSekolahController`) dan 4.8 (tab dan form profil), route profil. Verifikasi: sekolah1 menyimpan profil sendiri; mengakses profil sekolah lain mendapat 403; dinas dapat mengubah profil sekolah mana pun; persentase kelengkapan di dashboard berubah.

**Praktikum 16, Kelola Pengguna.** Bagian 4.9, route `admin.users.*`, menu Pengguna, dan view. Verifikasi: admin menambah akun role sekolah (wajib memilih sekolah), akun baru dapat login; admin tidak dapat menghapus dirinya; dinas dan sekolah mendapat 403 pada `/admin/users`.

**Praktikum 17, Profil pada Peta Publik.** Bagian 4.10. Verifikasi: `GET /data/sekolah` memuat `kepala_sekolah` dan `akreditasi` serta tidak memuat `email` dan `website`; popup menampilkan profil.

**Praktikum 18, Pengujian.** Tulis `tests/Feature/RbacTest.php` (`RefreshDatabase`, `setUp()` memanggil `$this->withoutVite()`; pakai `User::factory()->create(['role' => ..., 'sekolah_id' => ...])` dengan kata sandi factory `password`; buat sekolah lewat `Sekolah::create([...])`). Minimal sepuluh pengujian:

1. tamu membuka `/dashboard` dialihkan ke `/login`;
2. login gagal menghasilkan error pada `email`, login benar dialihkan ke `/dashboard` dan terautentikasi;
3. admin dapat membuka `/admin/users` tetapi mendapat 403 pada `/dinas/sekolah`;
4. admin menambah pengguna role sekolah dan tersimpan beserta `sekolah_id`;
5. role sekolah tanpa `sekolah_id` ditolak validasi;
6. dinas dapat membuka daftar sekolah, form ubah, dan profil sekolah mana pun, tetapi mendapat 403 pada `/admin/users`;
7. sekolah dapat membuka edit sekolahnya, mendapat 403 untuk edit dan profil sekolah lain, `/dinas/sekolah`, dan `/admin/users`;
8. sekolah mengubah data sekolahnya tetapi NPSN tidak berubah walau dikirim nilai berbeda;
9. sekolah menyimpan profil sekolahnya (tercatat pada `profil_sekolahs`);
10. sekolah mendapat 403 saat mengirim DELETE ke `/dinas/sekolah/{id}` dan data tetap ada.

Jalankan `php artisan test` sampai hijau. Pastikan konfigurasi test memakai database terpisah (SQLite di memori bawaan `phpunit.xml`); jika memakai MySQL, gunakan database khusus uji, bukan `sig_sekolah`.

## 6. Larangan

- Jangan memasang paket autentikasi atau RBAC pihak ketiga.
- Jangan menerima `role` atau `sekolah_id` dari input pengguna selain pada form admin yang divalidasi (`Rule::in`, `exists`).
- Jangan mengandalkan `readonly`, `disabled`, atau menu tersembunyi sebagai pengamanan; keputusan selalu di server.
- Jangan mengambil id sekolah pengguna sekolah dari input atau URL tanpa Policy.
- Jangan memakai `Hash::make()` untuk atribut `password` pada model User (hash ganda).
- Jangan memasukkan data pribadi atau kontak (email, website) ke GeoJSON publik.
- Jangan menulis kata sandi asli pada kode selain seeder demo `password123`, dan beri catatan bahwa itu hanya untuk praktikum.
- Jangan mengklaim "selesai" tanpa menjalankan verifikasi tiap praktikum dan `php artisan test`.

## 7. Kriteria Penerimaan

Pekerjaan selesai jika semua butir berikut benar dan dibuktikan dengan keluaran perintah:

1. `/login` menampilkan form; kredensial salah menampilkan "Email atau kata sandi salah."; enam percobaan gagal beruntun dalam satu menit menghasilkan 429.
2. Tamu yang membuka halaman terlindungi dialihkan ke `/login`; setelah login kembali ke halaman tujuan atau dashboard.
3. Admin: dapat menambah, mengubah, dan menghapus pengguna (kecuali akunnya sendiri); mendapat 403 pada `/dinas/sekolah`.
4. Dinas: dapat menambah, mengubah (data dan profil), dan menghapus sekolah mana pun; mendapat 403 pada `/admin/users`.
5. Sekolah: hanya dapat mengubah data dan profil sekolahnya; mengganti id pada URL ke sekolah lain menghasilkan 403; NPSN tidak berubah; tidak dapat menambah atau menghapus sekolah.
6. Menu navigasi sesuai role dan dashboard menampilkan ringkasan sesuai role (admin: jumlah akun per role, dinas: jumlah sekolah, sekolah: kelengkapan profil).
7. Koordinat yang diubah sekolah atau dinas (klik atau geser marker) langsung berpindah pada peta publik.
8. Profil sekolah tampil pada popup peta publik; email dan website tidak ada pada `/data/sekolah`.
9. `php artisan route:list` tidak memuat `admin.sekolah`, dan `grep -rn "admin.sekolah" app resources routes` tidak menghasilkan apa pun.
10. `php artisan test` hijau, termasuk sepuluh pengujian pada Praktikum 18.
11. `README.md` diperbarui: akun demo (email dan kata sandi), matriks hak akses singkat, dan cara menjalankan pengujian.

## 8. Laporan Akhir

Setelah selesai, tulis laporan ringkas berisi:

1. Daftar file yang dibuat atau diubah.
2. Keluaran ringkas verifikasi tiap praktikum dan hasil `php artisan test`.
3. Asumsi yang diambil dan penyimpangan dari spesifikasi (jika ada), beserta alasannya.
4. Daftar akun demo dan cara menguji tiap role.
5. Catatan keamanan yang masih tersisa: belum ada fitur lupa kata sandi, verifikasi email, atau pencatatan aktivitas; kata sandi demo harus diganti sebelum aplikasi dipublikasikan.
