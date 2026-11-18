<?php

namespace Database\Seeders;

use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sekolahs = Sekolah::orderBy('id')->take(2)->get();
        $sekolah1Id = $sekolahs->get(0)?->id;
        $sekolah2Id = $sekolahs->get(1)?->id;

        $users = [
            [
                'email' => 'admin@sig.test',
                'name' => 'Admin Sistem',
                'password' => 'password123',
                'role' => User::ROLE_ADMIN,
                'sekolah_id' => null,
            ],
            [
                'email' => 'dinas@sig.test',
                'name' => 'Dinas Pendidikan',
                'password' => 'password123',
                'role' => User::ROLE_DINAS,
                'sekolah_id' => null,
            ],
            [
                'email' => 'sekolah1@sig.test',
                'name' => 'Operator Sekolah 1',
                'password' => 'password123',
                'role' => User::ROLE_SEKOLAH,
                'sekolah_id' => $sekolah1Id,
            ],
            [
                'email' => 'sekolah2@sig.test',
                'name' => 'Operator Sekolah 2',
                'password' => 'password123',
                'role' => User::ROLE_SEKOLAH,
                'sekolah_id' => $sekolah2Id,
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
    }
}
