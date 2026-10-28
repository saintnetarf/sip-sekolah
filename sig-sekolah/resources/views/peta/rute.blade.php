@extends('layouts.publik')

@section('title', 'Rute Antar Sekolah - SIG Sekolah')

@section('content')
<div style="position: relative; flex: 1; display: flex; width: 100%; height: 100%; overflow: hidden;">
    <!-- Panel Kiri: Pilihan Sekolah & Urutan Rute -->
    <div id="panel-rute" class="panel" style="top: 12px; left: 12px; width: 340px; max-height: calc(100% - 24px); display: flex; flex-direction: column; overflow: hidden; z-index: 1000;">
        <div style="padding: 12px 14px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <div style="font-weight: 700; font-size: 0.95rem; color: #1e293b; margin-bottom: 8px;">
                Rute Perjalanan Sekolah
            </div>
            <input type="text" id="cari-sekolah-rute" class="form-input" placeholder="Cari nama atau kecamatan..." style="width: 100%;">
        </div>

        <!-- Daftar Sekolah dengan Checkbox (DOM constructed) -->
        <div id="daftar-sekolah-rute" style="flex: 1; overflow-y: auto; padding: 6px 0; max-height: 260px; border-bottom: 1px solid #e2e8f0;">
            <div style="padding: 10px 14px; font-size: 0.85rem; color: #64748b;">Memuat data sekolah...</div>
        </div>

        <!-- Bagian Urutan & Tombol Aksi -->
        <div style="padding: 12px 14px; background: #ffffff; display: flex; flex-direction: column; gap: 8px;">
            <div style="font-size: 0.8rem; font-weight: 600; color: #475569;">
                Urutan Rute (<span id="jumlah-terpilih">0</span>/10):
            </div>
            <div id="teks-urutan" style="font-size: 0.78rem; color: #334155; min-height: 24px; max-height: 60px; overflow-y: auto; line-height: 1.4; padding: 4px 6px; background: #f8fafc; border-radius: 4px; border: 1px solid #e2e8f0;">
                Belum ada sekolah yang dipilih.
            </div>

            <div style="display: flex; gap: 8px; margin-top: 4px;">
                <button type="button" id="btn-buat-rute" class="btn-action btn-primary" style="flex: 1; justify-content: center;">
                    Buat Rute
                </button>
                <button type="button" id="btn-reset-rute" class="btn-action" style="justify-content: center;">
                    Reset
                </button>
            </div>
        </div>

        <!-- Panel Informasi Rute -->
        <div id="info-rute" style="display: none; padding: 12px 14px; background: #eff6ff; border-top: 1px solid #bfdbfe; font-size: 0.8rem; color: #1e3a8a;">
            <div style="font-weight: 700; margin-bottom: 4px; color: #1d4ed8;">Informasi Rute</div>
            <div id="info-rute-titik" style="margin-bottom: 2px;">Jumlah titik: <strong>0</strong></div>
            <div id="info-rute-jarak" style="margin-bottom: 2px;">Total jarak: <strong>0 km</strong></div>
            <div id="info-rute-waktu" style="margin-bottom: 2px;">Perkiraan waktu: <strong>-</strong></div>
            <div id="info-rute-sumber" style="font-size: 0.75rem; color: #3b82f6; margin-top: 4px;">Sumber: <strong>-</strong></div>
        </div>
    </div>

    <div id="peta" class="peta"></div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet-polylinedecorator@1.6.0/dist/leaflet.polylineDecorator.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', async function () {
        const map = Peta.buat('peta');

        // Layer konteks untuk seluruh sekolah (circleMarker abu-abu)
        const contextLayer = L.layerGroup().addTo(map);

        // Layer rute (polyline + panah + marker nomor)
        const ruteLayer = L.layerGroup().addTo(map);

        const cariInput = document.getElementById('cari-sekolah-rute');
        const containerDaftar = document.getElementById('daftar-sekolah-rute');
        const teksUrutan = document.getElementById('teks-urutan');
        const jumlahTerpilih = document.getElementById('jumlah-terpilih');
        const btnBuatRute = document.getElementById('btn-buat-rute');
        const btnResetRute = document.getElementById('btn-reset-rute');

        const infoRuteBox = document.getElementById('info-rute');
        const infoRuteTitik = document.getElementById('info-rute-titik');
        const infoRuteJarak = document.getElementById('info-rute-jarak');
        const infoRuteWaktu = document.getElementById('info-rute-waktu');
        const infoRuteSumber = document.getElementById('info-rute-sumber');

        let semuaSekolah = [];
        let terpilih = []; // array of { id, nama, latlng, fitur, checkbox }
        const itemDaftarElements = [];

        try {
            semuaSekolah = await Peta.ambilSekolah();

            // Tampilkan context marker abu-abu untuk seluruh sekolah
            semuaSekolah.forEach(function (f) {
                const pos = Peta.latlng(f);
                if (pos) {
                    const circle = L.circleMarker(pos, {
                        radius: 5,
                        color: '#94a3b8',
                        fillColor: '#cbd5e1',
                        fillOpacity: 0.8,
                        weight: 1,
                    });
                    const nama = (f.properties && f.properties.nama) ? f.properties.nama : 'Sekolah';
                    circle.bindTooltip(Peta.esc(nama), { direction: 'top' });
                    contextLayer.addLayer(circle);
                }
            });

            bangunDaftarSekolah();
        } catch (err) {
            console.error(err);
            containerDaftar.textContent = 'Gagal memuat data sekolah.';
        }

        // Bangun elemen daftar checkbox menggunakan DOM murni (aman dari XSS)
        function bangunDaftarSekolah() {
            containerDaftar.textContent = '';

            semuaSekolah.forEach(function (fitur, index) {
                const pos = Peta.latlng(fitur);
                if (!pos) return;

                const p = fitur.properties || {};
                const id = p.npsn || String(index);
                const nama = p.nama || `Sekolah ${index + 1}`;
                const kecamatan = p.kecamatan || '';

                const row = document.createElement('label');
                row.style.display = 'flex';
                row.style.alignItems = 'center';
                row.style.gap = '8px';
                row.style.padding = '6px 12px';
                row.style.cursor = 'pointer';
                row.style.fontSize = '0.82rem';
                row.style.borderBottom = '1px solid #f8fafc';
                row.style.transition = 'background-color 0.15s';

                row.addEventListener('mouseenter', function () {
                    row.style.backgroundColor = '#f1f5f9';
                });
                row.addEventListener('mouseleave', function () {
                    row.style.backgroundColor = 'transparent';
                });

                const cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.value = id;
                cb.style.cursor = 'pointer';

                const textSpan = document.createElement('span');
                textSpan.style.color = '#334155';
                textSpan.textContent = `${nama} (${p.jenjang || '-'})`;

                cb.addEventListener('change', function (e) {
                    if (cb.checked) {
                        if (terpilih.length >= 10) {
                            cb.checked = false;
                            alert('Maksimal 10 sekolah untuk rute!');
                            return;
                        }
                        terpilih.push({
                            id: id,
                            nama: nama,
                            latlng: pos,
                            fitur: fitur,
                            checkbox: cb,
                        });
                    } else {
                        terpilih = terpilih.filter(item => item.id !== id);
                    }
                    perbaruiUrutan();
                });

                row.appendChild(cb);
                row.appendChild(textSpan);
                containerDaftar.appendChild(row);

                itemDaftarElements.push({
                    row: row,
                    namaLower: nama.toLowerCase(),
                    kecamatanLower: kecamatan.toLowerCase(),
                });
            });
        }

        function perbaruiUrutan() {
            jumlahTerpilih.textContent = terpilih.length;
            if (terpilih.length === 0) {
                teksUrutan.textContent = 'Belum ada sekolah yang dipilih.';
                return;
            }

            teksUrutan.textContent = terpilih.map((item, idx) => `${idx + 1}. ${item.nama}`).join(' → ');
        }

        // Pencarian daftar sekolah pada panel kiri
        cariInput.addEventListener('input', function () {
            const q = cariInput.value.trim().toLowerCase();
            itemDaftarElements.forEach(function (item) {
                if (!q || item.namaLower.includes(q) || item.kecamatanLower.includes(q)) {
                    item.row.style.display = 'flex';
                } else {
                    item.row.style.display = 'none';
                }
            });
        });

        /**
         * Mengambil rute dari OSRM demo dengan fallback garis lurus
         */
        async function ambilRute(titik) {
            // Urutan koordinat OSRM: longitude,latitude;...
            const osrmCoords = titik.map(t => `${t.latlng[1]},${t.latlng[0]}`).join(';');
            const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${osrmCoords}?overview=full&geometries=geojson`;

            try {
                const res = await fetch(osrmUrl);
                if (!res.ok) {
                    throw new Error('OSRM respons tidak ok: ' + res.status);
                }
                const data = await res.json();
                if (data.code !== 'Ok' || !data.routes || data.routes.length === 0) {
                    throw new Error('OSRM tidak menemukan rute.');
                }

                const r = data.routes[0];
                // Balik koordinat GeoJSON [lng, lat] menjadi Leaflet [lat, lng]
                const garis = r.geometry.coordinates.map(c => [c[1], c[0]]);
                const jarakKm = (r.distance / 1000).toFixed(2);
                const durasiMenit = Math.round(r.duration / 60);

                return {
                    garis: garis,
                    jarak: jarakKm,
                    durasi: durasiMenit,
                    legs: r.legs || [],
                    sumber: 'OSRM',
                };
            } catch (err) {
                console.error('OSRM gagal, beralih ke cadangan garis lurus:', err);

                // Cadangan wajib: Garis lurus antar titik dengan Haversine
                const garis = titik.map(t => t.latlng);
                const legs = [];
                let totalJarak = 0;

                for (let i = 0; i < titik.length - 1; i++) {
                    const d = Peta.jarakKm(titik[i].latlng, titik[i + 1].latlng);
                    totalJarak += d;
                    legs.push({
                        distance: d * 1000,
                        duration: null,
                    });
                }

                return {
                    garis: garis,
                    jarak: totalJarak.toFixed(2),
                    durasi: null,
                    legs: legs,
                    sumber: 'Garis lurus',
                };
            }
        }

        // Tombol Buat Rute
        btnBuatRute.addEventListener('click', async function () {
            if (terpilih.length < 2) {
                alert('Pilih minimal 2 sekolah untuk membuat rute!');
                return;
            }

            btnBuatRute.disabled = true;
            btnBuatRute.textContent = 'Membuat Rute...';
            ruteLayer.clearLayers();

            try {
                const hasil = await ambilRute(terpilih);

                // 1. Gambar Polyline rute berwarna biru
                const polyline = L.polyline(hasil.garis, {
                    color: '#2563eb',
                    weight: 5,
                    opacity: 0.85,
                }).addTo(ruteLayer);

                // 2. Tambahkan decorator panah arah rute
                if (typeof L.polylineDecorator === 'function') {
                    L.polylineDecorator(polyline, {
                        patterns: [
                            {
                                offset: '5%',
                                repeat: 100,
                                symbol: L.Symbol.arrowHead({
                                    pixelSize: 12,
                                    polygon: false,
                                    pathOptions: {
                                        stroke: true,
                                        color: '#1d4ed8',
                                        weight: 2,
                                    },
                                }),
                            },
                        ],
                    }).addTo(ruteLayer);
                }

                // 3. Tambahkan penanda bernomor Peta.ikonAngka
                // Awal: hijau (#16a34a), Akhir: merah (#dc2626), Lainnya: biru (#2563eb)
                terpilih.forEach(function (item, idx) {
                    const nomor = idx + 1;
                    let warna = '#2563eb';
                    if (idx === 0) {
                        warna = '#16a34a';
                    } else if (idx === terpilih.length - 1) {
                        warna = '#dc2626';
                    }

                    const marker = L.marker(item.latlng, {
                        icon: Peta.ikonAngka(nomor, warna),
                    });

                    const p = item.fitur.properties || {};
                    let infoJarakRuas = 'Titik Awal';
                    if (idx > 0 && hasil.legs && hasil.legs[idx - 1]) {
                        const dKm = (hasil.legs[idx - 1].distance / 1000).toFixed(2);
                        infoJarakRuas = `${dKm} km dari titik sebelumnya`;
                    }

                    marker.bindPopup(`
                        <div style="font-family: inherit; font-size: 13px; line-height: 1.4; min-width: 200px;">
                            <div style="font-weight: 700; font-size: 14px; color: #1e293b; margin-bottom: 4px;">
                                #${nomor} ${Peta.esc(item.nama)}
                            </div>
                            <div style="margin-bottom: 2px;"><strong>Alamat:</strong> ${Peta.esc(p.alamat || '-')}</div>
                            <div style="margin-top: 4px; padding-top: 4px; border-top: 1px dashed #e2e8f0; font-size: 12px; color: #4338ca;">
                                <strong>Jarak:</strong> ${Peta.esc(infoJarakRuas)}
                            </div>
                        </div>
                    `);

                    ruteLayer.addLayer(marker);
                });

                // 4. Perbarui Panel Informasi Rute
                infoRuteBox.style.display = 'block';
                infoRuteTitik.innerHTML = `Jumlah titik: <strong>${terpilih.length} sekolah</strong>`;
                infoRuteJarak.innerHTML = `Total jarak: <strong>${hasil.jarak} km</strong>`;
                infoRuteWaktu.innerHTML = `Perkiraan waktu: <strong>${hasil.durasi !== null ? hasil.durasi + ' menit' : '-'}</strong>`;
                infoRuteSumber.innerHTML = `Sumber rute: <strong>${Peta.esc(hasil.sumber)}</strong>`;

                // Fit map bounds ke rute
                map.fitBounds(polyline.getBounds().pad(0.15));
            } catch (err) {
                console.error(err);
                alert('Gagal memproses rute.');
            } finally {
                btnBuatRute.disabled = false;
                btnBuatRute.textContent = 'Buat Rute';
            }
        });

        // Tombol Reset Rute
        btnResetRute.addEventListener('click', function () {
            terpilih.forEach(item => {
                if (item.checkbox) item.checkbox.checked = false;
            });
            terpilih = [];
            perbaruiUrutan();
            ruteLayer.clearLayers();
            infoRuteBox.style.display = 'none';
            cariInput.value = '';
            itemDaftarElements.forEach(item => {
                item.row.style.display = 'flex';
            });
            map.setView(Peta.pusat, 12);
        });
    });
</script>
@endpush
