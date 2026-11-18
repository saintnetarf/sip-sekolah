<?php

namespace Database\Seeders;

use App\Models\Sekolah;
use Illuminate\Database\Seeder;

class SekolahDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        mt_srand(2026);

        $kecamatans = [
            ['nama' => 'Banjarmasin Utara', 'lat' => -3.2900, 'lng' => 114.5950, 'jumlah' => 30],
            ['nama' => 'Banjarmasin Timur', 'lat' => -3.3150, 'lng' => 114.6150, 'jumlah' => 22],
            ['nama' => 'Banjarmasin Tengah', 'lat' => -3.3250, 'lng' => 114.5900, 'jumlah' => 16],
            ['nama' => 'Banjarmasin Selatan', 'lat' => -3.3550, 'lng' => 114.5900, 'jumlah' => 26],
            ['nama' => 'Banjarmasin Barat', 'lat' => -3.3200, 'lng' => 114.5650, 'jumlah' => 12],
        ];

        $jenjangPilihan = ['SD', 'SD', 'SD', 'SMP', 'SMP', 'SMA', 'SMK'];
        $akreditasiPilihan = ['A', 'B', 'C'];
        $nomor = 1;

        foreach ($kecamatans as $kecamatan) {
            for ($i = 0; $i < $kecamatan['jumlah']; $i++) {
                $npsn = sprintf('9%07d', $nomor);
                $jenjang = $jenjangPilihan[mt_rand(0, count($jenjangPilihan) - 1)];
                $status = mt_rand(1, 3) <= 2 ? 'Negeri' : 'Swasta';
                $nama = "{$jenjang} Demo {$nomor}";

                $latOffset = mt_rand(-100, 100) / 10000;
                $lngOffset = mt_rand(-100, 100) / 10000;
                $lat = round($kecamatan['lat'] + $latOffset, 7);
                $lng = round($kecamatan['lng'] + $lngOffset, 7);

                $sekolah = Sekolah::updateOrCreate(
                    ['npsn' => $npsn],
                    [
                        'nama' => $nama,
                        'jenjang' => $jenjang,
                        'status' => $status,
                        'alamat' => "Jl. Demo No. {$nomor}, {$kecamatan['nama']}",
                        'kecamatan' => $kecamatan['nama'],
                        'telepon' => sprintf('0511-9%06d', $nomor),
                        'latitude' => $lat,
                        'longitude' => $lng,
                    ]
                );

                $sekolah->profil()->updateOrCreate(
                    [],
                    [
                        'kepala_sekolah' => "Kepala {$nama}",
                        'akreditasi' => $akreditasiPilihan[mt_rand(0, count($akreditasiPilihan) - 1)],
                        'jumlah_siswa' => mt_rand(80, 900),
                        'jumlah_guru' => mt_rand(8, 60),
                    ]
                );

                $nomor++;
            }
        }
    }
}
