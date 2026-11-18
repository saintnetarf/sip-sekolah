@extends('layouts.publik')

@section('title', 'Peta Panas - SIG Sekolah')

@section('content')
<div class="toolbar">
    <div style="display: flex; align-items: center; gap: 8px;">
        <label for="filter-bobot" style="font-weight: 500; color: #4b5563;">Bobot:</label>
        <select id="filter-bobot" class="form-select">
            <option value="sekolah">Kepadatan Sekolah</option>
            <option value="siswa">Jumlah Siswa</option>
        </select>
    </div>

    <div style="display: flex; align-items: center; gap: 8px;">
        <label for="filter-jenjang" style="font-weight: 500; color: #4b5563;">Jenjang:</label>
        <select id="filter-jenjang" class="form-select">
            <option value="">Semua Jenjang</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
            <option value="SMK">SMK</option>
        </select>
    </div>

    <div style="display: flex; align-items: center; gap: 8px;">
        <label for="slider-radius" style="font-weight: 500; color: #4b5563;">Radius:</label>
        <input type="range" id="slider-radius" min="10" max="60" value="30" style="vertical-align: middle; cursor: pointer;">
        <span id="label-radius" style="font-size: 0.85rem; color: #64748b; min-width: 40px;">30 px</span>
    </div>

    <div id="jumlah-sekolah" class="badge">Memuat data...</div>
</div>

<div style="position: relative; flex: 1; display: flex; width: 100%; height: 100%; overflow: hidden;">
    <div id="peta" class="peta"></div>

    <!-- Panel Legenda Gradien -->
    <div class="panel" style="bottom: 24px; right: 24px; padding: 10px 14px; width: 220px;">
        <div style="font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 6px;">
            Tingkat Kepadatan
        </div>
        <div style="height: 12px; border-radius: 6px; background: linear-gradient(to right, #2563eb, #06b6d4, #84cc16, #facc15, #dc2626);"></div>
        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b; margin-top: 4px;">
            <span>Rendah</span>
            <span>Tinggi</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = Peta.buat('peta');
        let heatLayer = null;

        const filterBobot = document.getElementById('filter-bobot');
        const filterJenjang = document.getElementById('filter-jenjang');
        const sliderRadius = document.getElementById('slider-radius');
        const labelRadius = document.getElementById('label-radius');
        const infoJumlah = document.getElementById('jumlah-sekolah');

        let dataSekolah = [];

        function renderHeatmap() {
            if (heatLayer) {
                map.removeLayer(heatLayer);
                heatLayer = null;
            }

            const modeBobot = filterBobot.value;
            const radius = parseInt(sliderRadius.value, 10) || 30;

            let maxSiswa = 1;
            if (modeBobot === 'siswa') {
                dataSekolah.forEach(function (f) {
                    const s = (f.properties && f.properties.jumlah_siswa) ? Number(f.properties.jumlah_siswa) : 0;
                    if (s > maxSiswa) {
                        maxSiswa = s;
                    }
                });
            }

            const points = [];
            const latlngs = [];

            dataSekolah.forEach(function (f) {
                const pos = Peta.latlng(f);
                if (pos) {
                    latlngs.push(pos);
                    let intensitas = 0.35;
                    if (modeBobot === 'siswa') {
                        const s = (f.properties && f.properties.jumlah_siswa) ? Number(f.properties.jumlah_siswa) : 0;
                        intensitas = Math.max(s / maxSiswa, 0.1);
                    }
                    points.push([pos[0], pos[1], intensitas]);
                }
            });

            if (points.length > 0) {
                heatLayer = L.heatLayer(points, {
                    radius: radius,
                    blur: 25,
                    maxZoom: 17,
                    max: 1.0,
                    gradient: {
                        0.2: '#2563eb',
                        0.4: '#06b6d4',
                        0.6: '#84cc16',
                        0.8: '#facc15',
                        1.0: '#dc2626',
                    },
                }).addTo(map);
            }
        }

        async function muatData() {
            infoJumlah.textContent = 'Memuat data...';
            try {
                const jenjang = filterJenjang.value;
                dataSekolah = await Peta.ambilSekolah(jenjang);
                infoJumlah.textContent = `${dataSekolah.length} sekolah`;

                renderHeatmap();

                const latlngs = [];
                dataSekolah.forEach(function (f) {
                    const pos = Peta.latlng(f);
                    if (pos) latlngs.push(pos);
                });

                if (latlngs.length > 0) {
                    map.fitBounds(L.latLngBounds(latlngs).pad(0.1));
                }
            } catch (err) {
                console.error(err);
                infoJumlah.textContent = 'Gagal memuat data';
            }
        }

        filterJenjang.addEventListener('change', muatData);

        filterBobot.addEventListener('change', function () {
            renderHeatmap();
        });

        sliderRadius.addEventListener('input', function () {
            labelRadius.textContent = `${this.value} px`;
            renderHeatmap();
        });

        muatData();
    });
</script>
@endpush
