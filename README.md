# Aplikasi SIG Sekolah (Laravel + MySQL + OpenStreetMap)

Proyek Sistem Informasi Geografis (SIG) Sekolah berbasis Laravel 13, MySQL, dan OpenStreetMap Leaflet. Proyek utama berada pada direktori [sig-sekolah](file:///Users/arifinasyikin/kuliah%202026/sig%202026/sig-sekolah).

---

## 1. Quick Start

```bash
cd sig-sekolah
php artisan serve
```

Akses aplikasi di browser:
- **Peta Publik:** [http://127.0.0.1:8000/](http://127.0.0.1:8000/)
- **Admin Kelola Sekolah:** [http://127.0.0.1:8000/admin/sekolah](http://127.0.0.1:8000/admin/sekolah)
- **Endpoint GeoJSON:** [http://127.0.0.1:8000/data/sekolah](http://127.0.0.1:8000/data/sekolah)

---

## 2. Pengujian Otomatis

```bash
cd sig-sekolah
php artisan test
```

Untuk panduan lengkap mengenai struktur modul, instalasi dari nol, dan konfigurasi environment, silakan merujuk ke [README.md di sig-sekolah](file:///Users/arifinasyikin/kuliah%202026/sig%202026/sig-sekolah/README.md).
