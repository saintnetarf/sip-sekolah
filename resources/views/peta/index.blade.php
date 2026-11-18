@extends('layouts.publik')

@section('title', 'Peta Sekolah - SIG Sekolah')

@section('content')
<div class="toolbar">
    <div style="display: flex; align-items: center; gap: 8px;">
        <label for="filter-jenjang" style="font-weight: 500; color: #4b5563;">Filter Jenjang:</label>
        <select id="filter-jenjang" class="form-select">
            <option value="">Semua Jenjang</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
            <option value="SMK">SMK</option>
        </select>
    </div>
    <div id="jumlah-sekolah" class="badge">Memuat data...</div>
</div>

<div id="peta" class="peta"></div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = Peta.buat('peta');
        const layerSekolah = L.layerGroup().addTo(map);
        const filterJenjang = document.getElementById('filter-jenjang');
        const infoJumlah = document.getElementById('jumlah-sekolah');

        async function muatData() {
            infoJumlah.textContent = 'Memuat data...';
            layerSekolah.clearLayers();

            try {
                const jenjang = filterJenjang.value;
                const fitur = await Peta.ambilSekolah(jenjang);

                const latlngs = [];
                fitur.forEach(function (f) {
                    const pos = Peta.latlng(f);
                    if (pos) {
                        latlngs.push(pos);
                        const marker = L.marker(pos);
                        marker.bindPopup(Peta.popup(f));
                        layerSekolah.addLayer(marker);
                    }
                });

                infoJumlah.textContent = `${fitur.length} sekolah`;

                if (latlngs.length > 0) {
                    map.fitBounds(L.latLngBounds(latlngs).pad(0.1));
                }
            } catch (err) {
                console.error(err);
                infoJumlah.textContent = 'Gagal memuat data';
            }
        }

        filterJenjang.addEventListener('change', muatData);
        muatData();
    });
</script>
@endpush
