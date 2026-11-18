<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- NPSN -->
        <div>
            <label for="npsn" class="block text-sm font-medium text-gray-700">NPSN <span class="text-red-500">*</span></label>
            <input type="text" name="npsn" id="npsn" maxlength="8" value="{{ old('npsn', $sekolah->npsn) }}" @readonly(auth()->user()->isSekolah()) placeholder="8 digit angka (contoh: 30000001)" class="mt-1 block w-full px-3 py-2 border @error('npsn') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror {{ auth()->user()->isSekolah() ? 'bg-gray-100 cursor-not-allowed text-gray-600' : '' }} rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-mono">
            @if (auth()->user()->isSekolah())
                <p class="mt-1 text-xs text-gray-500">NPSN tidak dapat diubah oleh operator sekolah.</p>
            @endif
            @error('npsn')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nama Sekolah -->
        <div>
            <label for="nama" class="block text-sm font-medium text-gray-700">Nama Sekolah <span class="text-red-500">*</span></label>
            <input type="text" name="nama" id="nama" value="{{ old('nama', $sekolah->nama) }}" placeholder="Contoh: SD Negeri Contoh 1" class="mt-1 block w-full px-3 py-2 border @error('nama') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            @error('nama')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jenjang -->
        <div>
            <label for="jenjang" class="block text-sm font-medium text-gray-700">Jenjang <span class="text-red-500">*</span></label>
            <select name="jenjang" id="jenjang" class="mt-1 block w-full px-3 py-2 border @error('jenjang') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white">
                <option value="">-- Pilih Jenjang --</option>
                @foreach (['SD', 'SMP', 'SMA', 'SMK'] as $j)
                    <option value="{{ $j }}" {{ old('jenjang', $sekolah->jenjang) === $j ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>
            @error('jenjang')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Status -->
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
            <select name="status" id="status" class="mt-1 block w-full px-3 py-2 border @error('status') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white">
                <option value="">-- Pilih Status --</option>
                @foreach (['Negeri', 'Swasta'] as $s)
                    <option value="{{ $s }}" {{ old('status', $sekolah->status) === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kecamatan -->
        <div>
            <label for="kecamatan" class="block text-sm font-medium text-gray-700">Kecamatan <span class="text-red-500">*</span></label>
            <input type="text" name="kecamatan" id="kecamatan" value="{{ old('kecamatan', $sekolah->kecamatan) }}" placeholder="Contoh: Banjarmasin Tengah" class="mt-1 block w-full px-3 py-2 border @error('kecamatan') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            @error('kecamatan')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Telepon -->
        <div>
            <label for="telepon" class="block text-sm font-medium text-gray-700">Telepon</label>
            <input type="text" name="telepon" id="telepon" value="{{ old('telepon', $sekolah->telepon) }}" placeholder="Contoh: 0511-3351001 (opsional)" class="mt-1 block w-full px-3 py-2 border @error('telepon') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            @error('telepon')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Alamat -->
    <div>
        <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat Lengkap <span class="text-red-500">*</span></label>
        <textarea name="alamat" id="alamat" rows="2" placeholder="Masukkan alamat lengkap sekolah..." class="mt-1 block w-full px-3 py-2 border @error('alamat') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('alamat', $sekolah->alamat) }}</textarea>
        @error('alamat')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Pemilih Koordinat Peta -->
    <div class="border-t border-gray-200 pt-6">
        <div class="mb-3">
            <h3 class="text-base font-semibold text-gray-900">Titik Koordinat Lokasi</h3>
            <p class="text-sm text-gray-500">Klik pada peta atau geser marker untuk menentukan koordinat otomatis, atau masukkan angka secara manual.</p>
        </div>

        <div id="map-picker" class="w-full h-80 rounded-xl border border-gray-300 shadow-inner z-0"></div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <div>
                <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude <span class="text-red-500">*</span></label>
                <input type="number" step="any" name="latitude" id="latitude" value="{{ old('latitude', $sekolah->latitude) }}" placeholder="-3.3194000" class="mt-1 block w-full px-3 py-2 border @error('latitude') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-mono">
                @error('latitude')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude <span class="text-red-500">*</span></label>
                <input type="number" step="any" name="longitude" id="longitude" value="{{ old('longitude', $sekolah->longitude) }}" placeholder="114.5908000" class="mt-1 block w-full px-3 py-2 border @error('longitude') border-red-300 ring-1 ring-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-mono">
                @error('longitude')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Tombol Aksi -->
    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
        <a href="{{ auth()->user()->isDinas() ? route('dinas.sekolah.index') : route('dashboard') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
            Batal
        </a>
        <button type="submit" class="px-5 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
            Simpan Data
        </button>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const defaultLat = -3.3194;
    const defaultLng = 114.5908;

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    let initialLat = parseFloat(latInput.value);
    let initialLng = parseFloat(lngInput.value);

    const hasInitialCoords = !isNaN(initialLat) && !isNaN(initialLng);
    const centerLat = hasInitialCoords ? initialLat : defaultLat;
    const centerLng = hasInitialCoords ? initialLng : defaultLng;
    const initialZoom = hasInitialCoords ? 16 : 13;

    const map = L.map('map-picker').setView([centerLat, centerLng], initialZoom);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    let marker = null;

    function createOrUpdateMarker(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                latInput.value = pos.lat.toFixed(7);
                lngInput.value = pos.lng.toFixed(7);
            });
        }
    }

    if (hasInitialCoords) {
        createOrUpdateMarker(initialLat, initialLng);
    }

    // Klik pada peta memindahkan marker dan mengisi koordinat
    map.on('click', function (e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
        createOrUpdateMarker(lat, lng);
    });

    // Mengetik koordinat manual memindahkan marker dan peta
    function onManualCoordsInput() {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);

        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            createOrUpdateMarker(lat, lng);
            map.panTo([lat, lng]);
        }
    }

    latInput.addEventListener('input', onManualCoordsInput);
    lngInput.addEventListener('input', onManualCoordsInput);
});
</script>
@endpush
