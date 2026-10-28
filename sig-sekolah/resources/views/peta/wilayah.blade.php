@extends('layouts.publik')

@section('title', 'Wilayah Sebaran Sekolah - SIG Sekolah')

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
    <div id="jumlah-wilayah" class="badge">Memuat data...</div>
</div>

<div style="position: relative; flex: 1; display: flex; width: 100%; height: 100%; overflow: hidden;">
    <div id="peta" class="peta"></div>

    <!-- Panel Legenda Relatif -->
    <div class="panel" style="bottom: 24px; right: 24px; padding: 12px 16px; width: 260px;">
        <div style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 8px;">
            Kepadatan Sekolah Relatif
        </div>
        <div style="display: flex; flex-direction: column; gap: 6px; font-size: 0.78rem; color: #475569;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="display: inline-block; width: 14px; height: 14px; background-color: #dc2626; border-radius: 3px; opacity: 0.8;"></span>
                <span>Tinggi (&ge; 80% dari maks)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="display: inline-block; width: 14px; height: 14px; background-color: #ea580c; border-radius: 3px; opacity: 0.8;"></span>
                <span>Sedang-Tinggi (60% - 79%)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="display: inline-block; width: 14px; height: 14px; background-color: #f59e0b; border-radius: 3px; opacity: 0.8;"></span>
                <span>Sedang (40% - 59%)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="display: inline-block; width: 14px; height: 14px; background-color: #3b82f6; border-radius: 3px; opacity: 0.8;"></span>
                <span>Rendah (&lt; 40%)</span>
            </div>
        </div>
        <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed #cbd5e1; font-size: 0.7rem; color: #64748b; line-height: 1.35;">
            * Catatan: Poligon merupakan perkiraan sebaran sekolah (Convex Hull), bukan batas wilayah administrasi resmi.
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = Peta.buat('peta');
        let geoJsonLayer = null;

        const filterJenjang = document.getElementById('filter-jenjang');
        const infoJumlah = document.getElementById('jumlah-wilayah');

        function tentukanWarna(rasio) {
            if (rasio >= 0.8) return '#dc2626';
            if (rasio >= 0.6) return '#ea580c';
            if (rasio >= 0.4) return '#f59e0b';
            return '#3b82f6';
        }

        async function muatWilayah() {
            infoJumlah.textContent = 'Memuat data...';

            if (geoJsonLayer) {
                map.removeLayer(geoJsonLayer);
                geoJsonLayer = null;
            }

            try {
                const jenjang = filterJenjang.value;
                const baseUrl = window.PETA_URL && window.PETA_URL.wilayah ? window.PETA_URL.wilayah : '/data/wilayah';
                const url = new URL(baseUrl, window.location.origin);
                if (jenjang) {
                    url.searchParams.set('jenjang', jenjang);
                }

                const res = await fetch(url.toString());
                if (!res.ok) {
                    throw new Error('Gagal memuat data wilayah: ' + res.statusText);
                }

                const data = await res.json();
                const features = data.features || [];

                infoJumlah.textContent = `${features.length} wilayah kecamatan`;

                if (features.length === 0) {
                    return;
                }

                let maxSekolah = 1;
                features.forEach(function (f) {
                    const js = (f.properties && f.properties.jumlah_sekolah) ? f.properties.jumlah_sekolah : 0;
                    if (js > maxSekolah) {
                        maxSekolah = js;
                    }
                });

                geoJsonLayer = L.geoJSON(data, {
                    style: function (fitur) {
                        const js = (fitur.properties && fitur.properties.jumlah_sekolah) ? fitur.properties.jumlah_sekolah : 0;
                        const rasio = js / maxSekolah;
                        const warna = tentukanWarna(rasio);
                        return {
                            color: warna,
                            fillColor: warna,
                            weight: 3,
                            fillOpacity: 0.3,
                        };
                    },
                    onEachFeature: function (fitur, layer) {
                        const p = fitur.properties || {};
                        const kec = p.kecamatan ? Peta.esc(p.kecamatan) : '-';
                        const js = p.jumlah_sekolah !== undefined ? Peta.esc(p.jumlah_sekolah) : '0';
                        const jsiswa = p.jumlah_siswa !== undefined ? Peta.esc(p.jumlah_siswa) : '0';

                        layer.bindTooltip(kec, {
                            permanent: false,
                            direction: 'center',
                            className: 'tooltip-wilayah',
                        });

                        layer.bindPopup(`
                            <div style="font-family: inherit; font-size: 13px; line-height: 1.5; min-width: 180px; color: #1e293b;">
                                <div style="font-weight: 700; font-size: 14px; margin-bottom: 6px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">
                                    ${kec}
                                </div>
                                <div style="margin-bottom: 3px;"><strong>Jumlah Sekolah:</strong> ${js}</div>
                                <div style="margin-bottom: 3px;"><strong>Total Siswa:</strong> ${jsiswa}</div>
                                <div style="margin-top: 6px; font-size: 11px; color: #64748b; font-style: italic;">Perkiraan sebaran (Convex Hull)</div>
                            </div>
                        `);
                    },
                }).addTo(map);

                map.fitBounds(geoJsonLayer.getBounds().pad(0.1));
            } catch (err) {
                console.error(err);
                infoJumlah.textContent = 'Gagal memuat data wilayah';
            }
        }

        filterJenjang.addEventListener('change', muatWilayah);
        muatWilayah();
    });
</script>
@endpush
