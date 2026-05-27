<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Read Assist' }}</title>
    <meta name="description" content="Read-Assist: Platform aksesibilitas buku audio untuk tunanetra. Dengarkan buku cetak dengan pemindaian QR code dan pemutaran teks otomatis.">
    <meta name="keywords" content="buku audio, tunanetra, aksesibilitas, read-assist, qr code, text to speech, Indonesia">
    <meta name="author" content="Read-Assist">
    <meta property="og:title" content="Read Assist - Buku Audio untuk Tunanetra">
    <meta property="og:description" content="Platform aksesibilitas buku audio untuk tunanetra. Cukup pindai QR code untuk mendengarkan.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="theme-color" content="#09090b">
    <link rel="icon" type="image/png" href="/logo.png">
    <link rel="apple-touch-icon" href="/logo.png">
    <link rel="manifest" href="/manifest.json">
    @php
        $hasBuild = file_exists(public_path('build/manifest.json'));
        $hasHot = file_exists(public_path('hot'));
        if ($hasBuild) {
            $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        }
    @endphp
    @if ($hasBuild)
        <link rel="stylesheet" href="{{ asset('build/' . ($manifest['resources/css/app.css']['file'] ?? 'assets/app.css')) }}">
    @endif
    @if ($hasHot || $hasBuild)
        @vite(['resources/js/app.js'])
    @endif
    <style>
        /* Modern, minimal layout styling */
        :root {
            --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            --accent-primary: #818cf8;
            --accent-secondary: #60a5fa;
            --accent-success: #34d399;
            --accent-danger: #f87171;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border-glass: rgba(255, 255, 255, 0.08);
            --bg-secondary: #0d0e12;
            --bg-glass: rgba(22, 25, 35, 0.6);
        }

        body {
            background-color: #0c0d10 !important;
            color: #cbd5e1 !important;
            font-family: var(--font-sans) !important;
        }

        /* Navbar Enhancements */
        .nav-icon-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.15rem;
            cursor: pointer;
            padding: 0.4rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
            position: relative;
        }
        .nav-icon-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.06);
        }
        .notification-badge {
            position: absolute;
            top: 3px;
            right: 3px;
            width: 7px;
            height: 7px;
            background-color: var(--accent-danger);
            border-radius: 50%;
        }
        
        /* Dropdown Styles */
        .nav-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: -10px;
            background: #121316;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 0.5rem;
            width: 240px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
            display: none;
            flex-direction: column;
            gap: 0.3rem;
            z-index: 1000;
            animation: fade-in 0.1s ease;
        }
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dropdown-header {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.3rem 0.5rem 0.4rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .dropdown-item {
            display: flex;
            gap: 0.5rem;
            font-size: 0.78rem;
            line-height: 1.4;
            color: var(--text-secondary);
            padding: 0.4rem 0.5rem;
            border-radius: 6px;
            transition: all 0.12s ease;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
        }
        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }
        .dropdown-item .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent-primary);
            margin-top: 5px;
            flex-shrink: 0;
        }
        .dropdown-text {
            margin: 0;
            color: var(--text-secondary);
        }
        .dropdown-time {
            font-size: 0.65rem;
            color: var(--text-muted);
            margin-top: 0.15rem;
            display: block;
        }
        
        .dropdown-item-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.12s ease;
        }
        .dropdown-item-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }
        .dropdown-logout-btn:hover {
            background: rgba(248, 113, 113, 0.08) !important;
            color: #f87171 !important;
        }

        .nav-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.8rem !important;
            transition: all 0.15s ease;
        }
        .nav-link.active {
            color: #fff !important;
        }
        
        .avatar-btn:hover {
            opacity: 0.95;
        }
        
        /* Search Input */
        .nav-search-form input:focus {
            background: rgba(255,255,255,0.08) !important;
            border-color: rgba(255,255,255,0.15) !important;
            box-shadow: none !important;
        }

        /* Navbar style */
        .navbar {
            background-color: #121316 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
        }

        .navbar .menu-horizontal > li > a {
            padding: 0 !important;
        }
        .navbar .menu-horizontal {
            gap: 0.25rem !important;
        }

        /* Clean nav buttons */
        .nav-btn {
            position: relative;
            display: flex !important;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.85rem !important;
            border-radius: 6px !important;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text-secondary) !important;
            background: transparent;
            border: 1px solid transparent;
            transition: all 0.15s ease !important;
            text-decoration: none;
            cursor: pointer;
        }
        .nav-btn:hover {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.05) !important;
        }
        .nav-btn.active {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.07) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        .nav-btn .nav-btn-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            opacity: 0.7;
        }
        .nav-btn:hover .nav-btn-icon {
            opacity: 0.95;
        }
    </style>
