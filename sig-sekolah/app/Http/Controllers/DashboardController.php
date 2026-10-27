<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $data = [
            'user' => $user,
        ];

        if ($user->isAdmin()) {
            $data['userCounts'] = [
                'admin' => User::where('role', User::ROLE_ADMIN)->count(),
                'dinas' => User::where('role', User::ROLE_DINAS)->count(),
                'sekolah' => User::where('role', User::ROLE_SEKOLAH)->count(),
            ];
        } elseif ($user->isDinas()) {
            $data['totalSekolah'] = Sekolah::count();
        } elseif ($user->isSekolah()) {
            $sekolah = $user->sekolah_id ? $user->sekolah()->with('profil')->first() : null;
            $persentaseProfil = 0;

            if ($sekolah && $sekolah->profil) {
                $columns = [
                    'kepala_sekolah',
                    'email',
                    'website',
                    'tahun_berdiri',
                    'akreditasi',
                    'jumlah_siswa',
                    'jumlah_guru',
                    'deskripsi',
                ];

                $filled = 0;
                foreach ($columns as $column) {
                    $val = $sekolah->profil->{$column};
                    if (! is_null($val) && trim((string) $val) !== '') {
                        $filled++;
                    }
                }

                $persentaseProfil = (int) round(($filled / 8) * 100);
            }

            $data['sekolah'] = $sekolah;
            $data['persentaseProfil'] = $persentaseProfil;
        }

        return view('dashboard', $data);
    }
}
