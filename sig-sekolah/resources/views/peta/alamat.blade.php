@extends('layouts.publik')

@section('title', 'Cek Lokasi & Alamat - SIG Sekolah')

@section('content')
<div style="position: relative; flex: 1; display: flex; width: 100%; height: 100%; overflow: hidden;">
    <!-- Panel Informasi Lokasi & Sekolah Terdekat -->
    <div id="panel-alamat" class="panel" style="top: 16px; left: 16px; width: 360px; max-height: calc(100% - 32px); display: flex; flex-direction: column; overflow: hidden; z-index: 1000;">
        <div style="padding: 12px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 700; font-size: 0.95rem; color: #1e293b;">
            Cek Lokasi &amp; Alamat
        </div>

        <div id="konten-alamat" style="flex: 1; overflow-y: auto; padding: 14px 16px; font-size: 0.85rem; line-height: 1.5; color: #334155;">
            <div id="petunjuk-awal" style="color: #64748b; text-align: center; padding: 20px 0;">
                Klik di mana saja pada peta untuk mengecek alamat dan melihat 3 sekolah terdekat.
            </div>

            <div id="detail-lokasi" style="display: none;">
                <div style="margin-bottom: 10px;">
                    <span style="font-weight: 600; color: #475569;">Koordinat:</span>
                    <div id="teks-koordinat" style="font-family: monospace; font-size: 0.85rem; color: #0f172a; margin-top: 2px;"></div>
                </div>

                <div style="margin-bottom: 10px;">
                    <span style="font-weight: 600; color: #475569;">Alamat Lengkap:</span>
                    <div id="teks-alamat-lengkap" style="color: #0f172a; margin-top: 2px; font-size: 0.82rem; background: #f8fafc; padding: 6px 8px; border-radius: 4px; border: 1px solid #e2e8f0;"></div>
                </div>

                <!-- Rincian Alamat -->
                <div style="margin-bottom: 12px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; font-size: 0.8rem;">
                    <div style="margin-bottom: 3px;"><strong>Jalan:</strong> <span id="rincian-jalan">-</span></div>
                    <div style="margin-bottom: 3px;"><strong>Kelurahan/Desa:</strong> <span id="rincian-desa">-</span></div>
                    <div style="margin-bottom: 3px;"><strong>Kota/Kabupaten:</strong> <span id="rincian-kota">-</span></div>
                    <div style="margin-bottom: 3px;"><strong>Provinsi:</strong> <span id="rincian-provinsi">-</span></div>
                    <div><strong>Kode Pos:</strong> <span id="rincian-pos">-</span></div>
                </div>

                <div style="font-weight: 700; color: #1e293b; margin-top: 14px; margin-bottom: 8px; font-size: 0.875rem;">
                    3 Sekolah Terdekat:
                </div>
                <div id="daftar-sekolah-terdekat" style="display: flex; flex-direction: column; gap: 8px;"></div>
            </div>
        </div>
    </div>

    <div id="peta" class="peta"></div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async function () {
        const map = Peta.buat('peta');

        // Layer titik klik pengguna & garis penghubung ke sekolah
        const hasilLayer = L.layerGroup().addTo(map);

        // Layer marker sekolah
        const sekolahLayer = L.layerGroup().addTo(map);

        const petunjukAwal = document.getElementById('petunjuk-awal');
        const detailLokasi = document.getElementById('detail-lokasi');
        const teksKoordinat = document.getElementById('teks-koordinat');
        const teksAlamatLengkap = document.getElementById('teks-alamat-lengkap');
        const rincianJalan = document.getElementById('rincian-jalan');
        const rincianDesa = document.getElementById('rincian-desa');
        const rincianKota = document.getElementById('rincian-kota');
        const rincianProvinsi = document.getElementById('rincian-provinsi');
        const rincianPos = document.getElementById('rincian-pos');
        const daftarSekolahTerdekat = document.getElementById('daftar-sekolah-terdekat');
        const panelAlamat = document.getElementById('panel-alamat');

        let semuaSekolah = [];
        let lastRequestTime = 0; // Pembatas laju 1200 ms
        const markerMap = new Map(); // npsn -> { marker, pos, fitur }

        try {
            semuaSekolah = await Peta.ambilSekolah();

            semuaSekolah.forEach(function (fitur) {
                const pos = Peta.latlng(fitur);
                if (pos) {
                    const marker = L.marker(pos);
                    marker.bindPopup(Peta.popup(fitur));
                    const npsn = (fitur.properties && fitur.properties.npsn) ? fitur.properties.npsn : '';
                    markerMap.set(npsn, {
                        marker: marker,
                        pos: pos,
                        fitur: fitur,
                    });
                }
            });
        } catch (err) {
            console.error(err);
        }

        // Event listener klik pada peta dengan pembatas laju 1200 ms
        map.on('click', async function (e) {
            const now = Date.now();
            if (now - lastRequestTime < 1200) {
                return; // Abaikan klik kurang dari 1200 ms dari permintaan sebelumnya
            }
            lastRequestTime = now;

            const lat = e.latlng.lat;
            const lon = e.latlng.lng;

            petunjukAwal.style.display = 'none';
            detailLokasi.style.display = 'block';

            hasilLayer.clearLayers();
            sekolahLayer.clearLayers();

            // 1. Tempatkan circleMarker merah pada titik klik
            L.circleMarker([lat, lon], {
                radius: 8,
                color: '#dc2626',
                fillColor: '#ef4444',
                fillOpacity: 0.9,
                weight: 2,
            }).addTo(hasilLayer);

            teksKoordinat.textContent = `${lat.toFixed(6)}, ${lon.toFixed(6)}`;
            teksAlamatLengkap.textContent = 'Memuat alamat...';
            rincianJalan.textContent = '-';
            rincianDesa.textContent = '-';
            rincianKota.textContent = '-';
            rincianProvinsi.textContent = '-';
            rincianPos.textContent = '-';

            // 2. Hitung tiga sekolah terdekat
            const jarakList = [];
            semuaSekolah.forEach(function (fitur) {
                const pos = Peta.latlng(fitur);
                if (pos) {
                    const d = Peta.jarakKm([lat, lon], pos);
                    jarakList.push({
                        fitur: fitur,
                        pos: pos,
                        jarak: d,
                    });
                }
            });

            jarakList.sort((a, b) => a.jarak - b.jarak);
            const tigaTerdekat = jarakList.slice(0, 3);

            // Tampilkan garis putus-putus biru dan marker untuk 3 sekolah terdekat
            daftarSekolahTerdekat.textContent = '';

            tigaTerdekat.forEach(function (item, idx) {
                const p = item.fitur.properties || {};
                const npsn = p.npsn || '';

                // Garis putus-putus biru dari titik ke sekolah
                L.polyline([[lat, lon], item.pos], {
                    color: '#2563eb',
                    weight: 2,
                    dashArray: '5, 5',
                }).addTo(hasilLayer);

                // Tambahkan marker sekolah ke layer
                const itemData = markerMap.get(npsn);
                if (itemData) {
                    sekolahLayer.addLayer(itemData.marker);
                }

                // Format jarak "x,xx km (garis lurus)"
                const jarakStr = `${item.jarak.toFixed(2).replace('.', ',')} km (garis lurus)`;

                // Kartu item sekolah di panel (DOM murni)
                const card = document.createElement('div');
                card.className = 'item-sekolah-terdekat';
                card.dataset.npsn = npsn;
                card.style.padding = '8px 10px';
                card.style.background = '#f8fafc';
                card.style.border = '1px solid #e2e8f0';
                card.style.borderRadius = '6px';
                card.style.cursor = 'pointer';
                card.style.transition = 'all 0.15s';

                card.addEventListener('mouseenter', function () {
                    card.style.background = '#eff6ff';
                    card.style.borderColor = '#bfdbfe';
                });
                card.addEventListener('mouseleave', function () {
                    card.style.background = '#f8fafc';
                    card.style.borderColor = '#e2e8f0';
                });

                const namaEl = document.createElement('div');
                namaEl.style.fontWeight = '600';
                namaEl.style.color = '#1e293b';
                namaEl.style.fontSize = '0.85rem';
                namaEl.textContent = `${idx + 1}. ${p.nama || '-'}`;

                const distEl = document.createElement('div');
                distEl.style.fontSize = '0.78rem';
                distEl.style.color = '#2563eb';
                distEl.style.fontWeight = '500';
                distEl.style.marginTop = '2px';
                distEl.textContent = jarakStr;

                const detailEl = document.createElement('div');
                detailEl.style.fontSize = '0.72rem';
                detailEl.style.color = '#64748b';
                detailEl.style.marginTop = '2px';
                detailEl.textContent = `${p.jenjang || '-'} • ${p.alamat || '-'}, ${p.kecamatan || '-'}`;

                card.appendChild(namaEl);
                card.appendChild(distEl);
                card.appendChild(detailEl);

                daftarSekolahTerdekat.appendChild(card);
            });

            // 3. Panggil Nominatim reverse geocoding (bahasa lewat URL)
            const nominatimUrl = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1&accept-language=id&lat=${lat}&lon=${lon}`;

            try {
                const res = await fetch(nominatimUrl);
                if (!res.ok) {
                    throw new Error('Nominatim respons tidak ok: ' + res.status);
                }

                const data = await res.json();
                const addr = data.address || {};

                teksAlamatLengkap.textContent = data.display_name ? data.display_name : 'Alamat tidak ditemukan.';

                rincianJalan.textContent = addr.road || '-';
                rincianDesa.textContent = addr.suburb || addr.neighbourhood || addr.village || '-';
                rincianKota.textContent = addr.city || addr.town || addr.municipality || addr.county || '-';
                rincianProvinsi.textContent = addr.state || '-';
                rincianPos.textContent = addr.postcode || '-';
            } catch (err) {
                console.error('Nominatim error:', err);
                teksAlamatLengkap.textContent = 'Alamat tidak dapat dimuat.';
            }
        });

        // Event delegation pada panel: klik item sekolah membuka popup dan flyTo
        panelAlamat.addEventListener('click', function (e) {
            const card = e.target.closest('.item-sekolah-terdekat');
            if (!card) return;

            const npsn = card.dataset.npsn;
            const itemData = markerMap.get(npsn);
            if (itemData) {
                map.flyTo(itemData.pos, 17);
                itemData.marker.openPopup();
            }
        });
    });
</script>
@endpush
