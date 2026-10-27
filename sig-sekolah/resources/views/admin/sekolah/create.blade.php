@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Data Sekolah</h1>
            <p class="text-sm text-gray-500 mt-1">Masukkan data sekolah dan tentukan titik koordinatnya</p>
        </div>
        <a href="{{ route('dinas.sekolah.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
            &larr; Kembali ke daftar
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
        <form method="POST" action="{{ route('dinas.sekolah.store') }}">
            @csrf
            @include('admin' . '/sekolah/_form')
        </form>
    </div>
</div>
@endsection
