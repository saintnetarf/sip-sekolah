<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | SIG Sekolah</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        body {
            background-color: #f3f4f6;
            color: #1f2937;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            border: 1px solid #e5e7eb;
            max-width: 480px;
            width: 100%;
            padding: 2.5rem;
            text-align: center;
        }
        .icon-container {
            width: 64px;
            height: 64px;
            background-color: #fee2e2;
            color: #dc2626;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .icon-container svg {
            width: 32px;
            height: 32px;
        }
        .code {
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #dc2626;
            margin-bottom: 0.5rem;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 0.75rem;
        }
        p {
            font-size: 0.95rem;
            color: #4b5563;
            line-height: 1.5;
            margin-bottom: 2rem;
        }
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.25rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #4f46e5;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #4338ca;
        }
        .btn-secondary {
            background-color: #f9fafb;
            color: #4b5563;
            border: 1px solid #d1d5db;
        }
        .btn-secondary:hover {
            background-color: #f3f4f6;
            color: #111827;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-container">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="code">403 Terlarang</div>
        <h1>Akses Ditolak</h1>
        <p>{{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses untuk membuka halaman ini.' }}</p>

        <div class="btn-group">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Kembali ke Dashboard</a>
            @endauth
            <a href="{{ route('peta.index') }}" class="btn {{ auth()->check() ? 'btn-secondary' : 'btn-primary' }}">Kembali ke Peta Publik</a>
        </div>
    </div>
</body>
</html>
