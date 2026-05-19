<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Read Assist' }}</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <link rel="stylesheet" href="/css/app.css?v={{ time() }}">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/katalog-audio" class="brand" style="display: flex; align-items: center; gap: 0.8rem; text-decoration: none;">
                <img src="/logo.png" alt="Read-Assist Logo" style="height: 40px; width: 40px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--accent-primary); transition: transform 0.3s ease;">
                <span style="font-weight: 700; font-size: 1.3rem; color: #fff; letter-spacing: -0.5px;">
                    <span class="brand-accent">Read</span>-Assist
                </span>
            </a>
            
            <ul class="nav-menu">
                <li>
                    <a href="/katalog-audio" class="nav-link {{ request()->is('katalog-audio') ? 'active' : '' }}">
                        Katalog Buku
                    </a>
                </li>
                @if (session('auth_role') === 'admin')
                    <li>
                        <a href="/katalog-audio/tambah" class="nav-link {{ request()->is('katalog-audio/tambah') ? 'active' : '' }}">
                            Tambah Buku
                        </a>
                    </li>
                    <li>
                        <a href="/admin/dashboard" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                            Dashboard Admin
                        </a>
                    </li>
                @elseif (session('auth_role') === 'user')
                    <li>
                        <a href="/user/tambah-buku" class="nav-link {{ request()->is('user/tambah-buku') ? 'active' : '' }}">
                            Tambah Buku Saya
                        </a>
                    </li>
                    <li>
                        <a href="/user/dashboard" class="nav-link {{ request()->is('user/dashboard') ? 'active' : '' }}">
                            Dashboard User
                        </a>
                    </li>
                @endif
            </ul>

            <div class="nav-user">
                @if (session('auth_role'))
                    <div class="user-info">
                        <span class="user-name">{{ session('auth_name') }}</span>
                        <span class="user-role">{{ session('auth_role') }}</span>
                    </div>
                    <form method="POST" action="/logout" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-inline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="/login" class="nav-link {{ request()->is('login') ? 'active' : '' }}">Login</a>
                    <a href="/register" class="btn btn-primary btn-inline" style="padding: 0.5rem 1.2rem; font-size: 0.9rem; border-radius: 6px;">
                        Daftar
                    </a>
                @endif
            </div>
        </div>
    </nav>

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

    <footer>
        <p>&copy; {{ date('Y') }} Read Assist. Sistem Pendukung Belajar Mandiri untuk Tunanetra.</p>
    </footer>
</body>
</html>
