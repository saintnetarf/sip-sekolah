<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin SIG Sekolah' }}</title>

    <!-- Tailwind CSS (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800 antialiased">
    <!-- Navigasi -->
    <header class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="flex items-center space-x-2 text-indigo-600 font-bold text-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>SIG Sekolah</span>
                    </a>
                    <nav class="flex space-x-2 sm:space-x-4">
                        <a href="{{ url('/') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->is('/') ? 'text-indigo-700 bg-indigo-50 font-semibold' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' }} transition">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            Peta Publik
                        </a>

                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? 'text-indigo-700 bg-indigo-50 font-semibold' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' }} transition">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Dashboard
                            </a>

                            @if (auth()->user()->isAdmin() && \Illuminate\Support\Facades\Route::has('admin.users.index'))
                                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.users.*') ? 'text-indigo-700 bg-indigo-50 font-semibold' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' }} transition">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    Pengguna
                                </a>
                            @endif

                            @if (auth()->user()->isDinas())
                                <a href="{{ route('dinas.sekolah.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dinas.sekolah.*') ? 'text-indigo-700 bg-indigo-50 font-semibold' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' }} transition">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                    Data Sekolah
                                </a>
                            @endif

                            @if (auth()->user()->isSekolah() && auth()->user()->sekolah_id && \Illuminate\Support\Facades\Route::has('sekolah.edit'))
                                <a href="{{ route('sekolah.edit', auth()->user()->sekolah_id) }}" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('sekolah.*') ? 'text-indigo-700 bg-indigo-50 font-semibold' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' }} transition">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    Sekolah Saya
                                </a>
                            @endif
                        @endauth
                    </nav>
                </div>

                <!-- Sisi Kanan: User Info & Logout -->
                <div class="flex items-center space-x-3">
                    @auth
                        <div class="hidden md:flex flex-col text-right">
                            <span class="text-sm font-semibold text-gray-900 leading-tight">{{ auth()->user()->name }}</span>
                            <span class="text-xs text-indigo-600 font-medium">{{ auth()->user()->roleLabel() }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50 border border-red-200 transition cursor-pointer">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Keluar
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition">
                            Masuk
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Flash Pesan Sukses -->
            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-50 p-4 border border-green-200 text-green-800 flex items-center shadow-sm" role="alert">
                    <svg class="w-5 h-5 mr-3 text-green-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Flash Pesan Error -->
            @if (session('error'))
                <div class="mb-6 rounded-lg bg-red-50 p-4 border border-red-200 text-red-800 flex items-center shadow-sm" role="alert">
                    <svg class="w-5 h-5 mr-3 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium text-sm">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @stack('scripts')
</body>
</html>
