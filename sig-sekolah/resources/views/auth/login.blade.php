<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SIG Sekolah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 antialiased text-gray-800">
    <div class="w-full max-w-md">
        <!-- Logo / Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-200 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">SIG Sekolah</h1>
            <p class="text-sm text-gray-500 mt-1">Masuk untuk mengelola data dan informasi sekolah</p>
        </div>

        <!-- Kartu Form Login -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
            @if ($errors->any())
                <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl flex items-start space-x-2">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Alamat Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:ring-red-400' : 'border-gray-200 focus:border-indigo-500 focus:ring-indigo-500' }} focus:ring-2 focus:ring-opacity-20 text-sm transition outline-none"
                        placeholder="nama@sig.test"
                    >
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Kata Sandi</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-red-400 bg-red-50 focus:ring-red-400' : 'border-gray-200 focus:border-indigo-500 focus:ring-indigo-500' }} focus:ring-2 focus:ring-opacity-20 text-sm transition outline-none"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center space-x-2 text-sm text-gray-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-sm shadow-md shadow-indigo-100 hover:shadow-lg transition duration-150 flex items-center justify-center space-x-2 cursor-pointer"
                >
                    <span>Masuk</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Tautan Kembali -->
        <div class="text-center mt-6">
            <a href="{{ route('peta.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke peta publik
            </a>
        </div>
    </div>
</body>
</html>
