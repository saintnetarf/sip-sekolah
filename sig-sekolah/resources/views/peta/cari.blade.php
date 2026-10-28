@extends('layouts.publik')

@section('title', 'Pencarian Sekolah - SIG Sekolah')

@section('content')
<div class="toolbar">
    <div style="display: flex; align-items: center; gap: 8px;">
        <label for="input-cari" style="font-weight: 500; color: #4b5563;">Cari:</label>
        <input type="text" id="input-cari" class="form-input" placeholder="Cari nama, NPSN, atau kecamatan..." style="min-width: 250px;">
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

    <button type="button" id="btn-hapus" class="btn-action">Hapus</button>

    <div id="info-jumlah" class="badge">Memuat data...</div>
</div>

<div style="position: relative; flex: 1; display: flex; width: 100%; height: 100%; overflow: hidden;">
    <div id="peta" class="peta"></div>

    <div id="panel-pencarian" class="panel" style="top: 16px; left: 16px; width: 340px; max-height: calc(100% - 32px); display: flex; flex-direction: column; overflow: hidden;">
        <div style="padding: 10px 14px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #334155; font-size: 0.875rem;">
            Daftar Sekolah (<span id="panel-count">0</span>)
        </div>
        <div id="daftar-hasil" style="overflow-y: auto; flex: 1; padding: 6px 0;"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async function () {
        const map = Peta.buat('peta');
        const layerSekolah = L.layerGroup().addTo(map);

        const inputCari = document.getElementById('input-cari');
        const filterJenjang = document.getElementById('filter-jenjang');
        const btnHapus = document.getElementById('btn-hapus');
        const infoJumlah = document.getElementById('info-jumlah');
        const panelCount = document.getElementById('panel-count');
        const daftarHasil = document.getElementById('daftar-hasil');

        let semuaSekolah = [];
        const markerMap = new Map(); // npsn -> { marker, latlng, fitur }

        try {
            infoJumlah.textContent = 'Memuat data...';
            semuaSekolah = await Peta.ambilSekolah();

            semuaSekolah.forEach(function (fitur) {
                const pos = Peta.latlng(fitur);
                if (pos) {
                    const marker = L.marker(pos);
                    marker.bindPopup(Peta.popup(fitur));
                    const npsn = (fitur.properties && fitur.properties.npsn) ? fitur.properties.npsn : '';
                    markerMap.set(npsn, {
                        marker: marker,
                        latlng: pos,
                        fitur: fitur,
                    });
                }
            });

            terapkanFilter();
        } catch (err) {
            console.error(err);
            infoJumlah.textContent = 'Gagal memuat data';
        }

        function terapkanFilter() {
            const query = inputCari.value.trim().toLowerCase();
            const jenjang = filterJenjang.value;

            layerSekolah.clearLayers();
            daftarHasil.textContent = ''; // Kosongkan daftar hasil

            const cocok = semuaSekolah.filter(function (fitur) {
                const p = fitur.properties || {};
                const cocokJenjang = !jenjang || p.jenjang === jenjang;
                if (!cocokJenjang) return false;

                if (!query) return true;

                const nama = (p.nama || '').toLowerCase();
                const npsn = (p.npsn || '').toLowerCase();
                const kecamatan = (p.kecamatan || '').toLowerCase();

                return nama.includes(query) || npsn.includes(query) || kecamatan.includes(query);
            });

            infoJumlah.textContent = `${cocok.length} dari ${semuaSekolah.length} sekolah`;
            panelCount.textContent = cocok.length;

            if (cocok.length === 0) {
                const kosong = document.createElement('div');
                kosong.textContent = 'Tidak ada sekolah yang cocok.';
                kosong.style.padding = '14px';
                kosong.style.color = '#64748b';
                kosong.style.textAlign = 'center';
                daftarHasil.appendChild(kosong);
                return;
            }

            const latlngs = [];

            cocok.forEach(function (fitur) {
                const p = fitur.properties || {};
                const npsn = p.npsn || '';
                const itemData = markerMap.get(npsn);

                if (itemData) {
                    layerSekolah.addLayer(itemData.marker);
                    latlngs.push(itemData.latlng);

                    // Buat elemen daftar dengan textContent (aman dari XSS)
                    const itemDiv = document.createElement('div');
                    itemDiv.style.padding = '8px 12px';
                    itemDiv.style.borderBottom = '1px solid #f1f5f9';
                    itemDiv.style.cursor = 'pointer';
                    itemDiv.style.transition = 'background-color 0.15s';

                    itemDiv.addEventListener('mouseenter', function () {
                        itemDiv.style.backgroundColor = '#f1f5f9';
                    });
                    itemDiv.addEventListener('mouseleave', function () {
                        itemDiv.style.backgroundColor = 'transparent';
                    });

                    const namaEl = document.createElement('div');
                    namaEl.style.fontWeight = '600';
                    namaEl.style.color = '#1e293b';
                    namaEl.style.fontSize = '0.85rem';
                    namaEl.textContent = p.nama || '-';

                    const detailEl = document.createElement('div');
                    detailEl.style.fontSize = '0.75rem';
                    detailEl.style.color = '#64748b';
                    detailEl.style.marginTop = '2px';
                    detailEl.textContent = `${p.npsn || '-'} • ${p.jenjang || '-'} (${p.status || '-'}) • ${p.kecamatan || '-'}`;

                    itemDiv.appendChild(namaEl);
                    itemDiv.appendChild(detailEl);

                    itemDiv.addEventListener('click', function () {
                        map.flyTo(itemData.latlng, 17);
                        itemData.marker.openPopup();
                    });

                    daftarHasil.appendChild(itemDiv);
                }
            });

            if (latlngs.length > 0 && query !== '') {
                map.fitBounds(L.latLngBounds(latlngs).pad(0.1));
            }
        }

        // Debounce 300 ms untuk input cari
        let debounceTimer;
        inputCari.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(terapkanFilter, 300);
        });

        filterJenjang.addEventListener('change', terapkanFilter);

        btnHapus.addEventListener('click', function () {
            inputCari.value = '';
            filterJenjang.value = '';
            terapkanFilter();
        });
    });
</script>
@endpush
