<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Nama -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
            <input
                type="text"
                name="name"
                id="name"
                value="{{ old('name', $user->name) }}"
                required
                maxlength="255"
                placeholder="Contoh: Budi Santoso"
                class="mt-1 block w-full px-3 py-2 border @error('name') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email <span class="text-red-500">*</span></label>
            <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email', $user->email) }}"
                required
                maxlength="255"
                placeholder="nama@sig.test"
                class="mt-1 block w-full px-3 py-2 border @error('email') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Role -->
        <div>
            <label for="role-select" class="block text-sm font-medium text-gray-700">Peran Pengguna <span class="text-red-500">*</span></label>
            <select
                name="role"
                id="role-select"
                required
                class="mt-1 block w-full px-3 py-2 border @error('role') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white"
            >
                <option value="">-- Pilih Peran --</option>
                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin Sistem</option>
                <option value="dinas" {{ old('role', $user->role) === 'dinas' ? 'selected' : '' }}>Dinas Pendidikan</option>
                <option value="sekolah" {{ old('role', $user->role) === 'sekolah' ? 'selected' : '' }}>Operator Sekolah</option>
            </select>
            @error('role')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Pilih Sekolah (Khusus Role Sekolah) -->
        <div id="sekolah-wrapper" style="{{ old('role', $user->role ?? '') === 'sekolah' ? '' : 'display: none;' }}">
            <label for="sekolah_id" class="block text-sm font-medium text-gray-700">
                Pilih Sekolah <span class="text-red-500">*</span>
            </label>
            <select
                name="sekolah_id"
                id="sekolah_id"
                class="mt-1 block w-full px-3 py-2 border @error('sekolah_id') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white"
            >
                <option value="">-- Pilih Sekolah --</option>
                @foreach ($sekolahs as $s)
                    <option value="{{ $s->id }}" {{ (string) old('sekolah_id', $user->sekolah_id) === (string) $s->id ? 'selected' : '' }}>
                        {{ $s->nama }} (NPSN: {{ $s->npsn }})
                    </option>
                @endforeach
            </select>
            @error('sekolah_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Password & Konfirmasi -->
    <div class="border-t border-gray-200 pt-6">
        <h3 class="text-base font-semibold text-gray-900 mb-1">Pengaturan Kata Sandi</h3>
        @if ($user->exists)
            <p class="text-sm text-gray-500 mb-4">Kosongkan jika tidak diganti.</p>
        @else
            <p class="text-sm text-gray-500 mb-4">Minimal 8 karakter.</p>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Kata Sandi {{ $user->exists ? '' : '*' }}
                </label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    {{ $user->exists ? '' : 'required' }}
                    minlength="8"
                    placeholder="••••••••"
                    class="mt-1 block w-full px-3 py-2 border @error('password') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Ulangi Kata Sandi {{ $user->exists ? '' : '*' }}
                </label>
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    {{ $user->exists ? '' : 'required' }}
                    minlength="8"
                    placeholder="••••••••"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
            </div>
        </div>
    </div>

    <!-- Tombol Aksi -->
    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
            Batal
        </a>
        <button type="submit" class="px-5 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition cursor-pointer">
            Simpan Pengguna
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('role-select');
        const sekolahWrapper = document.getElementById('sekolah-wrapper');
        const sekolahSelect = document.getElementById('sekolah_id');

        function toggleSekolah() {
            if (roleSelect && sekolahWrapper) {
                if (roleSelect.value === 'sekolah') {
                    sekolahWrapper.style.display = 'block';
                } else {
                    sekolahWrapper.style.display = 'none';
                    if (sekolahSelect) {
                        sekolahSelect.value = '';
                    }
                }
            }
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', toggleSekolah);
            toggleSekolah();
        }
    });
</script>