</head>
<body>
    @if (!session()->has('qr_restricted_token') || session()->has('auth_role'))
    <nav class="navbar bg-base-300/50 backdrop-blur-lg border-b border-white/10 sticky top-0 z-[1000] shadow-lg">
        <div class="navbar-start gap-2">
            <a href="/" class="flex items-center gap-3 no-underline">
                <img src="/logo.png" alt="Read-Assist Logo" class="h-10 w-10 rounded-full object-cover border-2 border-indigo-500 transition-transform hover:scale-105">
                <span class="text-xl font-bold text-white tracking-tight">
                    <span class="text-indigo-400">Read</span>-Assist
                </span>
            </a>
        </div>

        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1 gap-1">
                <li>
                    <a href="/" class="nav-btn {{ request()->is('/') ? 'active' : '' }}">
                        <svg class="nav-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                        <span class="nav-btn-text">Beranda</span>
                    </a>
                </li>
                <li>
                    <a href="/katalog-audio" class="nav-btn {{ request()->is('katalog-audio') ? 'active' : '' }}">
                        <svg class="nav-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                        <span class="nav-btn-text">Katalog Buku</span>
                    </a>
                </li>
                @if (session('auth_role') === 'admin')
                    <li>
                        <a href="/katalog-audio/tambah" class="nav-btn {{ request()->is('katalog-audio/tambah') ? 'active' : '' }}">
                            <svg class="nav-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            <span class="nav-btn-text">Tambah Buku</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/dashboard" class="nav-btn {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                            <svg class="nav-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                            <span class="nav-btn-text">Dashboard Admin</span>
                        </a>
                    </li>
                @elseif (session('auth_role') === 'user')
                    <li>
                        <a href="/user/tambah-buku" class="nav-btn {{ request()->is('user/tambah-buku') ? 'active' : '' }}">
                            <svg class="nav-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            <span class="nav-btn-text">Tambah Buku Saya</span>
                        </a>
                    </li>
                    <li>
                        <a href="/user/dashboard" class="nav-btn {{ request()->is('user/dashboard') ? 'active' : '' }}">
                            <svg class="nav-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                            <span class="nav-btn-text">Dashboard User</span>
                        </a>
                    </li>
                @endif
                <li>
                    <a href="/read-assist" class="nav-btn {{ request()->is('read-assist') || request()->is('read-assist*') ? 'active' : '' }}">
                        <svg class="nav-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        <span class="nav-btn-text">Read Assist</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="navbar-end gap-2">
                <div class="nav-accessibility-wrapper accessibility-nav-wrapper">
                    <button class="nav-icon-btn accessibility-nav-trigger" onclick="toggleAccessibilityDropdown(event)" title="Opsi Aksesibilitas" aria-label="Buka opsi aksesibilitas" aria-controls="accessibility-dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="16" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h2"/><circle cx="9" cy="7" r="4"/><path d="M1 21v-2a4 4 0 0 1 4-4"/></svg>
                    </button>
                    <div id="accessibility-dropdown" class="nav-dropdown accessibility-panel accessibility-panel--nav" style="display: none;">
                        <div class="accessibility-panel-header">
                            <div>
                                <span class="accessibility-kicker">Read-Assist</span>
                                <h2 class="accessibility-title">Aksesibilitas</h2>
                            </div>
                        </div>

                        <div class="accessibility-control-row">
                            <div>
                                <span class="accessibility-control-title">Kontras Tinggi</span>
                                <span class="accessibility-control-desc">Warna lebih tegas</span>
                            </div>
                            <button id="nav-btn-contrast" class="accessibility-toggle-btn" type="button">Aktifkan</button>
                        </div>

                        <div class="accessibility-control-group">
                            <div class="accessibility-control-row accessibility-control-row--stacked">
                                <div>
                                    <span class="accessibility-control-title">Ukuran Teks</span>
                                    <span class="accessibility-control-desc">Status: <strong id="nav-text-size-lbl">Normal</strong></span>
                                </div>
                            </div>
                            <div class="accessibility-size-options" role="group" aria-label="Pilih ukuran teks">
                                <button onclick="changeTextSize('normal')" class="accessibility-size-btn" data-text-size="normal" type="button">A</button>
                                <button onclick="changeTextSize('large')" class="accessibility-size-btn accessibility-size-btn--large" data-text-size="large" type="button">A+</button>
                                <button onclick="changeTextSize('xlarge')" class="accessibility-size-btn accessibility-size-btn--xlarge" data-text-size="xlarge" type="button">A++</button>
                            </div>
                        </div>

                        <div class="accessibility-control-row">
                            <div>
                                <span class="accessibility-control-title">Suara Pendamping</span>
                                <span class="accessibility-control-desc">Baca panduan elemen</span>
                            </div>
                            <button id="nav-btn-speech" class="accessibility-toggle-btn" type="button">Aktifkan</button>
                        </div>
                    </div>
                </div>


                @if (session('auth_role'))
                    <div class="nav-notification-wrapper" style="position: relative;">
                        <button class="nav-icon-btn" onclick="toggleNotificationDropdown(event)" title="Notifikasi">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    </button>
                        <div id="notification-dropdown" class="nav-dropdown" style="display: none; right: -50px; width: 260px;">
                            <div class="dropdown-header">Notifikasi Sistem</div>
                            <a href="{{ session('auth_role') === 'admin' ? '/admin/dashboard' : '/user/dashboard' }}" class="dropdown-item">
                                <span class="dot active"></span>
                                <div>
                                    <p class="dropdown-text" style="margin: 0; font-size: 0.78rem;">Sistem TTS berjalan optimal via API lokal.</p>
                                    <span class="dropdown-time">Baru saja</span>
                                </div>
                            </a>
                            <a href="/katalog-audio" class="dropdown-item">
                                <span class="dot"></span>
                                <div>
                                    <p class="dropdown-text" style="margin: 0; font-size: 0.78rem;">Buku audio baru ditambahkan ke katalog.</p>
                                    <span class="dropdown-time">10 menit yang lalu</span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="avatar-dropdown-wrapper" style="position: relative;">
                        <button class="avatar-btn" onclick="toggleAvatarDropdown(event)" style="background: none; border: none; padding: 0; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; font-size: 0.95rem; border: 2.2px solid rgba(255,255,255,0.15); box-shadow: 0 4px 10px rgba(0,0,0,0.25);">
                                {{ strtoupper(substr(session('auth_name'), 0, 1)) }}
                            </div>
                            <span style="font-size: 0.85rem; color: #fff; font-weight: 600;" class="nav-user-name-text">{{ session('auth_name') }}</span>
                            <span style="font-size: 0.6rem; color: var(--text-muted);">▼</span>
                        </button>
                        <div id="avatar-dropdown" class="nav-dropdown" style="display: none; right: 0; min-width: 190px;">
                            <div class="dropdown-header" style="border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 0.6rem; margin-bottom: 0.4rem;">
                                <div style="font-weight: 700; color: #fff; font-size: 0.88rem;">{{ session('auth_name') }}</div>
                                <div style="font-size: 0.7rem; color: var(--accent-primary); font-weight: bold; text-transform: uppercase; margin-top: 0.1rem;">{{ session('auth_role') }}</div>
                            </div>
                            
                            @if (session('auth_role') === 'admin')
                                <a class="dropdown-item-link" href="/admin/dashboard">Dashboard Admin</a>
                            @elseif (session('auth_role') === 'user')
                                <a class="dropdown-item-link" href="/user/dashboard">Dashboard User</a>
                            @endif
                            <a class="dropdown-item-link" href="/katalog-audio">Katalog Buku</a>
                            <a class="dropdown-item-link" href="/profile">Profil Saya</a>
                            
                            <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.08); margin: 0.4rem 0;">
                            
                            <form method="POST" action="/logout" style="margin: 0;">
                                @csrf
                                <button type="submit" class="dropdown-item-link dropdown-logout-btn" style="width: 100%; text-align: left; background: none; border: none; color: var(--accent-danger); cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                                    Keluar (Logout)
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="/login" class="nav-btn {{ request()->is('login') ? 'active' : '' }}">
                        <svg class="nav-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                        <span class="nav-btn-text">Login</span>
                    </a>
                    <a href="/register" class="btn btn-primary btn-sm shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all duration-200 hover:scale-105">
                        <svg class="nav-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg> Daftar
                    </a>
                @endif
            </div>
        </div>
    </nav>
    @endif

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('audio'))
            <div class="alert alert-danger">
                {{ $errors->first('audio') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer style="text-align: center; padding: 2rem; margin-top: 3rem; border-top: 1px solid var(--border-glass); background: rgba(0,0,0,0.2);">
        <p style="margin: 0; font-size: 0.9rem;">&copy; {{ date('Y') }} Read Assist. Sistem Pendukung Belajar Mandiri untuk Tunanetra.</p>
    </footer>

    <div id="accessibility-widget" class="accessibility-widget">
        <button id="accessibility-toggle-btn" class="accessibility-floating-btn" type="button" title="Menu Aksesibilitas" aria-label="Buka menu aksesibilitas" aria-controls="accessibility-menu" aria-expanded="false">
            <span aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="16" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h2"/><circle cx="9" cy="7" r="4"/><path d="M1 21v-2a4 4 0 0 1 4-4"/></svg>
                    </span>
        </button>
        <div id="accessibility-menu" class="accessibility-panel accessibility-panel--floating" style="display: none;" role="dialog" aria-label="Menu aksesibilitas">
            <div class="accessibility-panel-header">
                <div>
                    <span class="accessibility-kicker">Read-Assist</span>
                    <h2 class="accessibility-title">Opsi Aksesibilitas</h2>
                </div>
            </div>

            <div class="accessibility-control-row">
                <div>
                    <span class="accessibility-control-title">Kontras Tinggi</span>
                    <span class="accessibility-control-desc">Mode hitam-putih-kuning</span>
                </div>
                <button id="btn-contrast-toggle" class="accessibility-toggle-btn" type="button">Aktifkan</button>
            </div>

            <div class="accessibility-control-group">
                <div class="accessibility-control-row accessibility-control-row--stacked">
                    <div>
                        <span class="accessibility-control-title">Ukuran Teks</span>
                        <span class="accessibility-control-desc">Status: <strong id="text-size-label">Normal</strong></span>
                    </div>
                </div>
                <div class="accessibility-size-options" role="group" aria-label="Pilih ukuran teks">
                    <button onclick="changeTextSize('normal')" class="accessibility-size-btn" data-text-size="normal" type="button">A</button>
                    <button onclick="changeTextSize('large')" class="accessibility-size-btn accessibility-size-btn--large" data-text-size="large" type="button">A+</button>
                    <button onclick="changeTextSize('xlarge')" class="accessibility-size-btn accessibility-size-btn--xlarge" data-text-size="xlarge" type="button">A++</button>
                </div>
            </div>

            <div class="accessibility-control-row">
                <div>
                    <span class="accessibility-control-title">Suara Pendamping</span>
                    <span class="accessibility-control-desc">Membacakan elemen saat fokus</span>
                </div>
                <button id="btn-speech-toggle" class="accessibility-toggle-btn" type="button">Aktifkan</button>
            </div>

            <p class="accessibility-help-text">
                Aktifkan suara pendamping, lalu fokuskan tombol atau teks untuk mendengar panduan. Semua pilihan tersimpan otomatis di perangkat ini.
            </p>
        </div>
    </div>


    <script>
        // Dropdown toggle logic
        function toggleNotificationDropdown(e) {
            if (e) e.stopPropagation();
            const notif = document.getElementById('notification-dropdown');
            const avatar = document.getElementById('avatar-dropdown');
            const accDrop = document.getElementById('accessibility-dropdown');
            if (avatar) avatar.style.display = 'none';
            if (accDrop) accDrop.style.display = 'none';
            if (notif) {
                notif.style.display = notif.style.display === 'none' ? 'flex' : 'none';
            }
        }

        function toggleAvatarDropdown(e) {
            if (e) e.stopPropagation();
            const avatar = document.getElementById('avatar-dropdown');
            const notif = document.getElementById('notification-dropdown');
            const accDrop = document.getElementById('accessibility-dropdown');
            if (notif) notif.style.display = 'none';
            if (accDrop) accDrop.style.display = 'none';
            if (avatar) {
                avatar.style.display = avatar.style.display === 'none' ? 'flex' : 'none';
            }
        }

        function toggleAccessibilityDropdown(e) {
            if (e) e.stopPropagation();
            const accDrop = document.getElementById('accessibility-dropdown');
            const accTrigger = document.querySelector('.accessibility-nav-trigger');
            const notif = document.getElementById('notification-dropdown');
            const avatar = document.getElementById('avatar-dropdown');
            if (notif) notif.style.display = 'none';
            if (avatar) avatar.style.display = 'none';
            if (accDrop) {
                const willOpen = accDrop.style.display === 'none';
                accDrop.style.display = willOpen ? 'flex' : 'none';
                if (accTrigger) accTrigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            }
        }


        // Close dropdowns on clicking anywhere outside
        document.addEventListener('click', function(e) {
            const notif = document.getElementById('notification-dropdown');
            const avatar = document.getElementById('avatar-dropdown');
            const accDrop = document.getElementById('accessibility-dropdown');
            const notifBtn = e.target.closest('.nav-notification-wrapper');
            const avatarBtn = e.target.closest('.avatar-dropdown-wrapper');
            const accBtn = e.target.closest('.nav-accessibility-wrapper');
            
            if (!notifBtn && notif) {
                notif.style.display = 'none';
            }
            if (!avatarBtn && avatar) {
                avatar.style.display = 'none';
            }
            if (!accBtn && accDrop) {
                accDrop.style.display = 'none';
                const accTrigger = document.querySelector('.accessibility-nav-trigger');
                if (accTrigger) accTrigger.setAttribute('aria-expanded', 'false');
            }
        });

        const accToggleBtn = document.getElementById('accessibility-toggle-btn');
        const accMenu = document.getElementById('accessibility-menu');
        const btnContrast = document.getElementById('btn-contrast-toggle');
        const btnSpeech = document.getElementById('btn-speech-toggle');
        const textSizeLabel = document.getElementById('text-size-label');

        const navBtnContrast = document.getElementById('nav-btn-contrast');
        const navBtnSpeech = document.getElementById('nav-btn-speech');
        const navTextSizeLabel = document.getElementById('nav-text-size-lbl');

        let highContrastActive = localStorage.getItem('acc_contrast') === 'true';
        let textSize = localStorage.getItem('acc_text_size') || 'normal';
        let speechGuideActive = localStorage.getItem('acc_speech') === 'true';

        function updateAccessibilityButtonStates() {
            [btnContrast, navBtnContrast].forEach((button) => {
                if (!button) return;
                button.classList.toggle('is-active', highContrastActive);
                button.setAttribute('aria-pressed', highContrastActive ? 'true' : 'false');
                button.innerText = highContrastActive ? 'Matikan' : 'Aktifkan';
            });

            [btnSpeech, navBtnSpeech].forEach((button) => {
                if (!button) return;
                button.classList.toggle('is-active', speechGuideActive);
                button.setAttribute('aria-pressed', speechGuideActive ? 'true' : 'false');
                button.innerText = speechGuideActive ? 'Matikan' : 'Aktifkan';
            });

            document.querySelectorAll('.accessibility-size-btn').forEach((button) => {
                const isActive = button.dataset.textSize === textSize;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        }

        // Apply saved states on load
        if (highContrastActive) {
            document.body.classList.add('accessibility-high-contrast');
            if (btnContrast) {
                btnContrast.innerText = 'Matikan';
                btnContrast.style.borderColor = 'var(--accent-danger)';
            }
            if (navBtnContrast) {
                navBtnContrast.innerText = 'Matikan';
                navBtnContrast.style.borderColor = 'var(--accent-danger)';
            }
        }
        applyTextSize(textSize);
        if (speechGuideActive) {
            if (btnSpeech) {
                btnSpeech.innerText = 'Matikan';
                btnSpeech.style.borderColor = 'var(--accent-danger)';
            }
            if (navBtnSpeech) {
                navBtnSpeech.innerText = 'Matikan';
                navBtnSpeech.style.borderColor = 'var(--accent-danger)';
            }
        }
        updateAccessibilityButtonStates();

        if (accToggleBtn) {
            accToggleBtn.addEventListener('click', () => {
                const willOpen = accMenu.style.display === 'none';
                accMenu.style.display = willOpen ? 'flex' : 'none';
                accToggleBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                speakText("Menu opsi aksesibilitas dibuka.");
            });
        }

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (accToggleBtn && !document.getElementById('accessibility-widget').contains(e.target)) {
                accMenu.style.display = 'none';
                accToggleBtn.setAttribute('aria-expanded', 'false');
            }
        });

        // Toggle Contrast handler
        function toggleContrast() {
            highContrastActive = !highContrastActive;
            localStorage.setItem('acc_contrast', highContrastActive);
            if (highContrastActive) {
                document.body.classList.add('accessibility-high-contrast');
                if (btnContrast) {
                    btnContrast.innerText = 'Matikan';
                    btnContrast.style.borderColor = 'var(--accent-danger)';
                }
                if (navBtnContrast) {
                    navBtnContrast.innerText = 'Matikan';
                    navBtnContrast.style.borderColor = 'var(--accent-danger)';
                }
                updateAccessibilityButtonStates();
                speakText("Mode kontras tinggi diaktifkan.");
            } else {
                document.body.classList.remove('accessibility-high-contrast');
                if (btnContrast) {
                    btnContrast.innerText = 'Aktifkan';
                    btnContrast.style.borderColor = '';
                }
                if (navBtnContrast) {
                    navBtnContrast.innerText = 'Aktifkan';
                    navBtnContrast.style.borderColor = '';
                }
                updateAccessibilityButtonStates();
                speakText("Mode kontras tinggi dimatikan.");
            }
        }

        if (btnContrast) btnContrast.addEventListener('click', toggleContrast);
        if (navBtnContrast) navBtnContrast.addEventListener('click', toggleContrast);

        // Change Text Size
        function changeTextSize(size) {
            textSize = size;
            localStorage.setItem('acc_text_size', size);
            applyTextSize(size);
            speakText("Ukuran teks diubah ke " + (size === 'normal' ? 'Normal' : size === 'large' ? 'Besar' : 'Sangat Besar'));
        }

        function applyTextSize(size) {
            document.body.classList.remove('accessibility-large-text', 'accessibility-xlarge-text');
            const labelText = size === 'normal' ? 'Normal' : size === 'large' ? 'Besar' : 'Sangat Besar';
            if (size === 'large') {
                document.body.classList.add('accessibility-large-text');
            } else if (size === 'xlarge') {
                document.body.classList.add('accessibility-xlarge-text');
            }
            if (textSizeLabel) textSizeLabel.innerText = labelText;
            if (navTextSizeLabel) navTextSizeLabel.innerText = labelText;
            updateAccessibilityButtonStates();
        }

        // Toggle Speech Guide handler
        function toggleSpeechGuide() {
            speechGuideActive = !speechGuideActive;
            localStorage.setItem('acc_speech', speechGuideActive);
            if (speechGuideActive) {
                if (btnSpeech) {
                    btnSpeech.innerText = 'Matikan';
                    btnSpeech.style.borderColor = 'var(--accent-danger)';
                }
                if (navBtnSpeech) {
                    navBtnSpeech.innerText = 'Matikan';
                    navBtnSpeech.style.borderColor = 'var(--accent-danger)';
                }
                updateAccessibilityButtonStates();
                speakText("Suara pendamping diaktifkan.");
            } else {
                speakText("Suara pendamping dimatikan.");
                if (btnSpeech) {
                    btnSpeech.innerText = 'Aktifkan';
                    btnSpeech.style.borderColor = '';
                }
                if (navBtnSpeech) {
                    navBtnSpeech.innerText = 'Aktifkan';
                    navBtnSpeech.style.borderColor = '';
                }
                updateAccessibilityButtonStates();
            }
        }

        if (btnSpeech) btnSpeech.addEventListener('click', toggleSpeechGuide);
        if (navBtnSpeech) navBtnSpeech.addEventListener('click', toggleSpeechGuide);

        // Speech Helper
        function speakText(text) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const utter = new SpeechSynthesisUtterance(text);
                utter.lang = 'id-ID';
                window.speechSynthesis.speak(utter);
            }
        }

        // Nav-btn ripple effect — tracks mouse position for radial gradient
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('mousemove', (e) => {
                const rect = btn.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;
                btn.style.setProperty('--x', x + '%');
                btn.style.setProperty('--y', y + '%');
            });
        });

        // Hover & Focus speech listener
        document.addEventListener('mouseover', (e) => {
            if (!speechGuideActive) return;
            handleAccessibilitySpeech(e.target);
        });

        document.addEventListener('focusin', (e) => {
            if (!speechGuideActive) return;
            handleAccessibilitySpeech(e.target);
        });

        function handleAccessibilitySpeech(target) {
            // Find closest readable element
            const element = target.closest('a, button, input, select, textarea, h1, h2, h3, h4, h5, h6, .brand, p, .card-title');
            if (element && !element.classList.contains('no-speech') && !document.getElementById('accessibility-widget').contains(element)) {
                // If it's already read, don't read it again immediately
                if (window.lastSpokenElement === element) return;
                window.lastSpokenElement = element;

                let speechText = "";
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    const label = document.querySelector(`label[for="${element.id}"]`);
                    speechText = "Kolom masukan " + (label ? label.innerText : "") + ". " + (element.placeholder || "");
                } else if (element.tagName === 'SELECT') {
                    const label = document.querySelector(`label[for="${element.id}"]`);
                    speechText = "Pilihan " + (label ? label.innerText : "");
                } else {
                    speechText = element.innerText || element.getAttribute('title') || element.getAttribute('alt') || "";
                }

                if (speechText.trim()) {
                    speakText(speechText);
                }
            }
        }
    </script>

    <div id="mini-audio-player">
        <div class="mini-player-details">
            <div id="mini-player-cover-area" style="width: 40px; height: 40px; border-radius: 6px; overflow: hidden; flex-shrink: 0; background: #1e1b4b; border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17"/></svg>
            </div>
            <div style="overflow: hidden; display: flex; flex-direction: column; justify-content: center;">
                <h5 class="mini-player-title" id="mini-book-title" style="margin: 0;">Judul Buku Audio</h5>
                <p class="mini-player-author" id="mini-book-author" style="display: none;">Penulis Buku</p>
            </div>
        </div>

        <div id="mini-audio-status-badge" style="font-size: 0.7rem; font-weight: bold; background: rgba(99,102,241,0.2); color: var(--accent-primary); border: 1px solid var(--accent-primary); padding: 2px 8px; border-radius: 12px; white-space: nowrap;">
            Siap
        </div>

        <div class="mini-player-controls">
            <div class="mini-wave" id="mini-wave-anim" style="margin-right: 0.2rem;">
                <div class="mini-wave-bar"></div>
                <div class="mini-wave-bar"></div>
                <div class="mini-wave-bar"></div>
                <div class="mini-wave-bar"></div>
            </div>

            <button class="mini-player-btn" onclick="prevMiniSentence()" title="Kalimat Sebelumnya">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="19 20 9 12 19 4 19 20"/><line x1="5" y1="19" x2="5" y2="5"/></svg>
            </button>
            <button class="mini-player-btn play-btn" id="mini-btn-play" onclick="toggleMiniPlay()" title="Putar">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            </button>
            <button class="mini-player-btn" onclick="nextMiniSentence()" title="Kalimat Berikutnya">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19"/></svg>
            </button>
            <button class="mini-player-btn" onclick="closeMiniPlayer()" title="Tutup Pemutar" style="margin-left: 0.4rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent-danger)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
    </div>

    <script>
        // Mini Audio Player Logic
        let miniChunks = [];
        let miniCurrentIndex = 0;
        let miniSpeaking = false;
        let miniPaused = false;
        let miniUtterance = null;

        const miniPlayer = document.getElementById('mini-audio-player');
        const miniBookTitle = document.getElementById('mini-book-title');
        const miniBookAuthor = document.getElementById('mini-book-author');
        const miniPlayBtn = document.getElementById('mini-btn-play');
        const miniStatusBadge = document.getElementById('mini-audio-status-badge');
        const miniCoverArea = document.getElementById('mini-player-cover-area');

        // Global function to trigger play from other pages
        window.playMiniPlayer = function(title, author, description, coverUrl) {
            // Cancel any main page speech synthesis
            if (typeof stopTTS === 'function') {
                stopTTS();
            }
            window.speechSynthesis.cancel();

            miniBookTitle.innerText = title;
            miniBookAuthor.innerText = author || "Penulis Tidak Diketahui";
            
            // Set cover thumbnail
            if (coverUrl) {
                miniCoverArea.innerHTML = `<img src="${coverUrl}" style="width: 100%; height: 100%; object-fit: cover;">`;
            } else {
                const initials = title.substring(0, 2).toUpperCase();
                miniCoverArea.innerHTML = `<span style="font-weight: 700; color: #a5b4fc; font-size: 0.75rem;">${initials}</span>`;
            }

            // Chunk description
            miniChunks = getSpeechChunksList(title, author, description);
            miniCurrentIndex = 0;
            miniSpeaking = true;
            miniPaused = false;

            miniPlayer.classList.add('active');
            
            speakNextMini();
        };

        function getSpeechChunksList(title, author, description) {
            const list = [`Memutar buku ${title}.`];
            if (description) {
                const paragraphs = description.split(/\r?\n/);
                for (const para of paragraphs) {
                    const trimmed = para.trim();
                    if (trimmed) {
                        const sentences = trimmed.split(/(?<=[.!?])\s+/);
                        for (const sentence of sentences) {
                            if (sentence.trim()) {
                                list.push(sentence.trim());
                            }
                        }
                    }
                }
            }
            return list;
        }

        function speakNextMini() {
            if (!miniSpeaking) return;

            if (miniCurrentIndex >= miniChunks.length) {
                miniSpeaking = false;
                miniCurrentIndex = 0;
                updateMiniPlayerUI();
                return;
            }

            const text = miniChunks[miniCurrentIndex];
            
            miniUtterance = new SpeechSynthesisUtterance(text);
            miniUtterance.lang = 'id-ID';

            const voices = window.speechSynthesis.getVoices();
            const idVoice = voices.find(voice => voice.lang.includes('id') || voice.lang.includes('ID'));
            if (idVoice) {
                miniUtterance.voice = idVoice;
            }

            miniUtterance.onstart = function() {
                updateMiniPlayerUI();
            };

            miniUtterance.onend = function() {
                if (miniSpeaking && !miniPaused) {
                    miniCurrentIndex++;
                    speakNextMini();
                }
            };

            miniUtterance.onerror = function() {
                if (miniSpeaking && !miniPaused) {
                    miniCurrentIndex++;
                    speakNextMini();
                }
            };

            window.speechSynthesis.speak(miniUtterance);
        }

        function toggleMiniPlay() {
            if (!miniSpeaking) return;

            if (miniPaused) {
                window.speechSynthesis.resume();
                miniPaused = false;
                
                setTimeout(() => {
                    if (window.speechSynthesis.paused) {
                        window.speechSynthesis.cancel();
                        speakNextMini();
                    }
                }, 150);
            } else {
                window.speechSynthesis.pause();
                miniPaused = true;
            }
            updateMiniPlayerUI();
        }

        function prevMiniSentence() {
            if (miniChunks.length === 0) return;
            window.speechSynthesis.cancel();
            if (miniCurrentIndex > 0) {
                miniCurrentIndex--;
            }
            miniSpeaking = true;
            miniPaused = false;
            speakNextMini();
        }

        function nextMiniSentence() {
            if (miniChunks.length === 0) return;
            window.speechSynthesis.cancel();
            if (miniCurrentIndex < miniChunks.length - 1) {
                miniCurrentIndex++;
            }
            miniSpeaking = true;
            miniPaused = false;
            speakNextMini();
        }

        function updateMiniPlayerUI() {
            const playSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>';
            const pauseSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>';
            if (miniSpeaking) {
                miniPlayer.classList.add('playing');
                if (miniPaused) {
                    miniPlayer.classList.remove('playing');
                    miniPlayBtn.innerHTML = playSvg;
                    miniPlayBtn.title = 'Lanjutkan';
                    miniStatusBadge.innerText = 'Jeda';
                    miniStatusBadge.style.color = 'var(--text-muted)';
                    miniStatusBadge.style.borderColor = 'var(--border-glass)';
                } else {
                    miniPlayBtn.innerHTML = pauseSvg;
                    miniPlayBtn.title = 'Jeda';
                    miniStatusBadge.innerText = `Membaca (${miniCurrentIndex + 1}/${miniChunks.length})`;
                    miniStatusBadge.style.color = 'var(--accent-success)';
                    miniStatusBadge.style.borderColor = 'var(--accent-success)';
                }
            } else {
                miniPlayer.classList.remove('playing');
                miniPlayBtn.innerHTML = playSvg;
                miniStatusBadge.innerText = 'Selesai';
                miniStatusBadge.style.color = 'var(--text-secondary)';
                miniStatusBadge.style.borderColor = 'var(--border-glass)';
            }
        }

        function closeMiniPlayer() {
            miniSpeaking = false;
            miniPaused = false;
            window.speechSynthesis.cancel();
            miniPlayer.classList.remove('active');
            miniPlayer.classList.remove('playing');
            miniChunks = [];
        }

        // ─── Session timeout warning ─────────────────────────
        @if (session('auth_role'))
        (function() {
            const SESSION_LIFETIME = {{ config('session.lifetime', 120) }} * 60 * 1000;
            const WARNING_TIME = 5 * 60 * 1000;
            let timeoutId = null;
            let warningId = null;

            function resetTimers() {
                if (timeoutId) clearTimeout(timeoutId);
                if (warningId) clearTimeout(warningId);

                warningId = setTimeout(() => {
                    const toast = document.createElement('div');
                    toast.id = 'session-toast';
                    toast.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);z-index:999999;background:#1e1b4b;border:1px solid #6366f1;color:#fff;padding:12px 20px;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,0.5);display:flex;align-items:center;gap:12px;font-size:0.85rem;max-width:90%;width:400px;';
                    toast.innerHTML = '<span>Sesi Anda akan segera berakhir karena tidak ada aktivitas.</span><button onclick="window.location.href=\'/login\'" style="background:#6366f1;border:none;color:#fff;padding:6px 14px;border-radius:8px;cursor:pointer;font-weight:600;white-space:nowrap;">Login Ulang</button>';
                    document.body.appendChild(toast);
                }, SESSION_LIFETIME - WARNING_TIME);

                timeoutId = setTimeout(() => {
                    window.location.href = '/login';
                }, SESSION_LIFETIME);
            }

            ['click', 'keydown', 'scroll', 'mousemove', 'touchstart'].forEach(ev => {
                document.addEventListener(ev, () => {
                    const toast = document.getElementById('session-toast');
                    if (toast) toast.remove();
                    resetTimers();
                });
            });

            resetTimers();
        })();
        @endif

        // ─── PWA Service Worker ─────────────────────────────
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
</body>
</html>
