<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
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
        return view('peta');
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
