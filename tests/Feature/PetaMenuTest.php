<?php

namespace Tests\Feature;

use App\Models\Sekolah;
use App\Models\User;
use App\Support\Geometri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetaMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /**
     * 1. Ketujuh halaman menu berstatus 200 untuk tamu.
     */
    public function test_ketujuh_halaman_menu_berstatus_200_untuk_tamu(): void
    {
        $urls = [
            '/',
            '/pencarian',
            '/klaster',
            '/peta-panas',
            '/wilayah',
            '/rute',
            '/cek-alamat',
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
        }
    }

    /**
     * 2. Halaman memuat tautan ke keenam menu lain dan tautan Masuk bagi tamu.
     */
    public function test_halaman_memuat_tautan_ke_menu_lain_dan_tautan_masuk_bagi_tamu(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('peta.cari'));
        $response->assertSee(route('peta.klaster'));
        $response->assertSee(route('peta.panas'));
        $response->assertSee(route('peta.wilayah'));
        $response->assertSee(route('peta.rute'));
        $response->assertSee(route('peta.alamat'));
        $response->assertSee(route('login'));
        $response->assertSee('Masuk');
    }

    /**
     * 3. Pengguna yang login melihat tautan Dashboard, bukan Masuk.
     */
    public function test_pengguna_yang_login_melihat_tautan_dashboard_bukan_masuk(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('dashboard'));
        $response->assertSee('Dashboard');
        $response->assertDontSee(route('login'));
    }

    /**
     * 4. /data/sekolah mengembalikan FeatureCollection dengan coordinates berurutan [longitude, latitude] bertipe angka.
     */
    public function test_data_sekolah_mengembalikan_feature_collection_dengan_koordinat_angka(): void
    {
        $sekolah = Sekolah::create([
            'npsn' => '10000001',
            'nama' => 'SD Negeri Uji 1',
            'jenjang' => 'SD',
            'status' => 'Negeri',
            'alamat' => 'Jl. Uji No. 1',
            'kecamatan' => 'Banjarmasin Tengah',
            'telepon' => '0511-1000001',
            'latitude' => -3.3194000,
            'longitude' => 114.5908000,
        ]);

        $response = $this->getJson(route('peta.data'));

        $response->assertStatus(200)
            ->assertJsonPath('type', 'FeatureCollection')
            ->assertJsonCount(1, 'features');

        $feature = $response->json('features.0');
        $coords = $feature['geometry']['coordinates'];

        $this->assertSame('Feature', $feature['type']);
        $this->assertSame('Point', $feature['geometry']['type']);
        $this->assertCount(2, $coords);

        // Coordinates harus berupa float numerik: [longitude, latitude]
        $this->assertIsFloat($coords[0]);
        $this->assertIsFloat($coords[1]);
        $this->assertEquals(114.5908000, $coords[0]);
        $this->assertEquals(-3.3194000, $coords[1]);
    }

    /**
     * 5. /data/sekolah?jenjang=SMA hanya memuat fitur SMA.
     */
    public function test_data_sekolah_filter_jenjang_sma_hanya_memuat_fitur_sma(): void
    {
        Sekolah::create([
            'npsn' => '10000001',
            'nama' => 'SD Uji',
            'jenjang' => 'SD',
            'status' => 'Negeri',
            'alamat' => 'Jl. SD',
            'kecamatan' => 'Banjarmasin Tengah',
            'latitude' => -3.3194,
            'longitude' => 114.5908,
        ]);

        Sekolah::create([
            'npsn' => '10000002',
            'nama' => 'SMA Uji',
            'jenjang' => 'SMA',
            'status' => 'Negeri',
            'alamat' => 'Jl. SMA',
            'kecamatan' => 'Banjarmasin Tengah',
            'latitude' => -3.3200,
            'longitude' => 114.5910,
        ]);

        $response = $this->getJson(route('peta.data', ['jenjang' => 'SMA']));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'features')
            ->assertJsonPath('features.0.properties.jenjang', 'SMA')
            ->assertJsonPath('features.0.properties.nama', 'SMA Uji');
    }

    /**
     * 6. /data/sekolah memuat jumlah_siswa, kepala_sekolah, akreditasi dan tidak memuat email maupun website.
     */
    public function test_data_sekolah_memuat_atribut_profil_publik_dan_tidak_memuat_email_website(): void
    {
        $sekolah = Sekolah::create([
            'npsn' => '10000003',
            'nama' => 'SMP Profil',
            'jenjang' => 'SMP',
            'status' => 'Negeri',
            'alamat' => 'Jl. SMP',
            'kecamatan' => 'Banjarmasin Tengah',
            'latitude' => -3.3210,
            'longitude' => 114.5920,
        ]);

        $sekolah->profil()->create([
            'kepala_sekolah' => 'Dr. H. Guru, M.Pd',
            'akreditasi' => 'A',
            'jumlah_siswa' => 480,
            'jumlah_guru' => 30,
            'email' => 'rahasia@smp.sch.id',
            'website' => 'https://smp.sch.id',
        ]);

        $response = $this->getJson(route('peta.data'));

        $response->assertStatus(200);
        $properties = $response->json('features.0.properties');

        $this->assertArrayHasKey('jumlah_siswa', $properties);
        $this->assertArrayHasKey('kepala_sekolah', $properties);
        $this->assertArrayHasKey('akreditasi', $properties);
        $this->assertEquals(480, $properties['jumlah_siswa']);
        $this->assertEquals('Dr. H. Guru, M.Pd', $properties['kepala_sekolah']);
        $this->assertEquals('A', $properties['akreditasi']);

        // Data privasi email dan website TIDAK boleh diekspos di GeoJSON publik
        $this->assertArrayNotHasKey('email', $properties);
        $this->assertArrayNotHasKey('website', $properties);
    }

    /**
     * 7. /data/wilayah untuk tiga sekolah tidak segaris pada satu kecamatan mengembalikan satu Polygon dengan cincin tertutup dan jumlah_sekolah benar.
     */
    public function test_data_wilayah_tiga_sekolah_tidak_segaris_mengembalikan_satu_polygon_cincin_tertutup(): void
    {
        // Tiga titik segitiga tidak segaris
        $s1 = Sekolah::create([
            'npsn' => '20000001',
            'nama' => 'Sekolah Segitiga 1',
            'jenjang' => 'SD',
            'status' => 'Negeri',
            'alamat' => 'Jl. Titik 1',
            'kecamatan' => 'Banjarmasin Utara',
            'latitude' => -3.2900,
            'longitude' => 114.5800,
        ]);
        $s1->profil()->create(['jumlah_siswa' => 200]);

        $s2 = Sekolah::create([
            'npsn' => '20000002',
            'nama' => 'Sekolah Segitiga 2',
            'jenjang' => 'SMP',
            'status' => 'Swasta',
            'alamat' => 'Jl. Titik 2',
            'kecamatan' => 'Banjarmasin Utara',
            'latitude' => -3.2800,
            'longitude' => 114.6000,
        ]);
        $s2->profil()->create(['jumlah_siswa' => 300]);

        $s3 = Sekolah::create([
            'npsn' => '20000003',
            'nama' => 'Sekolah Segitiga 3',
            'jenjang' => 'SMA',
            'status' => 'Negeri',
            'alamat' => 'Jl. Titik 3',
            'kecamatan' => 'Banjarmasin Utara',
            'latitude' => -3.3000,
            'longitude' => 114.6100,
        ]);
        $s3->profil()->create(['jumlah_siswa' => 500]);

        $response = $this->getJson(route('peta.data.wilayah'));

        $response->assertStatus(200)
            ->assertJsonPath('type', 'FeatureCollection')
            ->assertJsonCount(1, 'features');

        $feature = $response->json('features.0');
        $this->assertSame('Polygon', $feature['geometry']['type']);
        $this->assertSame('Banjarmasin Utara', $feature['properties']['kecamatan']);
        $this->assertSame(3, $feature['properties']['jumlah_sekolah']);
        $this->assertSame(1000, $feature['properties']['jumlah_siswa']);

        // Periksa cincin tertutup: koordinat pertama harus sama dengan koordinat terakhir
        $ring = $feature['geometry']['coordinates'][0];
        $this->assertGreaterThanOrEqual(4, count($ring)); // 3 sudut + 1 titik penutup
        $this->assertEquals($ring[0], $ring[count($ring) - 1]);
    }

    /**
     * 8. /data/wilayah untuk kecamatan yang hanya memiliki dua sekolah mengembalikan nol fitur.
     */
    public function test_data_wilayah_dua_sekolah_mengembalikan_nol_fitur(): void
    {
        Sekolah::create([
            'npsn' => '20000004',
            'nama' => 'Sekolah Dua 1',
            'jenjang' => 'SD',
            'status' => 'Negeri',
            'alamat' => 'Jl. Dua 1',
            'kecamatan' => 'Banjarmasin Barat',
            'latitude' => -3.3200,
            'longitude' => 114.5650,
        ]);

        Sekolah::create([
            'npsn' => '20000005',
            'nama' => 'Sekolah Dua 2',
            'jenjang' => 'SMP',
            'status' => 'Swasta',
            'alamat' => 'Jl. Dua 2',
            'kecamatan' => 'Banjarmasin Barat',
            'latitude' => -3.3250,
            'longitude' => 114.5700,
        ]);

        $response = $this->getJson(route('peta.data.wilayah'));

        $response->assertStatus(200)
            ->assertJsonPath('type', 'FeatureCollection')
            ->assertJsonCount(0, 'features');
    }

    /**
     * 9. Geometri::convexHull pada titik uji (persegi dengan satu titik di dalamnya) mengembalikan empat titik sudut, dan pada tiga titik segaris mengembalikan kurang dari tiga titik.
     */
    public function test_geometri_convex_hull_persegi_dan_segaris(): void
    {
        // Kasus 1: Persegi [0,0], [10,0], [10,10], [0,10] dengan titik dalam [5,5]
        $titikPersegi = [
            [0, 0],
            [10, 0],
            [10, 10],
            [0, 10],
            [5, 5],
        ];

        $hullPersegi = Geometri::convexHull($titikPersegi);

        $this->assertCount(4, $hullPersegi);
        $expectedPersegi = [
            [0.0, 0.0],
            [10.0, 0.0],
            [10.0, 10.0],
            [0.0, 10.0],
        ];
        $this->assertEquals($expectedPersegi, $hullPersegi);

        // Kasus 2: Tiga titik segaris (collinear)
        $titikSegaris = [
            [0, 0],
            [1, 1],
            [2, 2],
        ];

        $hullSegaris = Geometri::convexHull($titikSegaris);

        $this->assertLessThan(3, count($hullSegaris));
    }

    /**
     * 10. /data/wilayah?jenjang=... menyaring poligon sesuai jenjang.
     */
    public function test_data_wilayah_filter_jenjang_menyaring_poligon_sesuai_jenjang(): void
    {
        // Buat 3 sekolah SMA di Banjarmasin Timur (membentuk poligon)
        for ($i = 1; $i <= 3; $i++) {
            Sekolah::create([
                'npsn' => "3000001{$i}",
                'nama' => "SMA Timur {$i}",
                'jenjang' => 'SMA',
                'status' => 'Negeri',
                'alamat' => "Jl. Timur {$i}",
                'kecamatan' => 'Banjarmasin Timur',
                'latitude' => -3.3100 - ($i * 0.01),
                'longitude' => 114.6100 + (($i % 2 == 0 ? 1 : -1) * 0.01),
            ]);
        }

        // Buat 1 sekolah SD di Banjarmasin Timur (tidak cukup untuk poligon)
        Sekolah::create([
            'npsn' => '30000020',
            'nama' => 'SD Timur 1',
            'jenjang' => 'SD',
            'status' => 'Negeri',
            'alamat' => 'Jl. SD Timur',
            'kecamatan' => 'Banjarmasin Timur',
            'latitude' => -3.3150,
            'longitude' => 114.6150,
        ]);

        // Filter SMA harus menghasilkan 1 poligon
        $responseSma = $this->getJson(route('peta.data.wilayah', ['jenjang' => 'SMA']));
        $responseSma->assertStatus(200)
            ->assertJsonCount(1, 'features')
            ->assertJsonPath('features.0.properties.kecamatan', 'Banjarmasin Timur')
            ->assertJsonPath('features.0.properties.jumlah_sekolah', 3);

        // Filter SD harus menghasilkan 0 poligon karena hanya ada 1 sekolah SD
        $responseSd = $this->getJson(route('peta.data.wilayah', ['jenjang' => 'SD']));
        $responseSd->assertStatus(200)
            ->assertJsonCount(0, 'features');
    }
}
