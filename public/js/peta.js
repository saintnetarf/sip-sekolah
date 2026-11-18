/**
 * Peta.js - Modul bersama Peta Interaktif SIG Sekolah
 */
const Peta = {
    /**
     * Titik koordinat pusat Banjarmasin [lat, lng]
     */
    pusat: [-3.3194, 114.5908],

    /**
     * Inisialisasi peta Leaflet dengan tile OpenStreetMap
     *
     * @param {string|HTMLElement} idElemen
     * @param {number} zoom
     * @returns {L.Map}
     */
    buat: function (idElemen, zoom = 12) {
        const map = L.map(idElemen).setView(Peta.pusat, zoom);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(map);

        return map;
    },

    /**
     * Escape string untuk mencegah XSS
     *
     * @param {*} teks
     * @returns {string}
     */
    esc: function (teks) {
        if (teks === null || teks === undefined) {
            return '';
        }
        const div = document.createElement('div');
        div.textContent = String(teks);
        return div.innerHTML;
    },

    /**
     * Ambil data GeoJSON sekolah dari backend
     *
     * @param {string} jenjang
     * @returns {Promise<Array>} Array of GeoJSON features
     */
    ambilSekolah: async function (jenjang = '') {
        const baseUrl = (window.PETA_URL && window.PETA_URL.data) ? window.PETA_URL.data : '/data/sekolah';
        const url = new URL(baseUrl, window.location.origin);
        if (jenjang) {
            url.searchParams.set('jenjang', jenjang);
        }

        const res = await fetch(url.toString());
        if (!res.ok) {
            throw new Error('Gagal memuat data sekolah: ' + res.statusText);
        }

        const data = await res.json();
        return data.features || [];
    },

    /**
     * Ubah GeoJSON [lng, lat] menjadi Leaflet [lat, lng]
     *
     * @param {Object} fitur
     * @returns {[number, number]|null}
     */
    latlng: function (fitur) {
        if (!fitur) {
            return null;
        }
        if (fitur.geometry && Array.isArray(fitur.geometry.coordinates)) {
            return [fitur.geometry.coordinates[1], fitur.geometry.coordinates[0]];
        }
        if (Array.isArray(fitur) && fitur.length >= 2) {
            return [fitur[1], fitur[0]];
        }
        return null;
    },

    /**
     * Render konten HTML popup informasi sekolah
     *
     * @param {Object} fitur
     * @returns {string}
     */
    popup: function (fitur) {
        if (!fitur) {
            return '';
        }
        const p = fitur.properties || {};
        const coords = (fitur.geometry && Array.isArray(fitur.geometry.coordinates)) ? fitur.geometry.coordinates : null;
        const koordinatTeks = coords ? `${coords[1].toFixed(6)}, ${coords[0].toFixed(6)}` : '-';

        const nama = p.nama ? Peta.esc(p.nama) : '-';
        const npsn = p.npsn ? Peta.esc(p.npsn) : '-';
        const jenjang = p.jenjang ? Peta.esc(p.jenjang) : '-';
        const status = p.status ? Peta.esc(p.status) : '-';
        const alamat = p.alamat ? Peta.esc(p.alamat) : '-';
        const kecamatan = p.kecamatan ? Peta.esc(p.kecamatan) : '-';
        const telepon = p.telepon ? Peta.esc(p.telepon) : '-';
        const kepala = p.kepala_sekolah ? Peta.esc(p.kepala_sekolah) : '-';
        const akreditasi = p.akreditasi ? Peta.esc(p.akreditasi) : '-';
        const siswa = (p.jumlah_siswa !== null && p.jumlah_siswa !== undefined) ? Peta.esc(p.jumlah_siswa) : '-';
        const guru = (p.jumlah_guru !== null && p.jumlah_guru !== undefined) ? Peta.esc(p.jumlah_guru) : '-';

        return `
            <div style="font-family: inherit; font-size: 13px; line-height: 1.5; min-width: 230px; color: #1f2937;">
                <div style="font-weight: 700; font-size: 14px; margin-bottom: 6px; padding-bottom: 4px; border-bottom: 1px solid #e5e7eb; color: #111827;">${nama}</div>
                <div style="margin-bottom: 3px;"><strong>NPSN:</strong> ${npsn}</div>
                <div style="margin-bottom: 3px;"><strong>Jenjang / Status:</strong> ${jenjang} (${status})</div>
                <div style="margin-bottom: 3px;"><strong>Alamat:</strong> ${alamat}, ${kecamatan}</div>
                <div style="margin-bottom: 3px;"><strong>Telepon:</strong> ${telepon}</div>
                <div style="margin-bottom: 3px;"><strong>Kepala Sekolah:</strong> ${kepala}</div>
                <div style="margin-bottom: 3px;"><strong>Akreditasi:</strong> ${akreditasi}</div>
                <div style="margin-bottom: 3px;"><strong>Siswa &amp; Guru:</strong> ${siswa} siswa / ${guru} guru</div>
                <div style="margin-top: 4px; padding-top: 4px; border-top: 1px dashed #e5e7eb; font-size: 11px; color: #6b7280;"><strong>Koordinat:</strong> ${Peta.esc(koordinatTeks)}</div>
            </div>
        `;
    },

    /**
     * Hitung jarak Haversine antara dua titik [lat, lng] dalam kilometer
     *
     * @param {[number, number]} a
     * @param {[number, number]} b
     * @returns {number}
     */
    jarakKm: function (a, b) {
        if (!a || !b) {
            return 0;
        }
        const R = 6371; // radius bumi dalam km
        const dLat = (b[0] - a[0]) * Math.PI / 180;
        const dLon = (b[1] - a[1]) * Math.PI / 180;
        const lat1 = a[0] * Math.PI / 180;
        const lat2 = b[0] * Math.PI / 180;
        const sinDLat = Math.sin(dLat / 2);
        const sinDLon = Math.sin(dLon / 2);
        const val = sinDLat * sinDLat + Math.cos(lat1) * Math.cos(lat2) * sinDLon * sinDLon;
        const c = 2 * Math.atan2(Math.sqrt(val), Math.sqrt(1 - val));
        return R * c;
    },

    /**
     * Buat Leaflet divIcon berbentuk lingkaran bernomor 30px
     *
     * @param {number|string} angka
     * @param {string} warna
     * @returns {L.DivIcon}
     */
    ikonAngka: function (angka, warna) {
        return L.divIcon({
            className: 'marker-angka-wrapper',
            html: `<div style="background-color: ${Peta.esc(warna)}; width: 30px; height: 30px; border-radius: 50%; color: #ffffff; font-weight: 700; font-size: 13px; display: flex; align-items: center; justify-content: center; border: 2px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.3); text-align: center; line-height: 1;">${Peta.esc(String(angka))}</div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 15],
            popupAnchor: [0, -15],
        });
    },
};
