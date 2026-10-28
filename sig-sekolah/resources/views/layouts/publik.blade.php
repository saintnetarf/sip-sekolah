<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIG Sekolah - Peta Digital Sekolah Kota Banjarmasin')</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1f2937;
            background-color: #f8fafc;
            overflow: hidden;
        }

        body {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        /* Header Navbar 56px */
        header.nav-header {
            height: 56px;
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            z-index: 1000;
            flex-shrink: 0;
            gap: 16px;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 1.1rem;
            color: #2563eb;
            text-decoration: none;
            white-space: nowrap;
        }

        .header-brand svg {
            width: 22px;
            height: 22px;
            stroke: currentColor;
        }

        nav.nav-menu {
            display: flex;
            align-items: center;
            gap: 4px;
            overflow-x: auto;
            white-space: nowrap;
            height: 100%;
            scrollbar-width: none;
        }

        nav.nav-menu::-webkit-scrollbar {
            display: none;
        }

        .nav-link {
            display: inline-flex;
            align-items: center;
            height: 100%;
            padding: 0 12px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
            text-decoration: none;
            border-bottom: 2px solid transparent;
            transition: color 0.15s, border-color 0.15s;
        }

        .nav-link:hover {
            color: #1d4ed8;
        }

        .nav-link.aktif {
            color: #2563eb;
            font-weight: 600;
            border-bottom-color: #2563eb;
        }

        .header-auth {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .btn-auth {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: background-color 0.15s;
        }

        .btn-auth:hover {
            background-color: #1d4ed8;
        }

        /* Layout Main */
        main.main-content {
            position: relative;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            height: calc(100vh - 56px);
        }

        /* Shared CSS Classes */
        .toolbar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 10px 16px;
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            z-index: 500;
            flex-shrink: 0;
            font-size: 0.875rem;
        }

        .peta {
            flex: 1;
            min-height: 300px;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .panel {
            position: absolute;
            z-index: 1000;
            background-color: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(6px);
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            font-size: 0.875rem;
        }

        /* Common Form elements */
        .form-select, .form-input {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #1e293b;
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .form-select:focus, .form-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #334155;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-action:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
            color: #ffffff;
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>

    @stack('styles')
</head>
<body>
    <header class="nav-header">
        <a href="{{ route('peta.index') }}" class="header-brand">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span>SIG Sekolah</span>
        </a>

        @php
            $navMenus = [
                'peta.index' => 'Peta Sekolah',
                'peta.cari' => 'Pencarian',
                'peta.klaster' => 'Klaster',
                'peta.panas' => 'Peta Panas',
                'peta.wilayah' => 'Wilayah',
                'peta.rute' => 'Rute',
                'peta.alamat' => 'Cek Lokasi',
            ];
        @endphp

        <nav class="nav-menu">
            @foreach ($navMenus as $nama => $label)
                @if (Route::has($nama))
                    <a href="{{ route($nama) }}" class="nav-link {{ request()->routeIs($nama) ? 'aktif' : '' }}">{{ $label }}</a>
                @endif
            @endforeach
        </nav>

        <div class="header-auth">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-auth">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn-auth">Masuk</a>
            @endauth
        </div>
    </header>

    <main class="main-content">
        @yield('content')
    </main>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        window.PETA_URL = {
            data: "{{ route('peta.data') }}"@if (Route::has('peta.data.wilayah')),
            wilayah: "{{ route('peta.data.wilayah') }}"@endif
        };
    </script>

    <script src="{{ asset('js/peta.js') }}"></script>

    @stack('scripts')
</body>
</html>
