<?php

namespace Tests\Feature;

use App\Models\ProfilSekolah;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /**
     * Helper untuk membuat data sekolah uji.
     */
    private function createSekolah(array $overrides = []): Sekolah
    {
        static $counter = 1000;
        $counter++;

        return Sekolah::create(array_merge([
            'npsn' => str_pad((string) $counter, 8, '3000', STR_PAD_LEFT),
            'nama' => 'Sekolah Uji '.$counter,
            'jenjang' => 'SMP',
            'status' => 'Negeri',
            'alamat' => 'Jl. Pengujian No. '.$counter,
            'kecamatan' => 'Banjarmasin Tengah',
            'telepon' => '0511-123456',
            'latitude' => -3.3194000,
            'longitude' => 114.5908000,
        ], $overrides));
    }

    /**
     * 1. Tamu membuka /dashboard dialihkan ke /login.
     */
    public function test_tamu_membuka_dashboard_dialihkan_ke_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * 2. Login gagal menghasilkan error pada email, login benar dialihkan ke /dashboard dan terautentikasi.
     */
    public function test_login_gagal_menghasilkan_error_pada_email_dan_login_benar_dialihkan_ke_dashboard(): void
    {
        User::factory()->create([
            'email' => 'pengguna@sig.test',
            'password' => 'password',
        ]);

        // Login dengan password salah
        $responseGagal = $this->post('/login', [
            'email' => 'pengguna@sig.test',
            'password' => 'passwordsalah',
        ]);

        $responseGagal->assertSessionHasErrors('email');
        $this->assertGuest();

        // Login dengan kredensial benar
        $responseBenar = $this->post('/login', [
            'email' => 'pengguna@sig.test',
            'password' => 'password',
        ]);

        $responseBenar->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    /**
     * 3. Admin dapat membuka /admin/users tetapi mendapat 403 pada /dinas/sekolah.
     */
    public function test_admin_dapat_membuka_admin_users_tetapi_mendapat_403_pada_dinas_sekolah(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $responseUsers = $this->actingAs($admin)->get('/admin/users');
        $responseUsers->assertStatus(200);

        $responseDinas = $this->actingAs($admin)->get('/dinas/sekolah');
        $responseDinas->assertStatus(403);
    }

    /**
     * 4. Admin menambah pengguna role sekolah dan tersimpan beserta sekolah_id.
     */
    public function test_admin_menambah_pengguna_role_sekolah_dan_tersimpan_beserta_sekolah_id(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $sekolah = $this->createSekolah();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Operator Sekolah Baru',
            'email' => 'operatorbaru@sig.test',
            'role' => User::ROLE_SEKOLAH,
            'sekolah_id' => $sekolah->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'operatorbaru@sig.test',
            'role' => User::ROLE_SEKOLAH,
            'sekolah_id' => $sekolah->id,
        ]);
    }

    /**
     * 5. Role sekolah tanpa sekolah_id ditolak validasi.
     */
    public function test_role_sekolah_tanpa_sekolah_id_ditolak_validasi(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Operator Tanpa Sekolah',
            'email' => 'tanpasekolah@sig.test',
            'role' => User::ROLE_SEKOLAH,
            'sekolah_id' => null,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('sekolah_id');
        $this->assertDatabaseMissing('users', [
            'email' => 'tanpasekolah@sig.test',
        ]);
    }

    /**
     * 6. Dinas dapat membuka daftar sekolah, form ubah, dan profil sekolah mana pun, tetapi mendapat 403 pada /admin/users.
     */
    public function test_dinas_dapat_kelola_sekolah_mana_pun_tetapi_403_pada_admin_users(): void
    {
        $dinas = User::factory()->create(['role' => User::ROLE_DINAS]);
        $sekolah1 = $this->createSekolah();
        $sekolah2 = $this->createSekolah();

        // Buka daftar sekolah
        $this->actingAs($dinas)->get(route('dinas.sekolah.index'))->assertStatus(200);

        // Buka form ubah sekolah manapun
        $this->actingAs($dinas)->get(route('sekolah.edit', $sekolah1))->assertStatus(200);
        $this->actingAs($dinas)->get(route('sekolah.edit', $sekolah2))->assertStatus(200);

        // Buka profil sekolah manapun
        $this->actingAs($dinas)->get(route('sekolah.profil.edit', $sekolah1))->assertStatus(200);
        $this->actingAs($dinas)->get(route('sekolah.profil.edit', $sekolah2))->assertStatus(200);

        // Mendapat 403 pada /admin/users
        $this->actingAs($dinas)->get('/admin/users')->assertStatus(403);
    }

    /**
     * 7. Sekolah dapat membuka edit sekolahnya, mendapat 403 untuk edit dan profil sekolah lain, /dinas/sekolah, dan /admin/users.
     */
    public function test_sekolah_hanya_bisa_akses_sekolahnya_sendiri_dan_terkunci_dari_yang_lain(): void
    {
        $sekolahA = $this->createSekolah();
        $sekolahB = $this->createSekolah();

        $userSekolahA = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'sekolah_id' => $sekolahA->id,
        ]);

        // Dapat membuka edit sekolahnya sendiri
        $this->actingAs($userSekolahA)->get(route('sekolah.edit', $sekolahA))->assertStatus(200);

        // Mendapat 403 untuk edit sekolah lain
        $this->actingAs($userSekolahA)->get(route('sekolah.edit', $sekolahB))->assertStatus(403);

        // Mendapat 403 untuk profil sekolah lain
        $this->actingAs($userSekolahA)->get(route('sekolah.profil.edit', $sekolahB))->assertStatus(403);

        // Mendapat 403 untuk /dinas/sekolah
        $this->actingAs($userSekolahA)->get('/dinas/sekolah')->assertStatus(403);

        // Mendapat 403 untuk /admin/users
        $this->actingAs($userSekolahA)->get('/admin/users')->assertStatus(403);
    }

    /**
     * 8. Sekolah mengubah data sekolahnya tetapi NPSN tidak berubah walau dikirim nilai berbeda.
     */
    public function test_sekolah_mengubah_data_sekolahnya_tetapi_npsn_tidak_berubah(): void
    {
        $sekolah = $this->createSekolah(['npsn' => '30008888']);
        $user = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'sekolah_id' => $sekolah->id,
        ]);

        $response = $this->actingAs($user)->put(route('sekolah.update', $sekolah), [
            'npsn' => '99999999', // mencoba mengganti NPSN
            'nama' => 'SMP Negeri Uji Diperbarui',
            'jenjang' => $sekolah->jenjang,
            'status' => $sekolah->status,
            'alamat' => 'Alamat baru sekolah',
            'kecamatan' => $sekolah->kecamatan,
            'telepon' => $sekolah->telepon,
            'latitude' => $sekolah->latitude,
            'longitude' => $sekolah->longitude,
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $sekolah->refresh();
        $this->assertSame('30008888', $sekolah->npsn);
        $this->assertSame('SMP Negeri Uji Diperbarui', $sekolah->nama);
        $this->assertSame('Alamat baru sekolah', $sekolah->alamat);
    }

    /**
     * 9. Sekolah menyimpan profil sekolahnya (tercatat pada profil_sekolahs).
     */
    public function test_sekolah_menyimpan_profil_sekolahnya_tercatat_pada_profil_sekolahs(): void
    {
        $sekolah = $this->createSekolah();
        $user = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'sekolah_id' => $sekolah->id,
        ]);

        $response = $this->actingAs($user)->put(route('sekolah.profil.update', $sekolah), [
            'kepala_sekolah' => 'Drs. H. Mulyadi, M.Pd',
            'email' => 'smp@sekolah.sch.id',
            'website' => 'https://smpuji.sch.id',
            'tahun_berdiri' => 1998,
            'akreditasi' => 'A',
            'jumlah_siswa' => 520,
            'jumlah_guru' => 32,
            'deskripsi' => 'Sekolah unggulan berwawasan lingkungan.',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('profil_sekolahs', [
            'sekolah_id' => $sekolah->id,
            'kepala_sekolah' => 'Drs. H. Mulyadi, M.Pd',
            'akreditasi' => 'A',
            'jumlah_siswa' => 520,
            'jumlah_guru' => 32,
        ]);
    }

    /**
     * 10. Sekolah mendapat 403 saat mengirim DELETE ke /dinas/sekolah/{id} dan data tetap ada.
     */
    public function test_sekolah_mendapat_403_saat_mengirim_delete_ke_dinas_sekolah_dan_data_tetap_ada(): void
    {
        $sekolah = $this->createSekolah();
        $user = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'sekolah_id' => $sekolah->id,
        ]);

        $response = $this->actingAs($user)->delete("/dinas/sekolah/{$sekolah->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('sekolahs', [
            'id' => $sekolah->id,
        ]);
    }

    /**
     * 11. Enam percobaan login gagal beruntun dalam satu menit menghasilkan status 429.
     */
    public function test_enam_percobaan_login_gagal_beruntun_dalam_satu_menit_menghasilkan_429(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'wrong@sig.test',
                'password' => 'wrongpassword',
            ]);
            $response->assertSessionHasErrors('email');
        }

        // Percobaan ke-6 harus terkena rate limiter 429 Too Many Requests
        $responseKeenam = $this->post('/login', [
            'email' => 'wrong@sig.test',
            'password' => 'wrongpassword',
        ]);

        $responseKeenam->assertStatus(429);
    }

    /**
     * 12. Admin tidak dapat menghapus akunnya sendiri dan tidak dapat menurunkan perannya sendiri.
     */
    public function test_admin_tidak_dapat_menghapus_atau_menurunkan_role_akunnya_sendiri(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        // Coba hapus akun sendiri
        $responseHapus = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));
        $responseHapus->assertRedirect(route('admin.users.index'));
        $responseHapus->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        // Coba turunkan peran akun sendiri menjadi dinas
        $responseUbah = $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => User::ROLE_DINAS,
        ]);
        $responseUbah->assertSessionHasErrors('role');
        $admin->refresh();
        $this->assertSame(User::ROLE_ADMIN, $admin->role);
    }

    /**
     * 13. GeoJSON publik memuat profil (kepala_sekolah, akreditasi) dan tidak memuat data kontak pribadi (email, website).
     */
    public function test_geojson_publik_memuat_profil_dan_tidak_memuat_email_serta_website(): void
    {
        $sekolah = $this->createSekolah();
        ProfilSekolah::create([
            'sekolah_id' => $sekolah->id,
            'kepala_sekolah' => 'Bapak Kepala Uji',
            'email' => 'rahasia@sekolah.sch.id',
            'website' => 'https://rahasia.sch.id',
            'akreditasi' => 'B',
            'jumlah_siswa' => 300,
            'jumlah_guru' => 20,
        ]);

        $response = $this->get(route('peta.data'));
        $response->assertStatus(200);

        $json = $response->json();
        $this->assertNotEmpty($json['features']);
        $properties = $json['features'][0]['properties'];

        $this->assertSame('Bapak Kepala Uji', $properties['kepala_sekolah']);
        $this->assertSame('B', $properties['akreditasi']);
        $this->assertSame(300, $properties['jumlah_siswa']);
        $this->assertSame(20, $properties['jumlah_guru']);

        // Pastikan email dan website tidak diekspos di GeoJSON publik
        $this->assertArrayNotHasKey('email', $properties);
        $this->assertArrayNotHasKey('website', $properties);
    }

    /**
     * 14. Dashboard menampilkan ringkasan data yang tepat untuk tiap peran.
     */
    public function test_dashboard_menampilkan_ringkasan_sesuai_masing_masing_role(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $dinas = User::factory()->create(['role' => User::ROLE_DINAS]);
        $sekolah = $this->createSekolah();
        $userSekolah = User::factory()->create(['role' => User::ROLE_SEKOLAH, 'sekolah_id' => $sekolah->id]);

        // Dashboard Admin memuat userCounts
        $this->actingAs($admin)->get('/dashboard')
            ->assertStatus(200)
            ->assertViewHas('userCounts');

        // Dashboard Dinas memuat totalSekolah
        $this->actingAs($dinas)->get('/dashboard')
            ->assertStatus(200)
            ->assertViewHas('totalSekolah');

        // Dashboard Sekolah memuat sekolah dan persentaseProfil
        $this->actingAs($userSekolah)->get('/dashboard')
            ->assertStatus(200)
            ->assertViewHas('persentaseProfil')
            ->assertViewHas('sekolah');
    }
}
