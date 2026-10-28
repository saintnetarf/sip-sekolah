<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use App\Support\Geometri;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetaController extends Controller
{
    /**
     * Tampilkan halaman utama peta digital publik.
     */
    public function index(): View
    {
        return view('peta.index');
    }

    /**
     * Halaman pencarian sekolah.
     */
    public function cari(): View
    {
        return view('peta.cari');
    }

    /**
     * Halaman klaster marker sekolah.
     */
    public function klaster(): View
    {
        return view('peta.klaster');
    }

    /**
     * Halaman peta panas (heatmap).
     */
    public function panas(): View
    {
        return view('peta.panas');
    }

    /**
     * Halaman poligon sebaran wilayah sekolah per kecamatan.
     */
    public function wilayah(): View
    {
        return view('peta.wilayah');
    }

    /**
     * Halaman rute antar sekolah pilihan.
     */
    public function rute(): View
    {
        return view('peta.rute');
    }

    /**
     * Halaman cek lokasi, reverse geocoding dan sekolah terdekat.
     */
    public function alamat(): View
    {
        return view('peta.alamat');
    }

    /**
     * Endpoint GeoJSON poligon wilayah per kecamatan (Convex Hull).
     */
    public function geojsonWilayah(Request $request): JsonResponse
    {
        $query = Sekolah::with('profil');

        if ($jenjang = $request->query('jenjang')) {
            $query->where('jenjang', $jenjang);
        }

        $sekolahs = $query->get();
        $kelompokKecamatan = $sekolahs->groupBy('kecamatan');

        $features = [];

        foreach ($kelompokKecamatan as $kecamatan => $daftar) {
            $titik = $daftar->map(function ($s) {
                return [(float) $s->longitude, (float) $s->latitude];
            })->all();

            $hull = Geometri::convexHull($titik);

            // Lewati kelompok yang hull-nya kurang dari 3 titik
            if (count($hull) < 3) {
                continue;
            }

            // Tutup cincin poligon (titik pertama sama dengan titik terakhir)
            $cincin = $hull;
            $cincin[] = $cincin[0];

            $jumlahSiswa = $daftar->sum(function ($s) {
                return (int) ($s->profil?->jumlah_siswa ?? 0);
            });

            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [
                        $cincin,
                    ],
                ],
                'properties' => [
                    'kecamatan' => $kecamatan,
                    'jumlah_sekolah' => $daftar->count(),
                    'jumlah_siswa' => $jumlahSiswa,
                ],
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    /**
     * Endpoint GeoJSON data sekolah.
     */
    public function geojson(Request $request): JsonResponse
    {
        $query = Sekolah::with('profil');

        if ($jenjang = $request->query('jenjang')) {
            $query->where('jenjang', $jenjang);
        }

        $sekolahs = $query->get();

        $features = $sekolahs->map(function ($sekolah) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    // Urutan GeoJSON: [longitude, latitude]
                    'coordinates' => [
                        (float) $sekolah->longitude,
                        (float) $sekolah->latitude,
                    ],
                ],
                'properties' => [
                    'npsn' => $sekolah->npsn,
                    'nama' => $sekolah->nama,
                    'jenjang' => $sekolah->jenjang,
                    'status' => $sekolah->status,
                    'alamat' => $sekolah->alamat,
                    'kecamatan' => $sekolah->kecamatan,
                    'telepon' => $sekolah->telepon,
                    'kepala_sekolah' => $sekolah->profil?->kepala_sekolah,
                    'akreditasi' => $sekolah->profil?->akreditasi,
                    'jumlah_siswa' => $sekolah->profil?->jumlah_siswa,
                    'jumlah_guru' => $sekolah->profil?->jumlah_guru,
                ],
            ];
        })->values();

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
