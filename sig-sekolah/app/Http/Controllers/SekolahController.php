<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SekolahController extends Controller
{
    /**
     * Tampilkan daftar sekolah dengan pencarian dan paginasi.
     */
    public function index(Request $request): View
    {
        $query = Sekolah::query();

        if ($q = $request->query('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('npsn', 'like', "%{$q}%")
                    ->orWhere('kecamatan', 'like', "%{$q}%");
            });
        }

        $sekolahs = $query->orderBy('nama')->paginate(10)->withQueryString();

        return view('admin.sekolah.index', compact('sekolahs'));
    }

    /**
     * Tampilkan formulir penambahan data sekolah.
     */
    public function create(): View
    {
        $sekolah = new Sekolah();

        return view('admin.sekolah.create', compact('sekolah'));
    }

    /**
     * Simpan data sekolah baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages());

        Sekolah::create($validated);

        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Data sekolah berhasil ditambahkan.');
    }

    /**
     * Tampilkan formulir pengubahan data sekolah.
     */
    public function edit(Sekolah $sekolah): View
    {
        return view('admin.sekolah.edit', compact('sekolah'));
    }

    /**
     * Perbarui data sekolah di database.
     */
    public function update(Request $request, Sekolah $sekolah): RedirectResponse
    {
        $validated = $request->validate($this->rules($sekolah->id), $this->messages());

        $sekolah->update($validated);

        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Data sekolah berhasil diperbarui.');
    }

    /**
     * Hapus data sekolah dari database.
     */
    public function destroy(Sekolah $sekolah): RedirectResponse
    {
        $sekolah->delete();

        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Data sekolah berhasil dihapus.');
    }

    /**
     * Aturan validasi untuk store dan update.
     *
     * @return array<string, mixed>
     */
    private function rules(?int $id = null): array
    {
        return [
            'npsn' => ['required', 'digits:8', Rule::unique('sekolahs', 'npsn')->ignore($id)],
            'nama' => ['required', 'string', 'max:255'],
            'jenjang' => ['required', Rule::in(['SD', 'SMP', 'SMA', 'SMK'])],
            'status' => ['required', Rule::in(['Negeri', 'Swasta'])],
            'alamat' => ['required', 'string'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'npsn.required' => 'NPSN wajib diisi.',
            'npsn.digits' => 'NPSN harus 8 digit angka.',
            'npsn.unique' => 'NPSN sudah terdaftar.',
            'nama.required' => 'Nama sekolah wajib diisi.',
            'nama.max' => 'Nama sekolah maksimal 255 karakter.',
            'jenjang.required' => 'Jenjang wajib dipilih.',
            'jenjang.in' => 'Jenjang tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'alamat.required' => 'Alamat wajib diisi.',
            'kecamatan.required' => 'Kecamatan wajib diisi.',
            'kecamatan.max' => 'Kecamatan maksimal 100 karakter.',
            'telepon.max' => 'Nomor telepon maksimal 20 karakter.',
            'latitude.required' => 'Latitude wajib diisi.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus di antara -90 sampai 90.',
            'longitude.required' => 'Longitude wajib diisi.',
            'longitude.numeric' => 'Longitude harus di antara -180 sampai 180.',
        ];
    }
}
