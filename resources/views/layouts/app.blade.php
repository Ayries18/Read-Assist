<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Read Assist' }}</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <link rel="stylesheet" href="/css/app.css?v={{ time() }}">
    <style>
        /* Navbar Enhancements */
        .nav-icon-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.4rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            position: relative;
        }
        .nav-icon-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
            transform: scale(1.05);
        }
        .notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 8px;
            height: 8px;
            background-color: var(--accent-danger);
            border-radius: 50%;
            box-shadow: 0 0 6px var(--accent-danger);
            animation: pulse-badge 2s infinite;
        }
        @keyframes pulse-badge {
            0% { transform: scale(0.9); opacity: 0.6; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.6; }
        }
        
        /* Dropdown Styles */
        .nav-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: -10px;
            background: rgba(30, 27, 75, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            padding: 0.8rem;
            width: 250px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            display: none;
            flex-direction: column;
            gap: 0.6rem;
            z-index: 1000;
            animation: slide-down 0.2s ease;
        }
        @keyframes slide-down {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dropdown-header {
            font-size: 0.75rem;
            font-weight: bold;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 0.4rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .dropdown-item {
            display: flex;
            gap: 0.6rem;
            font-size: 0.78rem;
            line-height: 1.4;
            color: var(--text-secondary);
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            transition: background 0.2s ease;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
        }
        .dropdown-item:hover {
            background: rgba(99, 102, 241, 0.15);
            color: #fff;
        }
        .dropdown-item .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent-primary);
            margin-top: 4px;
            flex-shrink: 0;
        }
        .dropdown-item .dot.active {
            box-shadow: 0 0 6px var(--accent-primary);
        }
        .dropdown-text {
            margin: 0;
            color: var(--text-secondary);
        }
        .dropdown-time {
            font-size: 0.68rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
            display: block;
        }
        
        /* Avatar & Link style */
        .dropdown-item-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.82rem;
            padding: 0.5rem 0.8rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }
        .dropdown-item-link:hover {
            background: rgba(99, 102, 241, 0.15);
            color: #fff;
            padding-left: 1rem;
        }
        .dropdown-logout-btn:hover {
            background: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
        }

        /* Glow Active State */
        .nav-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
        }
        .nav-link.active {
            color: #fff !important;
            text-shadow: 0 0 10px rgba(99, 102, 241, 0.6), 0 0 20px rgba(99, 102, 241, 0.3);
        }
        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 10%;
            width: 80%;
            height: 2px;
            background: var(--accent-primary);
            box-shadow: 0 0 8px var(--accent-primary), 0 0 15px var(--accent-primary);
            border-radius: 2px;
        }
        
        .avatar-btn:hover {
            opacity: 0.9;
        }
        
        /* Search Input */
        .nav-search-form input:focus {
            background: rgba(255,255,255,0.12) !important;
            border-color: var(--accent-primary) !important;
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.4) !important;
        }

        /* Pulse green for system status */
        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
            50% { transform: scale(1.15); box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* Glassmorphism navbar extra glow */
        .navbar {
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255,255,255,0.05) !important;
        }

        /* Micro hover animation for links */
        .nav-menu li a {
            transition: transform 0.2s ease, color 0.3s ease;
        }
        .nav-menu li a:hover {
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    @if (!session()->has('qr_restricted_token') || session()->has('auth_role'))
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/" class="brand" style="display: flex; align-items: center; gap: 0.8rem; text-decoration: none;">
                <img src="/logo.png" alt="Read-Assist Logo" style="height: 40px; width: 40px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--accent-primary); transition: transform 0.3s ease;">
                <span style="font-weight: 700; font-size: 1.3rem; color: #fff; letter-spacing: -0.5px;">
                    <span class="brand-accent">Read</span>-Assist
                </span>
            </a>
            
            <ul class="nav-menu">
                <li>
                    <a href="/" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                        <span>🏠</span> Beranda
                    </a>
                </li>
                <li>
                    <a href="/katalog-audio" class="nav-link {{ request()->is('katalog-audio') ? 'active' : '' }}">
                        <span>📚</span> Katalog Buku
                    </a>
                </li>
                @if (session('auth_role') === 'admin')
                    <li>
                        <a href="/katalog-audio/tambah" class="nav-link {{ request()->is('katalog-audio/tambah') ? 'active' : '' }}">
                            <span>📥</span> Tambah Buku
                        </a>
                    </li>
                    <li>
                        <a href="/admin/dashboard" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                            <span>🖥️</span> Dashboard Admin
                        </a>
                    </li>
                @elseif (session('auth_role') === 'user')
                    <li>
                        <a href="/user/tambah-buku" class="nav-link {{ request()->is('user/tambah-buku') ? 'active' : '' }}">
                            <span>📥</span> Tambah Buku Saya
                        </a>
                    </li>
                    <li>
                        <a href="/user/dashboard" class="nav-link {{ request()->is('user/dashboard') ? 'active' : '' }}">
                            <span>👤</span> Dashboard User
                        </a>
                    </li>
                @endif
            </ul>

            <div class="nav-user" style="display: flex; align-items: center; gap: 1rem;">
                <!-- ♿ Accessibility Dropdown -->
                <div class="nav-accessibility-wrapper" style="position: relative;">
                    <button class="nav-icon-btn" onclick="toggleAccessibilityDropdown(event)" title="Opsi Aksesibilitas" style="font-size: 1.2rem;">
                        ♿
                    </button>
                    <div id="accessibility-dropdown" class="nav-dropdown" style="display: none; right: -80px; width: 260px; padding: 1rem;">
                        <div class="dropdown-header" style="border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 0.5rem; margin-bottom: 0.6rem;">
                            ♿ Aksesibilitas
                        </div>
                        <!-- Contrast -->
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; margin-bottom: 0.6rem; color: var(--text-secondary);">
                            <span>🌓 Kontras Tinggi</span>
                            <button id="nav-btn-contrast" class="btn btn-secondary btn-inline" style="padding: 0.2rem 0.5rem; font-size: 0.72rem;">Aktifkan</button>
                        </div>
                        <!-- Text Size -->
                        <div style="display: flex; flex-direction: column; gap: 0.3rem; font-size: 0.8rem; margin-bottom: 0.6rem; color: var(--text-secondary);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>🔎 Ukuran Teks</span>
                                <span id="nav-text-size-lbl" style="font-size: 0.72rem; color: var(--text-muted); font-weight: bold;">Normal</span>
                            </div>
                            <div style="display: flex; gap: 0.25rem;">
                                <button onclick="changeTextSize('normal')" class="btn btn-secondary btn-inline" style="flex: 1; padding: 0.2rem; font-size: 0.72rem;">A</button>
                                <button onclick="changeTextSize('large')" class="btn btn-secondary btn-inline" style="flex: 1; padding: 0.2rem; font-size: 0.78rem; font-weight: bold;">A+</button>
                                <button onclick="changeTextSize('xlarge')" class="btn btn-secondary btn-inline" style="flex: 1; padding: 0.2rem; font-size: 0.85rem; font-weight: 900;">A++</button>
                            </div>
                        </div>
                        <!-- Speech Guide -->
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: var(--text-secondary);">
                            <span>🗣️ Suara Pendamping</span>
                            <button id="nav-btn-speech" class="btn btn-secondary btn-inline" style="padding: 0.2rem 0.5rem; font-size: 0.72rem;">Aktifkan</button>
                        </div>
                    </div>
                </div>


                @if (session('auth_role'))
                    <!-- 🔔 Notification Dropdown -->
                    <div class="nav-notification-wrapper" style="position: relative;">
                        <button class="nav-icon-btn" onclick="toggleNotificationDropdown(event)" title="Notifikasi" style="font-size: 1.15rem;">
                            🔔
                            <span class="notification-badge"></span>
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

                    <!-- 👤 Avatar Dropdown -->
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
                                <a class="dropdown-item-link" href="/admin/dashboard">🖥️ Dashboard Admin</a>
                            @elseif (session('auth_role') === 'user')
                                <a class="dropdown-item-link" href="/user/dashboard">👤 Dashboard User</a>
                            @endif
                            <a class="dropdown-item-link" href="/katalog-audio">📚 Katalog Buku</a>
                            
                            <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.08); margin: 0.4rem 0;">
                            
                            <form method="POST" action="/logout" style="margin: 0;">
                                @csrf
                                <button type="submit" class="dropdown-item-link dropdown-logout-btn" style="width: 100%; text-align: left; background: none; border: none; color: var(--accent-danger); cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                                    🚪 Keluar (Logout)
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="/login" class="nav-link {{ request()->is('login') ? 'active' : '' }}">Login</a>
                    <a href="/register" class="btn btn-primary btn-inline" style="padding: 0.5rem 1.2rem; font-size: 0.9rem; border-radius: 6px;">
                        Daftar
                    </a>
                @endif
            </div>
        </div>
    </nav>
    @endif

    <main class="container">
        @if (session('success'))
            <div class="alert alert-success">
                <span>✓</span> {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('audio'))
            <div class="alert alert-danger">
                <span>✗</span> {{ $errors->first('audio') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer style="text-align: center; padding: 2rem; margin-top: 3rem; border-top: 1px solid var(--border-glass); background: rgba(0,0,0,0.2);">
        <p style="margin: 0; font-size: 0.9rem;">&copy; {{ date('Y') }} Read Assist. Sistem Pendukung Belajar Mandiri untuk Tunanetra.</p>
    </footer>

    <!-- ♿ Floating Accessibility Panel -->
    <div id="accessibility-widget" style="position: fixed; bottom: 20px; left: 20px; z-index: 9999;">
        <button id="accessibility-toggle-btn" class="btn btn-secondary" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.3); border: 2px solid var(--accent-primary); background: var(--bg-glass); cursor: pointer;" title="Menu Aksesibilitas">
            ♿
        </button>
        <div id="accessibility-menu" style="display: none; position: absolute; bottom: 60px; left: 0; background: var(--bg-secondary); border: 2px solid var(--border-glass); border-radius: 12px; padding: 1rem; width: 260px; box-shadow: 0 8px 30px rgba(0,0,0,0.5); flex-direction: column; gap: 0.8rem;">
            <h4 style="margin: 0 0 0.5rem 0; font-size: 0.95rem; font-weight: 700; border-bottom: 1px solid var(--border-glass); padding-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                <span>⚙️ Opsi Aksesibilitas</span>
                <span style="font-size: 0.75rem; color: var(--accent-secondary); font-weight: normal;">Read-Assist</span>
            </h4>
            
            <!-- Contrast Option -->
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem;">
                <span>🌓 Kontras Tinggi</span>
                <button id="btn-contrast-toggle" class="btn btn-secondary btn-inline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">Aktifkan</button>
            </div>
            
            <!-- Font Size Option -->
            <div style="display: flex; flex-direction: column; gap: 0.3rem; font-size: 0.85rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>🔎 Ukuran Teks</span>
                    <span id="text-size-label" style="font-weight: bold; font-size: 0.75rem; color: var(--text-muted);">Normal</span>
                </div>
                <div style="display: flex; gap: 0.25rem;">
                    <button onclick="changeTextSize('normal')" class="btn btn-secondary btn-inline" style="flex: 1; padding: 0.3rem; font-size: 0.75rem;">A</button>
                    <button onclick="changeTextSize('large')" class="btn btn-secondary btn-inline" style="flex: 1; padding: 0.3rem; font-size: 0.85rem; font-weight: bold;">A+</button>
                    <button onclick="changeTextSize('xlarge')" class="btn btn-secondary btn-inline" style="flex: 1; padding: 0.3rem; font-size: 0.95rem; font-weight: 900;">A++</button>
                </div>
            </div>

            <!-- Speech Feedback Option -->
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem;">
                <span>🗣️ Suara Pendamping</span>
                <button id="btn-speech-toggle" class="btn btn-secondary btn-inline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">Aktifkan</button>
            </div>
            
            <p style="font-size: 0.7rem; color: var(--text-muted); margin: 0; line-height: 1.4; border-top: 1px solid var(--border-glass); padding-top: 0.5rem;">
                * Arahkan kursor atau fokus pada tombol/teks untuk mendengarkan panduan suara saat Suara Pendamping aktif.
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
            const notif = document.getElementById('notification-dropdown');
            const avatar = document.getElementById('avatar-dropdown');
            if (notif) notif.style.display = 'none';
            if (avatar) avatar.style.display = 'none';
            if (accDrop) {
                accDrop.style.display = accDrop.style.display === 'none' ? 'flex' : 'none';
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

        if (accToggleBtn) {
            accToggleBtn.addEventListener('click', () => {
                accMenu.style.display = accMenu.style.display === 'none' ? 'flex' : 'none';
                speakText("Menu opsi aksesibilitas dibuka.");
            });
        }

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (accToggleBtn && !document.getElementById('accessibility-widget').contains(e.target)) {
                accMenu.style.display = 'none';
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

    <!-- 🔊 Bottom Floating Mini Audio Player -->
    <div id="mini-audio-player">
        <div class="mini-player-details">
            <!-- Small Cover -->
            <div id="mini-player-cover-area" style="width: 40px; height: 40px; border-radius: 6px; overflow: hidden; flex-shrink: 0; background: #1e1b4b; border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                🖼️
            </div>
            <div style="overflow: hidden; display: flex; flex-direction: column; justify-content: center;">
                <h5 class="mini-player-title" id="mini-book-title" style="margin: 0;">Judul Buku Audio</h5>
                <p class="mini-player-author" id="mini-book-author" style="display: none;">Penulis Buku</p>
            </div>
        </div>

        <!-- Audio Status Badge -->
        <div id="mini-audio-status-badge" style="font-size: 0.7rem; font-weight: bold; background: rgba(99,102,241,0.2); color: var(--accent-primary); border: 1px solid var(--accent-primary); padding: 2px 8px; border-radius: 12px; white-space: nowrap;">
            Siap
        </div>

        <div class="mini-player-controls">
            <!-- Visual Sound Wave -->
            <div class="mini-wave" id="mini-wave-anim" style="margin-right: 0.2rem;">
                <div class="mini-wave-bar"></div>
                <div class="mini-wave-bar"></div>
                <div class="mini-wave-bar"></div>
                <div class="mini-wave-bar"></div>
            </div>

            <button class="mini-player-btn" onclick="prevMiniSentence()" title="Kalimat Sebelumnya">⏮️</button>
            <button class="mini-player-btn play-btn" id="mini-btn-play" onclick="toggleMiniPlay()" title="Putar">▶️</button>
            <button class="mini-player-btn" onclick="nextMiniSentence()" title="Kalimat Berikutnya">⏭️</button>
            <button class="mini-player-btn" onclick="closeMiniPlayer()" title="Tutup Pemutar" style="color: var(--accent-danger); font-weight: bold; margin-left: 0.4rem;">✕</button>
        </div>
    </div>

    <script>
        // 🔊 Bottom Floating Mini Audio Player Logic
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
            if (miniSpeaking) {
                miniPlayer.classList.add('playing');
                if (miniPaused) {
                    miniPlayer.classList.remove('playing');
                    miniPlayBtn.innerText = '▶️';
                    miniPlayBtn.title = 'Lanjutkan';
                    miniStatusBadge.innerText = 'Jeda';
                    miniStatusBadge.style.color = 'var(--text-muted)';
                    miniStatusBadge.style.borderColor = 'var(--border-glass)';
                } else {
                    miniPlayBtn.innerText = '⏸️';
                    miniPlayBtn.title = 'Jeda';
                    miniStatusBadge.innerText = `Membaca (${miniCurrentIndex + 1}/${miniChunks.length})`;
                    miniStatusBadge.style.color = 'var(--accent-success)';
                    miniStatusBadge.style.borderColor = 'var(--accent-success)';
                }
            } else {
                miniPlayer.classList.remove('playing');
                miniPlayBtn.innerText = '▶️';
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
    </script>
</body>
</html>
