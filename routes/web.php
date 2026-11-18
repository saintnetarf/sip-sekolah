<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\ProfilSekolahController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'role:dinas'])->group(function () {
    Route::resource('dinas/sekolah', SekolahController::class)
        ->only(['index', 'create', 'store', 'destroy'])
        ->names('dinas.sekolah');
});

Route::middleware(['auth', 'role:dinas,sekolah'])->group(function () {
    Route::get('/sekolah/{sekolah}/edit', [SekolahController::class, 'edit'])->name('sekolah.edit');
    Route::put('/sekolah/{sekolah}', [SekolahController::class, 'update'])->name('sekolah.update');
    Route::get('/sekolah/{sekolah}/profil', [ProfilSekolahController::class, 'edit'])->name('sekolah.profil.edit');
    Route::put('/sekolah/{sekolah}/profil', [ProfilSekolahController::class, 'update'])->name('sekolah.profil.update');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('admin/users', UserController::class)
        ->except(['show'])
        ->names('admin.users');
});
