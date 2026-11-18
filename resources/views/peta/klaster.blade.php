@extends('layouts.publik')

@section('title', 'Klaster Sekolah - SIG Sekolah')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css"/>
@endpush

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
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = Peta.buat('peta');
        const clusterGroup = L.markerClusterGroup({
            showCoverageOnHover: false,
            maxClusterRadius: 60,
            spiderfyOnMaxZoom: true,
            disableClusteringAtZoom: 17,
            chunkedLoading: true,
        }).addTo(map);

        const filterJenjang = document.getElementById('filter-jenjang');
        const infoJumlah = document.getElementById('jumlah-sekolah');

        async function muatData() {
            infoJumlah.textContent = 'Memuat data...';
            clusterGroup.clearLayers();

            try {
                const jenjang = filterJenjang.value;
                const fitur = await Peta.ambilSekolah(jenjang);

                const markers = [];
                fitur.forEach(function (f) {
                    const pos = Peta.latlng(f);
                    if (pos) {
                        const marker = L.marker(pos);
                        marker.bindPopup(Peta.popup(f));
                        markers.push(marker);
                    }
                });

                clusterGroup.addLayers(markers);
                infoJumlah.textContent = `${fitur.length} sekolah`;

                if (markers.length > 0) {
                    map.fitBounds(clusterGroup.getBounds().pad(0.1));
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
