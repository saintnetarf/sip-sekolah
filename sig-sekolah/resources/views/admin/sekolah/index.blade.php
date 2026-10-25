@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Halaman dan Tombol Tambah -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kelola Data Sekolah</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar seluruh sekolah dan lokasi koordinatnya</p>
        </div>
        <div>
            <a href="{{ route('admin.sekolah.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Sekolah
            </a>
        </div>
    </div>

    <!-- Kolom Pencarian -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <form method="GET" action="{{ route('admin.sekolah.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama sekolah, NPSN, atau kecamatan..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900 transition">
                    Cari
                </button>
                @if (request('q'))
                    <a href="{{ route('admin.sekolah.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Data Sekolah -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3">NPSN</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3 text-center">Jenjang</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3">Kecamatan</th>
                        <th class="px-4 py-3">Koordinat</th>
                        <th class="px-4 py-3 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                    @forelse ($sekolahs as $index => $sekolah)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-center font-medium text-gray-500">
                                {{ $sekolahs->firstItem() ? $sekolahs->firstItem() + $index : $index + 1 }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-900">
                                {{ $sekolah->npsn }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $sekolah->nama }}
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ $sekolah->alamat }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                    @if($sekolah->jenjang === 'SD') bg-red-100 text-red-800
                                    @elseif($sekolah->jenjang === 'SMP') bg-blue-100 text-blue-800
                                    @elseif($sekolah->jenjang === 'SMA') bg-indigo-100 text-indigo-800
                                    @else bg-amber-100 text-amber-800 @endif">
                                    {{ $sekolah->jenjang }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $sekolah->status === 'Negeri' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $sekolah->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                {{ $sekolah->kecamatan }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                {{ $sekolah->latitude }}, {{ $sekolah->longitude }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center space-x-2">
                                    <a href="{{ route('admin.sekolah.edit', $sekolah) }}" class="p-1.5 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded transition" title="Ubah">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.sekolah.destroy', $sekolah) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data sekolah ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded transition" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                Belum ada data.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sekolahs->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                {{ $sekolahs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
