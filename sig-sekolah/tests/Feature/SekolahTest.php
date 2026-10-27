<?php

namespace Tests\Feature;

use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SekolahTest extends TestCase
{
    use RefreshDatabase;

    protected User $dinas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->dinas = User::factory()->create(['role' => User::ROLE_DINAS]);
        $this->actingAs($this->dinas);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'npsn' => '30000001',
            'nama' => 'SD Negeri Contoh 1',
            'jenjang' => 'SD',
            'status' => 'Negeri',
            'alamat' => 'Jl. Lambung Mangkurat No. 10',
            'kecamatan' => 'Banjarmasin Tengah',
            'telepon' => '0511-3351001',
            'latitude' => -3.3194000,
            'longitude' => 114.5908000,
        ], $overrides);
    }

    /**
     * 1. Simpan sekolah valid menambah satu baris.
     */
    public function test_simpan_sekolah_valid_menambah_satu_baris(): void
    {
        $payload = $this->validPayload();

        $response = $this->post(route('dinas.sekolah.store'), $payload);

        $response->assertRedirect(route('dinas.sekolah.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('sekolahs', 1);
        $this->assertDatabaseHas('sekolahs', [
            'npsn' => '30000001',
            'nama' => 'SD Negeri Contoh 1',
        ]);
    }

    /**
     * 2. Nama kosong ditolak.
     */
    public function test_nama_kosong_ditolak(): void
    {
        $payload = $this->validPayload(['nama' => '']);

        $response = $this->post(route('dinas.sekolah.store'), $payload);

        $response->assertSessionHasErrors('nama');
        $this->assertDatabaseCount('sekolahs', 0);
    }

    /**
     * 3. NPSN ganda ditolak.
     */
    public function test_npsn_ganda_ditolak(): void
    {
        Sekolah::create($this->validPayload(['npsn' => '30000001']));

        $response = $this->post(route('dinas.sekolah.store'), $this->validPayload([
            'npsn' => '30000001',
            'nama' => 'SD Lain dengan NPSN Sama',
        ]));

        $response->assertSessionHasErrors('npsn');
        $this->assertDatabaseCount('sekolahs', 1);
    }

    /**
     * 4. Latitude 120 ditolak.
     */
    public function test_latitude_120_ditolak(): void
    {
        $payload = $this->validPayload(['latitude' => 120]);

        $response = $this->post(route('dinas.sekolah.store'), $payload);

        $response->assertSessionHasErrors('latitude');
        $this->assertDatabaseCount('sekolahs', 0);
    }

    /**
     * 5. Longitude 200 ditolak.
     */
    public function test_longitude_200_ditolak(): void
    {
        $payload = $this->validPayload(['longitude' => 200]);

        $response = $this->post(route('dinas.sekolah.store'), $payload);

        $response->assertSessionHasErrors('longitude');
        $this->assertDatabaseCount('sekolahs', 0);
    }

    /**
     * 6. Ubah data (NPSN sendiri tidak dianggap ganda).
     */
    public function test_ubah_data_npsn_sendiri_tidak_dianggap_ganda(): void
    {
        $sekolah = Sekolah::create($this->validPayload());

        $response = $this->put(route('sekolah.update', $sekolah), $this->validPayload([
            'nama' => 'SD Negeri Contoh 1 Terupdate',
            'telepon' => '0511-9998888',
        ]));

        $response->assertRedirect(route('dinas.sekolah.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sekolahs', [
            'id' => $sekolah->id,
            'nama' => 'SD Negeri Contoh 1 Terupdate',
            'telepon' => '0511-9998888',
        ]);
    }

    /**
     * 7. Hapus data.
     */
    public function test_hapus_data(): void
    {
        $sekolah = Sekolah::create($this->validPayload());

        $this->assertDatabaseCount('sekolahs', 1);

        $response = $this->delete(route('dinas.sekolah.destroy', $sekolah));

        $response->assertRedirect(route('dinas.sekolah.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('sekolahs', 0);
    }

    /**
     * 8. /data/sekolah mengembalikan FeatureCollection dengan coordinates berurutan [longitude, latitude] dan bertipe angka.
     */
    public function test_data_sekolah_mengembalikan_feature_collection_dengan_coordinates_berurutan_longitude_latitude_dan_bertipe_angka(): void
    {
        Sekolah::create($this->validPayload([
            'latitude' => -3.3194000,
            'longitude' => 114.5908000,
        ]));

        $response = $this->get(route('peta.data'));

        $response->assertStatus(200);
        $response->assertJson([
            'type' => 'FeatureCollection',
        ]);

        $data = $response->json();
        $this->assertCount(1, $data['features']);

        $feature = $data['features'][0];
        $this->assertSame('Feature', $feature['type']);
        $this->assertSame('Point', $feature['geometry']['type']);

        $coordinates = $feature['geometry']['coordinates'];
        $this->assertCount(2, $coordinates);

        // Coordinates urutan GeoJSON: [longitude, latitude] dan bertipe float/number
        $this->assertIsFloat($coordinates[0]);
        $this->assertIsFloat($coordinates[1]);
        $this->assertEquals(114.5908000, $coordinates[0]);
        $this->assertEquals(-3.3194000, $coordinates[1]);

        $this->assertSame('30000001', $feature['properties']['npsn']);
        $this->assertSame('SD Negeri Contoh 1', $feature['properties']['nama']);
    }

    /**
     * 9. /data/sekolah?jenjang=SMA hanya mengembalikan fitur berjenjang SMA.
     */
    public function test_data_sekolah_filter_jenjang_sma_hanya_mengembalikan_fitur_berjenjang_sma(): void
    {
        Sekolah::create($this->validPayload([
            'npsn' => '30000001',
            'nama' => 'SD Negeri Contoh 1',
            'jenjang' => 'SD',
        ]));

        Sekolah::create($this->validPayload([
            'npsn' => '30000005',
            'nama' => 'SMA Negeri Contoh 5',
            'jenjang' => 'SMA',
        ]));

        $response = $this->get(route('peta.data', ['jenjang' => 'SMA']));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(1, $data['features']);
        $this->assertSame('SMA Negeri Contoh 5', $data['features'][0]['properties']['nama']);
        $this->assertSame('SMA', $data['features'][0]['properties']['jenjang']);
    }

    /**
     * 10. / dan /dinas/sekolah berstatus 200.
     */
    public function test_halaman_peta_dan_admin_sekolah_berstatus_200(): void
    {
        $responsePeta = $this->get(route('peta.index'));
        $responsePeta->assertStatus(200);

        $responseAdmin = $this->get(route('dinas.sekolah.index'));
        $responseAdmin->assertStatus(200);
    }
}
