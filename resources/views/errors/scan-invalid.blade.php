<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>QR Tidak Valid - Read Assist</title>
    <meta name="theme-color" content="#081028">
    @php
        $hasBuild = file_exists(public_path('build/manifest.json'));
        $hasHot = file_exists(public_path('hot'));
        if ($hasBuild) {
            $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        }
        $isMobile = (bool) preg_match('/(android|iphone|ipad|mobile|phone)/i', request()->header('User-Agent', ''));
        $isLocalHost = in_array(request()->getHost(), ['localhost', '127.0.0.1', '::1'], true);
        $useBuild = $hasBuild && (!$hasHot || !$isLocalHost || $isMobile);
    @endphp
    @if ($useBuild)
        <link rel="stylesheet" href="{{ asset('build/' . ($manifest['resources/css/app.css']['file'] ?? 'assets/app-B9pJSmzU.css')) }}">
    @else
        @vite(['resources/css/app.css'])
    @endif
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background: #0f1123;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e2e8f0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            width: 100%;
            max-width: 380px;
            padding: 32px 24px;
            text-align: center;
        }
        .icon-box {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .icon-box svg {
            width: 36px;
            height: 36px;
            color: #ef4444;
        }
        h1 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #fff;
        }
        p {
            font-size: 0.9rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            background: linear-gradient(135deg, #7c3aed, #6366f1);
            color: #fff;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(124, 58, 237, 0.3);
        }
        .btn:active { transform: scale(0.97); }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            background: rgba(255,255,255,0.05);
            color: #94a3b8;
            border: 1px solid rgba(255,255,255,0.08);
            margin-top: 12px;
        }
        .btn-secondary:active { transform: scale(0.97); }
        .footer { margin-top: 32px; font-size: 0.7rem; color: #475569; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <h1>Buku Tidak Ditemukan</h1>
        <p>QR Code yang Anda pindai tidak terdaftar atau buku telah dihapus. Silakan hubungi administrator perpustakaan.</p>
        <a href="{{ url('/') }}" class="btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5m7-7-7 7 7 7"/></svg>
            Kembali ke Beranda
        </a>
        <a href="{{ url('/katalog-audio') }}" class="btn-secondary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/></svg>
            Lihat Katalog Buku
        </a>
        <div class="footer">Read-Assist &mdash; Buku Audio untuk Tunanetra</div>
    </div>
</body>
</html>