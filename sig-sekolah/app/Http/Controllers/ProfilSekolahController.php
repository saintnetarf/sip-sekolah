<?php

namespace App\Http\Controllers;

use App\Models\ProfilSekolah;
use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfilSekolahController extends Controller
{
    /**
     * Tampilkan formulir pengubahan profil sekolah.
     */
    public function edit(Sekolah $sekolah): View
    {
        Gate::authorize('update', $sekolah);

        $profil = $sekolah->profil ?? new ProfilSekolah;

        return view('sekolah.profil', compact('sekolah', 'profil'));
    }

    /**
     * Simpan pembaruan profil sekolah ke database.
     */
    public function update(Request $request, Sekolah $sekolah): RedirectResponse
    {
        Gate::authorize('update', $sekolah);

        $currentYear = (int) date('Y');

        $validated = $request->validate([
            'kepala_sekolah' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'website' => ['nullable', 'url', 'max:255'],
            'tahun_berdiri' => ['nullable', 'integer', "between:1800,{$currentYear}"],
            'akreditasi' => ['nullable', Rule::in(['A', 'B', 'C', 'Belum Terakreditasi'])],
            'jumlah_siswa' => ['nullable', 'integer', 'min:0'],
            'jumlah_guru' => ['nullable', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ], [
            'kepala_sekolah.max' => 'Nama kepala sekolah maksimal 150 karakter.',
            'email.email' => 'Format email sekolah tidak valid.',
            'email.max' => 'Email sekolah maksimal 150 karakter.',
            'website.url' => 'Format website tidak valid (harus menyertakan http:// atau https://).',
            'website.max' => 'Website maksimal 255 karakter.',
            'tahun_berdiri.integer' => 'Tahun berdiri harus berupa angka tahun.',
            'tahun_berdiri.between' => "Tahun berdiri harus antara tahun 1800 sampai {$currentYear}.",
            'akreditasi.in' => 'Akreditasi harus berupa A, B, C, atau Belum Terakreditasi.',
            'jumlah_siswa.integer' => 'Jumlah siswa harus berupa bilangan bulat.',
            'jumlah_siswa.min' => 'Jumlah siswa tidak boleh negatif.',
            'jumlah_guru.integer' => 'Jumlah guru harus berupa bilangan bulat.',
            'jumlah_guru.min' => 'Jumlah guru tidak boleh negatif.',
            'deskripsi.max' => 'Deskripsi sekolah maksimal 2000 karakter.',
        ]);

        $sekolah->profil()->updateOrCreate([], $validated);

        return back()->with('success', 'Profil sekolah berhasil diperbarui.');
    }
}
