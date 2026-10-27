<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Persebaran Sekolah - SIG Sekolah</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1f2937;
            background-color: #f3f4f6;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* Header Navbar */
        header {
            background-color: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            height: 64px;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1000;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            flex-shrink: 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.15rem;
            color: #4f46e5;
            text-decoration: none;
        }

        .brand svg {
            width: 24px;
            height: 24px;
        }

        .controls {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
        }

        select {
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            background-color: #ffffff;
            font-size: 0.875rem;
            color: #374151;
            outline: none;
            cursor: pointer;
            transition: border-color 0.15s ease;
        }

        select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }

        .badge-count {
            display: inline-block;
            background-color: #eef2ff;
            color: #4338ca;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.825rem;
            font-weight: 600;
            border: 1px solid #c7d2fe;
        }

        .btn-admin {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #4f46e5;
            color: #ffffff;
            text-decoration: none;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background-color 0.15s ease;
        }

        .btn-admin:hover {
            background-color: #4338ca;
        }

        /* Area Peta */
        #map {
            flex: 1;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        /* Popup Styling */
        .leaflet-popup-content-wrapper {
            border-radius: 8px;
            padding: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .popup-content {
            font-size: 0.875rem;
            line-height: 1.5;
            color: #374151;
        }

        .popup-title {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
        }

        .popup-row {
            margin-bottom: 4px;
        }

        .popup-row span.label {
            font-weight: 600;
            color: #4b5563;
        }

        .popup-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 4px;
        }

        .badge-jenjang {
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .badge-status-negeri {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-status-swasta {
            background-color: #f3f4f6;
            color: #374151;
        }

        /* Responsive Layout */
        @media (max-width: 640px) {
            header {
                height: auto;
                padding: 10px 14px;
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }

            .controls {
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <header>
        <a href="{{ url('/') }}" class="brand">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span>SIG Sekolah</span>
        </a>

        <div class="controls">
            <div class="filter-group">
                <label for="filter-jenjang" class="filter-label">Filter:</label>
                <select id="filter-jenjang">
                    <option value="">Semua jenjang</option>
                    <option value="SD">SD</option>
                    <option value="SMP">SMP</option>
                    <option value="SMA">SMA</option>
                    <option value="SMK">SMK</option>
                </select>
            </div>

            <div id="total-sekolah" class="badge-count">Memuat data...</div>

            @auth
                <a href="{{ route('dashboard') }}" class="btn-admin">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-admin">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span>Masuk</span>
                </a>
            @endauth
        </div>
    </header>

    <div id="map"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        // Fungsi escape berbasis textContent untuk mencegah XSS
        function esc(str) {
            if (str === null || str === undefined) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Inisialisasi peta Leaflet
        const map = L.map('map').setView([-3.3194, 114.5908], 12);

        // Tile layer OpenStreetMap dengan atribusi wajib
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let geojsonLayer = null;
        const totalSekolahEl = document.getElementById('total-sekolah');
        const filterJenjangEl = document.getElementById('filter-jenjang');

        // Fungsi memuat data sekolah via endpoint GeoJSON
        function muatData(jenjang) {
            totalSekolahEl.textContent = 'Memuat...';

            let url = "{{ route('peta.data') }}";
            if (jenjang) {
                url += '?jenjang=' + encodeURIComponent(jenjang);
            }

            fetch(url)
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Gagal memuat data dari server');
                    }
                    return response.json();
                })
                .then(function (data) {
                    // Bersihkan lapisan peta lama jika ada
                    if (geojsonLayer) {
                        map.removeLayer(geojsonLayer);
                    }

                    const count = data.features ? data.features.length : 0;
                    totalSekolahEl.textContent = count + ' sekolah';

                    // Buat layer GeoJSON baru
                    geojsonLayer = L.geoJSON(data, {
                        onEachFeature: function (feature, layer) {
                            const p = feature.properties || {};
                            const coords = feature.geometry && feature.geometry.coordinates ? feature.geometry.coordinates : [0, 0];
                            const lng = coords[0];
                            const lat = coords[1];
                            const statusClass = p.status === 'Negeri' ? 'badge-status-negeri' : 'badge-status-swasta';

                            const siswa = (p.jumlah_siswa !== null && p.jumlah_siswa !== undefined) ? p.jumlah_siswa : '-';
                            const guru = (p.jumlah_guru !== null && p.jumlah_guru !== undefined) ? p.jumlah_guru : '-';

                            const popupHtml = [
                                '<div class="popup-content">',
                                '  <div class="popup-title"><strong>' + esc(p.nama) + '</strong></div>',
                                '  <div class="popup-row"><span class="label">NPSN:</span> ' + esc(p.npsn) + '</div>',
                                '  <div class="popup-row">',
                                '    <span class="popup-badge badge-jenjang">' + esc(p.jenjang) + '</span>',
                                '    <span class="popup-badge ' + statusClass + '">' + esc(p.status) + '</span>',
                                '  </div>',
                                '  <div class="popup-row"><span class="label">Kepala Sekolah:</span> ' + esc(p.kepala_sekolah || '-') + '</div>',
                                '  <div class="popup-row"><span class="label">Akreditasi:</span> ' + esc(p.akreditasi || '-') + '</div>',
                                '  <div class="popup-row"><span class="label">Siswa / Guru:</span> ' + siswa + ' / ' + guru + '</div>',
                                '  <div class="popup-row"><span class="label">Alamat:</span> ' + esc(p.alamat) + ', ' + esc(p.kecamatan) + '</div>',
                                '  <div class="popup-row"><span class="label">Telepon:</span> ' + esc(p.telepon || '-') + '</div>',
                                '  <div class="popup-row"><span class="label">Koordinat:</span> ' + lat + ', ' + lng + '</div>',
                                '</div>'
                            ].join('');

                            layer.bindPopup(popupHtml);
                        }
                    }).addTo(map);

                    // Sesuaikan zoom dan batas bila terdapat fitur
                    if (count > 0) {
                        map.fitBounds(geojsonLayer.getBounds().pad(0.1));
                    }
                })
                .catch(function (error) {
                    console.error('Error saat memuat GeoJSON:', error);
                    totalSekolahEl.textContent = 'Error memuat data';
                });
        }

        // Muat data awal saat halaman dibuka
        muatData('');

        // Event listener perubahan filter jenjang
        filterJenjangEl.addEventListener('change', function () {
            muatData(this.value);
        });
    </script>
</body>
</html>
