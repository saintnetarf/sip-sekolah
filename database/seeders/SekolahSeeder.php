<?php

namespace Database\Seeders;

use App\Models\Sekolah;
use Illuminate\Database\Seeder;

class SekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sekolahs = [
            [
                'npsn' => '30000001',
                'nama' => 'SD Negeri Contoh 1',
                'jenjang' => 'SD',
                'status' => 'Negeri',
                'alamat' => 'Jl. Lambung Mangkurat No. 10',
                'kecamatan' => 'Banjarmasin Tengah',
                'telepon' => '0511-3351001',
                'latitude' => -3.3194000,
                'longitude' => 114.5908000,
            ],
            [
                'npsn' => '30000002',
                'nama' => 'SD Swasta Contoh 2',
                'jenjang' => 'SD',
                'status' => 'Swasta',
                'alamat' => 'Jl. Hasan Basry No. 25',
                'kecamatan' => 'Banjarmasin Utara',
                'telepon' => '0511-3351002',
                'latitude' => -3.2980000,
                'longitude' => 114.5850000,
            ],
            [
                'npsn' => '30000003',
                'nama' => 'SMP Negeri Contoh 3',
                'jenjang' => 'SMP',
                'status' => 'Negeri',
                'alamat' => 'Jl. Pangeran Samudera No. 45',
                'kecamatan' => 'Banjarmasin Tengah',
                'telepon' => '0511-3351003',
                'latitude' => -3.3245000,
                'longitude' => 114.5925000,
            ],
            [
                'npsn' => '30000004',
                'nama' => 'SMP Swasta Contoh 4',
                'jenjang' => 'SMP',
                'status' => 'Swasta',
                'alamat' => 'Jl. Veteran No. 12',
                'kecamatan' => 'Banjarmasin Timur',
                'telepon' => '0511-3351004',
                'latitude' => -3.3210000,
                'longitude' => 114.6015000,
            ],
            [
                'npsn' => '30000005',
                'nama' => 'SMA Negeri Contoh 5',
                'jenjang' => 'SMA',
                'status' => 'Negeri',
                'alamat' => 'Jl. Mulawarman No. 5',
                'kecamatan' => 'Banjarmasin Tengah',
                'telepon' => '0511-3351005',
                'latitude' => -3.3160000,
                'longitude' => 114.5880000,
            ],
            [
                'npsn' => '30000006',
                'nama' => 'SMA Swasta Contoh 6',
                'jenjang' => 'SMA',
                'status' => 'Swasta',
                'alamat' => 'Jl. Sutoyo S No. 88',
                'kecamatan' => 'Banjarmasin Barat',
                'telepon' => '0511-3351006',
                'latitude' => -3.3312000,
                'longitude' => 114.5760000,
            ],
            [
                'npsn' => '30000007',
                'nama' => 'SMK Negeri Contoh 7',
                'jenjang' => 'SMK',
                'status' => 'Negeri',
                'alamat' => 'Jl. A. Yani Km. 4.5',
                'kecamatan' => 'Banjarmasin Timur',
                'telepon' => '0511-3351007',
                'latitude' => -3.3420000,
                'longitude' => 114.6050000,
            ],
            [
                'npsn' => '30000008',
                'nama' => 'SMK Swasta Contoh 8',
                'jenjang' => 'SMK',
                'status' => 'Swasta',
                'alamat' => 'Jl. Gerilya No. 17',
                'kecamatan' => 'Banjarmasin Selatan',
                'telepon' => '0511-3351008',
                'latitude' => -3.3550000,
                'longitude' => 114.5980000,
            ],
        ];

        foreach ($sekolahs as $sekolah) {
            Sekolah::updateOrCreate(
                ['npsn' => $sekolah['npsn']],
                $sekolah
            );
        }
    }
}
