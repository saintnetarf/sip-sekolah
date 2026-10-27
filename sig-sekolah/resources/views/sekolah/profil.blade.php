@extends('layouts.app', ['title' => 'Profil Sekolah - ' . $sekolah->nama])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $sekolah->nama }}</h1>
            <p class="text-sm text-gray-500 mt-1">NPSN: <span class="font-mono font-medium text-gray-700">{{ $sekolah->npsn }}</span> &bull; {{ $sekolah->jenjang }} &bull; {{ $sekolah->status }}</p>
        </div>
        @if (auth()->user()->isDinas())
            <a href="{{ route('dinas.sekolah.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                &larr; Kembali ke daftar
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                &larr; Kembali ke dashboard
            </a>
        @endif
    </div>

    <!-- Tab Navigasi -->
    @include('sekolah._tab')

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
        <form method="POST" action="{{ route('sekolah.profil.update', $sekolah) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kepala Sekolah -->
                <div>
                    <label for="kepala_sekolah" class="block text-sm font-medium text-gray-700">Nama Kepala Sekolah</label>
                    <input
                        type="text"
                        name="kepala_sekolah"
                        id="kepala_sekolah"
                        value="{{ old('kepala_sekolah', $profil->kepala_sekolah) }}"
                        maxlength="150"
                        placeholder="Contoh: Drs. H. Ahmad Fauzi, M.Pd"
                        class="mt-1 block w-full px-3 py-2 border @error('kepala_sekolah') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                    @error('kepala_sekolah')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Resmi Sekolah -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Sekolah</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $profil->email) }}"
                        maxlength="150"
                        placeholder="info@sekolah.sch.id"
                        class="mt-1 block w-full px-3 py-2 border @error('email') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Website -->
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700">Website</label>
                    <input
                        type="url"
                        name="website"
                        id="website"
                        value="{{ old('website', $profil->website) }}"
                        maxlength="255"
                        placeholder="https://sekolah.sch.id"
                        class="mt-1 block w-full px-3 py-2 border @error('website') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                    @error('website')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tahun Berdiri -->
                <div>
                    <label for="tahun_berdiri" class="block text-sm font-medium text-gray-700">Tahun Berdiri</label>
                    <input
                        type="number"
                        name="tahun_berdiri"
                        id="tahun_berdiri"
                        min="1800"
                        max="{{ date('Y') }}"
                        value="{{ old('tahun_berdiri', $profil->tahun_berdiri) }}"
                        placeholder="Contoh: 1985"
                        class="mt-1 block w-full px-3 py-2 border @error('tahun_berdiri') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                    @error('tahun_berdiri')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Akreditasi -->
                <div>
                    <label for="akreditasi" class="block text-sm font-medium text-gray-700">Akreditasi</label>
                    <select
                        name="akreditasi"
                        id="akreditasi"
                        class="mt-1 block w-full px-3 py-2 border @error('akreditasi') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white"
                    >
                        <option value="">-- Pilih Akreditasi --</option>
                        @foreach (['A', 'B', 'C', 'Belum Terakreditasi'] as $akr)
                            <option value="{{ $akr }}" {{ old('akreditasi', $profil->akreditasi) === $akr ? 'selected' : '' }}>
                                {{ $akr }}
                            </option>
                        @endforeach
                    </select>
                    @error('akreditasi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jumlah Siswa & Guru -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="jumlah_siswa" class="block text-sm font-medium text-gray-700">Jumlah Siswa</label>
                        <input
                            type="number"
                            name="jumlah_siswa"
                            id="jumlah_siswa"
                            min="0"
                            value="{{ old('jumlah_siswa', $profil->jumlah_siswa) }}"
                            placeholder="0"
                            class="mt-1 block w-full px-3 py-2 border @error('jumlah_siswa') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                        @error('jumlah_siswa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="jumlah_guru" class="block text-sm font-medium text-gray-700">Jumlah Guru</label>
                        <input
                            type="number"
                            name="jumlah_guru"
                            id="jumlah_guru"
                            min="0"
                            value="{{ old('jumlah_guru', $profil->jumlah_guru) }}"
                            placeholder="0"
                            class="mt-1 block w-full px-3 py-2 border @error('jumlah_guru') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                        @error('jumlah_guru')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Deskripsi Sekolah -->
            <div>
                <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi / Profil Singkat</label>
                <textarea
                    name="deskripsi"
                    id="deskripsi"
                    rows="4"
                    maxlength="2000"
                    placeholder="Tuliskan gambaran umum, visi-misi singkat, atau fasilitas unggulan sekolah..."
                    class="mt-1 block w-full px-3 py-2 border @error('deskripsi') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >{{ old('deskripsi', $profil->deskripsi) }}</textarea>
                @error('deskripsi')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ auth()->user()->isDinas() ? route('dinas.sekolah.index') : route('dashboard') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                    Simpan Profil
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
