<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Masuk - SIG Sekolah Interaktif</title>

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg-deep: #060913;
            --bg-card: rgba(13, 20, 36, 0.75);
            --border-glow: rgba(56, 189, 248, 0.25);
            --accent-cyan: #06b6d4;
            --accent-blue: #3b82f6;
            --accent-indigo: #6366f1;
            --accent-violet: #8b5cf6;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-deep);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 24px 16px;
        }

        /* Background Animated Grid & Ambient Lights */
        .cyber-grid {
            position: fixed;
            inset: 0;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            mask-image: radial-gradient(circle at 50% 50%, black 30%, transparent 80%);
            -webkit-mask-image: radial-gradient(circle at 50% 50%, black 30%, transparent 80%);
            pointer-events: none;
            z-index: 0;
        }

        /* Glowing Orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.45;
            pointer-events: none;
            z-index: 0;
            animation: orbFloat 16s ease-in-out infinite alternate;
        }

        .orb-1 {
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, #4f46e5 0%, #06b6d4 100%);
            top: -80px;
            left: 15%;
            animation-duration: 14s;
        }

        .orb-2 {
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, #8b5cf6 0%, #ec4899 100%);
            bottom: -60px;
            right: 15%;
            animation-duration: 18s;
            animation-delay: -5s;
        }

        .orb-3 {
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, #0284c7 0%, #10b981 100%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.2;
            animation: orbPulse 8s ease-in-out infinite alternate;
        }

        @keyframes orbFloat {
            0% { transform: translateY(0) scale(1); }
            50% { transform: translateY(35px) scale(1.08); }
            100% { transform: translateY(-25px) scale(0.95); }
        }

        @keyframes orbPulse {
            0% { transform: translate(-50%, -50%) scale(0.9); opacity: 0.15; }
            100% { transform: translate(-50%, -50%) scale(1.15); opacity: 0.28; }
        }

        /* Container & Glass Card */
        .login-wrapper {
            width: 100%;
            max-width: 460px;
            position: relative;
            z-index: 10;
        }

        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 36px 32px;
            box-shadow: 
                0 30px 60px -15px rgba(0, 0, 0, 0.6),
                0 0 35px rgba(99, 102, 241, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.15);
            position: relative;
            overflow: hidden;
        }

        /* High-tech Glowing Top Border */
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #38bdf8, #818cf8, #c084fc, transparent);
        }

        /* Header Elements */
        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .logo-box {
            position: relative;
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-ring {
            position: absolute;
            inset: -4px;
            border-radius: 20px;
            background: linear-gradient(135deg, #06b6d4, #6366f1, #a855f7);
            opacity: 0.75;
            filter: blur(6px);
            animation: ringRotate 8s linear infinite;
        }

        .logo-inner {
            position: relative;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0f172a, #1e1b4b);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #38bdf8;
            box-shadow: inset 0 2px 4px rgba(255, 255, 255, 0.15);
        }

        @keyframes ringRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .badge-telemetry {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            letter-spacing: 0.04em;
            color: #38bdf8;
            background: rgba(6, 182, 212, 0.1);
            border: 1px solid rgba(6, 182, 212, 0.25);
            padding: 4px 10px;
            border-radius: 9999px;
            margin-bottom: 10px;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 8px #10b981;
            animation: blink 2s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.8); }
        }

        .brand-title {
            font-size: 1.65rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #ffffff 30%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 4px;
        }

        .brand-subtitle {
            font-size: 0.84rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        /* Alert Styling */
        .alert-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.35);
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 22px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.83rem;
            color: #fca5a5;
            backdrop-filter: blur(8px);
        }

        /* Form Inputs */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.82rem;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 7px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            width: 18px;
            height: 18px;
            color: #64748b;
            pointer-events: none;
            transition: color 0.2s;
        }

        .cyber-input {
            width: 100%;
            height: 46px;
            background: rgba(10, 15, 29, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 0 42px 0 42px;
            font-family: inherit;
            font-size: 0.88rem;
            color: #f8fafc;
            outline: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .cyber-input:focus {
            background: rgba(15, 23, 42, 0.9);
            border-color: #38bdf8;
            box-shadow: 
                0 0 0 3px rgba(56, 189, 248, 0.2),
                0 0 20px rgba(56, 189, 248, 0.15);
        }

        .cyber-input:focus ~ .input-icon {
            color: #38bdf8;
        }

        .cyber-input.has-error {
            border-color: #ef4444;
            background: rgba(239, 68, 68, 0.08);
        }

        .btn-toggle-pw {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .btn-toggle-pw:hover {
            color: #94a3b8;
        }

        .error-hint {
            font-size: 0.75rem;
            color: #f87171;
            margin-top: 5px;
            font-weight: 500;
        }

        /* Checkbox Custom */
        .checkbox-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .custom-checkbox {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
            font-size: 0.82rem;
            color: #94a3b8;
            transition: color 0.2s;
        }

        .custom-checkbox:hover {
            color: #e2e8f0;
        }

        .custom-checkbox input {
            accent-color: #38bdf8;
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            height: 48px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
            font-size: 0.92rem;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(135deg, #4f46e5 0%, #2563eb 50%, #06b6d4 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 
                0 10px 25px -5px rgba(37, 99, 235, 0.5),
                0 0 15px rgba(6, 182, 212, 0.35);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                60deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 
                0 14px 30px -5px rgba(37, 99, 235, 0.65),
                0 0 25px rgba(6, 182, 212, 0.5);
        }

        .btn-submit:hover::after {
            transform: translateX(100%);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit svg {
            transition: transform 0.2s ease;
        }

        .btn-submit:hover svg {
            transform: translateX(4px);
        }

        /* Quick Account Helper Drawer */
        .demo-section {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px dashed rgba(255, 255, 255, 0.12);
        }

        .demo-label {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            text-align: center;
            margin-bottom: 10px;
        }

        .demo-chips {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
        }

        .chip-btn {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 6px 4px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            color: #94a3b8;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }

        .chip-btn:hover {
            background: rgba(56, 189, 248, 0.15);
            border-color: rgba(56, 189, 248, 0.4);
            color: #38bdf8;
            transform: translateY(-1px);
        }

        /* Footer Link */
        .back-link-wrapper {
            text-align: center;
            margin-top: 20px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.84rem;
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            transition: all 0.2s;
        }

        .back-link:hover {
            color: #38bdf8;
            transform: translateX(-3px);
        }
    </style>
</head>
<body>
    <!-- Background Grid & Glowing Orbs -->
    <div class="cyber-grid"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="login-wrapper">
        <div class="glass-card">
            <!-- Brand Header -->
            <div class="brand-header">
                <div class="logo-box">
                    <div class="logo-ring"></div>
                    <div class="logo-inner">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                            <line x1="9" y1="3" x2="9" y2="18"/>
                            <line x1="15" y1="6" x2="15" y2="21"/>
                            <circle cx="15" cy="11" r="2" fill="#38bdf8"/>
                        </svg>
                    </div>
                </div>

                <div class="badge-telemetry">
                    <span class="status-dot"></span>
                    <span>SISTEM AKTIF &bull; BANJARMASIN</span>
                </div>

                <h1 class="brand-title">SIG Sekolah</h1>
                <p class="brand-subtitle">Portal Autentikasi Pengelolaan Data Spasial &amp; Profil Sekolah</p>
            </div>

            <!-- Error Notification -->
            @if ($errors->any())
                <div class="alert-error">
                    <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-top: 1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Form Login -->
            <form action="{{ route('login') }}" method="POST" autocomplete="on">
                @csrf

                <!-- Input Email -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <span>Alamat Email</span>
                        <span style="font-size: 0.72rem; color: #64748b; font-weight: 400;">Identitas Akun</span>
                    </label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            placeholder="nama@sig.test"
                            class="cyber-input {{ $errors->has('email') ? 'has-error' : '' }}"
                        >
                        <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                    </div>
                    @error('email')
                        <div class="error-hint">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Input Password -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <span>Kata Sandi</span>
                        <span style="font-size: 0.72rem; color: #64748b; font-weight: 400;">Kunci Keamanan</span>
                    </label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                            class="cyber-input {{ $errors->has('password') ? 'has-error' : '' }}"
                        >
                        <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <button type="button" class="btn-toggle-pw" id="btn-toggle-pw" title="Lihat Kata Sandi">
                            <svg id="pw-eye-open" style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="pw-eye-closed" style="width: 18px; height: 18px; display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="error-hint">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="checkbox-row">
                    <label class="custom-checkbox">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Ingat sesi saya</span>
                    </label>
                </div>

                <!-- Tombol Submit -->
                <button type="submit" class="btn-submit">
                    <span>Masuk ke Sistem</span>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </form>

            <!-- Quick Demo Credentials Helper -->
            <div class="demo-section">
                <div class="demo-label">Akses Demo Sekali Klik</div>
                <div class="demo-chips">
                    <button type="button" class="chip-btn" data-email="admin@sig.test">Admin</button>
                    <button type="button" class="chip-btn" data-email="dinas@sig.test">Dinas</button>
                    <button type="button" class="chip-btn" data-email="sekolah1@sig.test">Sekolah 1</button>
                    <button type="button" class="chip-btn" data-email="sekolah2@sig.test">Sekolah 2</button>
                </div>
            </div>
        </div>

        <!-- Back to Public Map Link -->
        <div class="back-link-wrapper">
            <a href="{{ route('peta.index') }}" class="back-link">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke Peta Publik</span>
            </a>
        </div>
    </div>

    <!-- Script Interaktif Toggle Password & Quick Demo Fill -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Toggle Show/Hide Password
            const pwInput = document.getElementById('password');
            const btnTogglePw = document.getElementById('btn-toggle-pw');
            const eyeOpen = document.getElementById('pw-eye-open');
            const eyeClosed = document.getElementById('pw-eye-closed');

            if (btnTogglePw && pwInput) {
                btnTogglePw.addEventListener('click', function () {
                    const isPassword = pwInput.type === 'password';
                    pwInput.type = isPassword ? 'text' : 'password';
                    eyeOpen.style.display = isPassword ? 'none' : 'block';
                    eyeClosed.style.display = isPassword ? 'block' : 'none';
                    pwInput.focus();
                });
            }

            // 2. Quick Demo Fill Chips
            const emailInput = document.getElementById('email');
            const chips = document.querySelectorAll('.chip-btn');

            chips.forEach(chip => {
                chip.addEventListener('click', function () {
                    const email = this.getAttribute('data-email');
                    if (emailInput && pwInput) {
                        emailInput.value = email;
                        pwInput.value = 'password123';

                        // Subtle flash effect on inputs
                        [emailInput, pwInput].forEach(el => {
                            el.style.borderColor = '#38bdf8';
                            el.style.boxShadow = '0 0 15px rgba(56, 189, 248, 0.4)';
                            setTimeout(() => {
                                el.style.borderColor = '';
                                el.style.boxShadow = '';
                            }, 500);
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
