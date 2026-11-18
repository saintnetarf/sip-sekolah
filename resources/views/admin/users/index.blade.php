@extends('layouts.app', ['title' => 'Kelola Pengguna - SIG Sekolah'])

@section('content')
<div class="space-y-6">
    <!-- Header Halaman dan Tombol Tambah -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kelola Pengguna</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar seluruh akun pengguna sistem beserta peran dan hak aksesnya</p>
        </div>
        <div>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Pengguna
            </a>
        </div>
    </div>

    <!-- Filter Role -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center space-x-3">
            <label for="role" class="text-sm font-medium text-gray-700">Filter Peran:</label>
            <select name="role" id="role" onchange="this.form.submit()" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Semua Peran</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="dinas" {{ request('role') === 'dinas' ? 'selected' : '' }}>Dinas Pendidikan</option>
                <option value="sekolah" {{ request('role') === 'sekolah' ? 'selected' : '' }}>Sekolah</option>
            </select>
            @if (request('role'))
                <a href="{{ route('admin.users.index') }}" class="text-xs text-gray-500 hover:text-indigo-600 underline">
                    Reset Filter
                </a>
            @endif
        </form>
        <div class="text-xs text-gray-500 font-medium">
            Total: {{ $users->total() }} akun
        </div>
    </div>

    <!-- Tabel Pengguna -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3 text-center">Peran</th>
                        <th class="px-4 py-3">Sekolah</th>
                        <th class="px-4 py-3 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                    @forelse ($users as $index => $u)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-center font-medium text-gray-500">
                                {{ $users->firstItem() ? $users->firstItem() + $index : $index + 1 }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-900">
                                {{ $u->name }}
                                @if ($u->is(auth()->user()))
                                    <span class="ml-1.5 px-2 py-0.5 text-xs bg-indigo-50 text-indigo-700 font-normal rounded-full border border-indigo-100">Akun Anda</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                {{ $u->email }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($u->isAdmin())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                        Admin
                                    </span>
                                @elseif ($u->isDinas())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        Dinas Pendidikan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Sekolah
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $u->sekolah?->nama ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center space-x-2">
                                    <a href="{{ route('admin.users.edit', $u) }}" class="p-1.5 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded transition" title="Ubah">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    @if (! $u->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pengguna ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded transition cursor-pointer" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data pengguna yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
