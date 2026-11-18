@extends('layouts.app', ['title' => 'Dashboard - SIG Sekolah'])

@section('content')
<div class="space-y-6">
    <!-- Header Sambutan -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Selamat Datang, {{ auth()->user()->name }}!</h1>
            <p class="text-sm text-gray-500 mt-1">
                Anda masuk sebagai peran <span class="font-semibold text-indigo-600">{{ auth()->user()->roleLabel() }}</span>.
            </p>
        </div>
        <div>
            <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                {{ auth()->user()->email }}
            </span>
        </div>
    </div>

    {{-- KONTEN ROLE ADMIN --}}
    @if (auth()->user()->isAdmin())
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengguna Admin</p>
                        <p class="text-3xl font-extrabold text-gray-900 mt-2">{{ $userCounts['admin'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Dinas Pendidikan</p>
                        <p class="text-3xl font-extrabold text-gray-900 mt-2">{{ $userCounts['dinas'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Operator Sekolah</p>
                        <p class="text-3xl font-extrabold text-gray-900 mt-2">{{ $userCounts['sekolah'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900">Manajemen Pengguna Sistem</h2>
                <p class="text-sm text-gray-500 mt-0.5">Kelola data administrator, dinas, dan operator sekolah.</p>
            </div>
            @if (\Illuminate\Support\Facades\Route::has('admin.users.index'))
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-sm transition">
                    Kelola Pengguna
                </a>
            @endif
        </div>
    @endif

    {{-- KONTEN ROLE DINAS --}}
    @if (auth()->user()->isDinas())
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Sekolah Terdata</p>
                        <p class="text-4xl font-extrabold text-gray-900 mt-2">{{ $totalSekolah ?? 0 }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Kelola Data & Profil Sekolah</h2>
                    <p class="text-sm text-gray-500 mt-1">Anda memiliki akses penuh untuk menambah, memperbarui data, koordinat peta, serta profil semua sekolah di wilayah kerja Anda.</p>
                </div>
                <div class="mt-4">
                    <a href="{{ route('dinas.sekolah.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-sm transition">
                        Buka Data Sekolah &rarr;
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- KONTEN ROLE SEKOLAH --}}
    @if (auth()->user()->isSekolah())
        @if (! $sekolah)
            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-6 text-amber-800">
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h2 class="text-base font-bold">Perhatian</h2>
                        <p class="text-sm mt-0.5">Akun Anda belum ditautkan ke sekolah. Hubungi admin.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-100 pb-6">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md">
                            {{ $sekolah->jenjang }} &bull; {{ $sekolah->status }}
                        </span>
                        <h2 class="text-2xl font-extrabold text-gray-900 mt-2">{{ $sekolah->nama }}</h2>
                        <p class="text-sm text-gray-500 mt-1">NPSN: <span class="font-mono font-medium text-gray-700">{{ $sekolah->npsn }}</span> &bull; Kecamatan: {{ $sekolah->kecamatan }}</p>
                    </div>
                    <div>
                        @if (\Illuminate\Support\Facades\Route::has('sekolah.edit'))
                            <a href="{{ route('sekolah.edit', $sekolah) }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-sm transition">
                                Ubah Data & Profil
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Persentase Kelengkapan Profil -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-700">Kelengkapan Profil Sekolah</span>
                        <span class="text-sm font-extrabold {{ $persentaseProfil >= 100 ? 'text-emerald-600' : 'text-indigo-600' }}">{{ $persentaseProfil }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div class="h-3 rounded-full {{ $persentaseProfil >= 100 ? 'bg-emerald-500' : 'bg-indigo-600' }} transition-all duration-500" style="width: {{ $persentaseProfil }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        @if ($persentaseProfil >= 100)
                            Profil sekolah Anda telah terisi lengkap.
                        @else
                            Lengkapi profil sekolah Anda (kepala sekolah, kontak, akreditasi, dan deskripsi) agar informasi lebih informatif bagi publik.
                        @endif
                    </p>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
