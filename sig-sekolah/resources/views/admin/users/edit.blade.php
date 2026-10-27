@extends('layouts.app', ['title' => 'Ubah Pengguna - ' . $user->name])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ubah Data Pengguna</h1>
            <p class="text-sm text-gray-500 mt-1">Perbarui data profil, peran, atau kata sandi pengguna</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
            &larr; Kembali ke daftar
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('admin.users._form')
        </form>
    </div>
</div>
@endsection
