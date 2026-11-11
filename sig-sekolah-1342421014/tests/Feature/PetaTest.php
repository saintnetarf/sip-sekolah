<?php

namespace Tests\Feature;

use App\Models\Sekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_halaman_peta_dapat_diakses(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('Peta Sekolah');
    }

    public function test_geojson_sekolah_mengembalikan_feature_collection(): void
    {
        $sekolah = Sekolah::create([
            'npsn' => '12345678',
            'nama' => 'SD Negeri 1 Test',
            'jenjang' => 'SD',
            'status' => 'Negeri',
            'alamat' => 'Jl. Pendidikan No. 1',
            'kecamatan' => 'Banjarmasin Utara',
            'telepon' => '0511123456',
            'latitude' => -3.3194,
            'longitude' => 114.5908,
        ]);

        $sekolah->profil()->create([
            'kepala_sekolah' => 'Budi Santoso',
            'akreditasi' => 'A',
            'jumlah_siswa' => 250,
            'jumlah_guru' => 20,
        ]);

        $response = $this->getJson('/data/sekolah');

        $response->assertOk()
            ->assertJson([
                'type' => 'FeatureCollection',
            ])
            ->assertJsonFragment([
                'type' => 'Point',
                'coordinates' => [114.5908, -3.3194],
            ])
            ->assertJsonFragment([
                'npsn' => '12345678',
                'nama' => 'SD Negeri 1 Test',
                'jenjang' => 'SD',
                'kepala_sekolah' => 'Budi Santoso',
            ]);
    }

    public function test_geojson_sekolah_filter_jenjang(): void
    {
        Sekolah::create([
            'npsn' => '11111111',
            'nama' => 'SD 1',
            'jenjang' => 'SD',
            'status' => 'Negeri',
            'alamat' => 'Jl. A',
            'kecamatan' => 'Utara',
            'latitude' => -3.3,
            'longitude' => 114.5,
        ]);

        Sekolah::create([
            'npsn' => '22222222',
            'nama' => 'SMP 1',
            'jenjang' => 'SMP',
            'status' => 'Negeri',
            'alamat' => 'Jl. B',
            'kecamatan' => 'Selatan',
            'latitude' => -3.4,
            'longitude' => 114.6,
        ]);

        $resSD = $this->getJson('/data/sekolah?jenjang=SD');
        $resSD->assertOk();
        $this->assertCount(1, $resSD->json('features'));
        $this->assertEquals('SD 1', $resSD->json('features.0.properties.nama'));

        $resSMP = $this->getJson('/data/sekolah?jenjang=SMP');
        $resSMP->assertOk();
        $this->assertCount(1, $resSMP->json('features'));
        $this->assertEquals('SMP 1', $resSMP->json('features.0.properties.nama'));
    }
}

