@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ubah Data Sekolah</h1>
            <p class="text-sm text-gray-500 mt-1">Perbarui data sekolah dan sesuaikan titik koordinatnya</p>
        </div>
        @if (auth()->user()->isDinas())
            <a href="{{ route('dinas.sekolah.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                &larr; Kembali ke daftar
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                &larr; Kembali ke dashboard
            </a>
        @endif
    </div>

    @if (view()->exists('sekolah._tab'))
        @include('sekolah._tab')
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
        <form method="POST" action="{{ route('sekolah.update', $sekolah) }}">
            @csrf
            @method('PUT')
            @include('admin' . '/sekolah/_form')
        </form>
    </div>
</div>
@endsection
