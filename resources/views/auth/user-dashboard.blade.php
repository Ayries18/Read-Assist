@extends('layouts.app')

@section('content')
@php
    $host = request()->getSchemeAndHttpHost();
    if (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) {
        $detectedIp = \App\Http\Controllers\AudioBukuController::getDetectedIp();
        $port = request()->getPort();
        $host = 'http://' . $detectedIp . ($port ? ':' . $port : ':8000');
    }
    $serverOrigin = rtrim($host, '/');
@endphp

    <div style="max-width: 800px; margin: 0 auto;">
        <!-- Welcome Card -->
        <div class="card border shadow-sm" style="padding: 2.5rem; margin-bottom: 2rem; background: #ffffff; border-color: rgba(0, 0, 0, 0.12);">
            <div class="card-body p-0">
                <span class="badge badge-outline badge-primary mb-4">
                    Member Area
                </span>
                <h1 class="text-gradient text-4xl font-extrabold leading-tight mb-3">
                    Selamat Datang, {{ session('auth_name') }}!
                </h1>
                <p class="text-slate-600 max-w-lg" style="font-size: 1.05rem; line-height: 1.6;">
                    Anda masuk sebagai pengguna biasa. Anda dapat menelusuri katalog buku audio untuk didengarkan, serta menambahkan buku baru yang Anda miliki ke dalam sistem.
                </p>
            </div>
        </div>

        <!-- Statistik Dashboard -->
        <h3 class="text-xl font-bold mb-5 flex items-center gap-2">
            Statistik Dashboard
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1.2rem; margin-bottom: 2.5rem;">
            <!-- Stat 1 -->
            <div class="card bg-white border border-black/10 shadow-md text-center">
                <div class="card-body">
                    <span class="block mb-1 text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17"/></svg>
                    </span>
                    <span class="text-xs uppercase text-slate-600 font-semibold tracking-wider">Buku Audio</span>
                    <h2 class="text-3xl font-extrabold mt-1 text-blue-400">
                        {{ $stats['total_books'] }}
                    </h2>
                </div>
            </div>
            <!-- Stat 2 -->
            <div class="card bg-white border border-black/10 shadow-md text-center">
                <div class="card-body">
                    <span class="block mb-1 text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    </span>
                    <span class="text-xs uppercase text-slate-600 font-semibold tracking-wider">Unggahan Saya</span>
                    <h2 class="text-3xl font-extrabold mt-1 text-indigo-400">
                        {{ $stats['my_uploads'] }}
                    </h2>
                </div>
            </div>
            <!-- Stat 3 -->
            <div class="card bg-white border border-black/10 shadow-md text-center">
                <div class="card-body">
                    <span class="block mb-1 text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                    </span>
                    <span class="text-xs uppercase text-slate-600 font-semibold tracking-wider">Audio Siap (Milik Saya)</span>
                    <h2 class="text-3xl font-extrabold mt-1 text-emerald-400">
                        {{ $stats['my_uploads_completed'] }}
                    </h2>
                </div>
            </div>
            <!-- Stat 4 -->
            <div class="card bg-white border border-black/10 shadow-md text-center">
                <div class="card-body">
                    <span class="block mb-1 text-purple-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                    </span>
                    <span class="text-xs uppercase text-slate-600 font-semibold tracking-wider">Buku Didengarkan</span>
                    <h2 class="text-3xl font-extrabold mt-1 text-purple-400">
                        {{ $stats['listened_books'] }}
                    </h2>
                </div>
            </div>
        </div>

        <!-- Listening Ringkasan -->
        <div class="card bg-white border border-black/10 shadow-md mb-2" style="padding: 1rem 1.5rem;">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                <span class="text-slate-600 font-semibold uppercase tracking-wider text-xs">Ringkasan Mendengar:</span>
                <span class="text-slate-600"><strong class="text-purple-400">{{ $stats['listened_books'] }}</strong> buku pernah dibuka</span>
                <span class="text-slate-600"><strong class="text-emerald-400">{{ $stats['listened_completed'] }}</strong> selesai didengarkan</span>
            </div>
        </div>

        <!-- Accessibility Guide & QR Quick Actions -->
        <style>
            .grid-innovative {
                display: grid; 
                grid-template-columns: 1.25fr 0.75fr; 
                gap: 1.5rem; 
                margin-bottom: 2.5rem;
            }
            @media (max-width: 768px) {
                .grid-innovative {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <div class="grid-innovative">
            <!-- Left Side: Keyboard Shortcuts Cheatsheet (Accessibility) -->
            <div class="card bg-white border border-black/10 shadow-md">
                <div class="card-body">
                    <h3 class="text-xl font-bold mb-2 flex items-center gap-2 text-indigo-400">
                        Pintasan Keyboard Player (Aksesibilitas)
                    </h3>
                    <p class="text-slate-600 text-sm mb-6">
                        Sistem pemutar audio pintar dilengkapi pintasan tombol keyboard untuk mempermudah rekan tunanetra bernavigasi secara mandiri:
                    </p>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; flex-grow: 1;">
                        <div class="bg-black/5 border border-black/10 p-3 rounded-lg flex items-center gap-3 justify-between">
                            <span class="text-sm text-slate-600 font-medium">Main / Jeda Suara</span>
                            <kbd style="background: #334155; color: #fff; padding: 0.3rem 0.6rem; border-radius: 4px; font-family: monospace; font-size: 0.8rem; box-shadow: 0 2px 0 #1e293b;">Spasi</kbd>
                        </div>
                        <div class="bg-black/5 border border-black/10 p-3 rounded-lg flex items-center gap-3 justify-between">
                            <span class="text-sm text-slate-600 font-medium">Kalimat Sebelumnya</span>
                            <kbd style="background: #334155; color: #fff; padding: 0.3rem 0.6rem; border-radius: 4px; font-family: monospace; font-size: 0.8rem; box-shadow: 0 2px 0 #1e293b;">← Panah Kiri</kbd>
                        </div>
                        <div class="bg-black/5 border border-black/10 p-3 rounded-lg flex items-center gap-3 justify-between">
                            <span class="text-sm text-slate-600 font-medium">Kalimat Selanjutnya</span>
                            <kbd style="background: #334155; color: #fff; padding: 0.3rem 0.6rem; border-radius: 4px; font-family: monospace; font-size: 0.8rem; box-shadow: 0 2px 0 #1e293b;">Panah Kanan →</kbd>
                        </div>
                        <div class="bg-black/5 border border-black/10 p-3 rounded-lg flex items-center gap-3 justify-between">
                            <span class="text-sm text-slate-600 font-medium">Ulangi Kalimat Aktif</span>
                            <kbd style="background: #334155; color: #fff; padding: 0.3rem 0.6rem; border-radius: 4px; font-family: monospace; font-size: 0.8rem; box-shadow: 0 2px 0 #1e293b;">Tombol R</kbd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Quick QR Generator -->
            <div class="card bg-white border border-black/10 shadow-md" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                <div class="card-body w-full">
                    <h3 class="text-lg font-bold mb-3 text-left">
                        Pintasan QR Code
                    </h3>
                    <div class="mb-4 text-left">
                        <label for="quick-book-selector" class="text-xs font-bold text-slate-600 uppercase block mb-1">Pilih Buku:</label>
                        <select id="quick-book-selector" onchange="updateQuickQR(this.value)" class="select select-bordered w-full bg-white text-black text-sm mt-1">
                            @forelse (\App\Models\AudioBuku::all() as $b)
                                <option value="{{ $b->qr_token }}" data-title="{{ $b->judul }}">
                                    {{ \Illuminate\Support\Str::limit($b->judul, 28) }}
                                </option>
                            @empty
                                <option value="">Tidak ada buku</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="bg-white p-3 rounded-xl flex items-center justify-center mx-auto" style="width: 140px; height: 140px; box-shadow: 0 4px 20px rgba(0,0,0,0.4); margin: 0.2rem auto;">
                        <img id="quick-qr-img" src="" alt="Quick QR Preview" class="rounded" style="width: 116px; height: 116px; min-width: 116px; min-height: 116px; flex-shrink: 0; object-fit: contain;">
                    </div>
                    
                    <div class="flex gap-2 w-full mt-4">
                        <button onclick="printQuickQR()" class="btn btn-outline btn-sm flex-1 rounded-xl text-xs">Cetak</button>
                        <button onclick="downloadQuickQR()" class="btn btn-primary btn-sm flex-1 rounded-xl text-xs font-semibold">Unduh</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // --- Quick QR Generator Logic ---
            function updateQuickQR(token) {
                if (!token) return;
                const selector = document.getElementById('quick-book-selector');
                const selectedOpt = selector.options[selector.selectedIndex];
                const title = selectedOpt.getAttribute('data-title');
                
                const playUrl = `{{ $serverOrigin }}/scan/book/${token}`;
                const qrImg = document.getElementById('quick-qr-img');
                if (qrImg) {
                    qrImg.src = `/qr-code?data=${encodeURIComponent(playUrl)}&size=200`;
                }
            }

            function printQuickQR() {
                const qrImg = document.getElementById('quick-qr-img');
                const selector = document.getElementById('quick-book-selector');
                const title = selector.options[selector.selectedIndex].getAttribute('data-title');
                
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Cetak QR - ${title}</title>
                        <style>
                            body { font-family: system-ui, -apple-system, sans-serif; text-align: center; padding: 40px; color: #000; background: #fff; }
                            .container { border: 3px dashed #b8860b; padding: 30px; display: inline-block; border-radius: 15px; max-width: 350px; }
                            h2 { margin: 0 0 10px 0; font-size: 1.5rem; color: #000; }
                            img { width: 220px; height: 220px; display: block; margin: 0 auto; border: 1px solid #e5e7eb; padding: 8px; border-radius: 8px; }
                            .footer { margin-top: 20px; font-size: 0.8rem; border-top: 1px solid #e5e7eb; padding-top: 15px; font-weight: bold; color: #7a5a00; }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <h2>${title}</h2>
                            <img src="${qrImg.src}" />
                            <div class="footer">SISTEM READ-ASSIST QR-AUDIO</div>
                        </div>
                        <script>
                            window.onload = function() { window.print(); window.close(); }
                        <\/script>
                    </body>
                    </html>
                `);
                printWindow.document.close();
            }

            function downloadQuickQR() {
                const qrImg = document.getElementById('quick-qr-img');
                const selector = document.getElementById('quick-book-selector');
                const title = selector.options[selector.selectedIndex].getAttribute('data-title');
                const slug = title.toLowerCase().replace(/[^a-z0-9]+/g, '-');
                
                fetch(qrImg.src)
                    .then(r => r.blob())
                    .then(blob => {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `${slug}_qr_code.svg`;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    });
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Init quick QR preview
                const bookSelector = document.getElementById('quick-book-selector');
                if (bookSelector) {
                    updateQuickQR(bookSelector.value);
                }
            });
        </script>

        <!-- Quick Access Grid -->
        <h3 class="text-xl font-bold mb-5">Akses Cepat</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <!-- Action 1: Browse Catalog -->
            <div class="card bg-white border border-black/10 shadow-md">
                <div class="card-body">
                    <span class="block mb-3 text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17"/></svg>
                    </span>
                    <h4 class="text-lg font-semibold mb-2">Buka Katalog Buku</h4>
                    <p class="text-slate-600 text-sm mb-6" style="min-height: 40px;">
                        Cari buku audio favorit Anda dan mulai mendengarkan kalimat demi kalimat.
                    </p>
                    <a href="{{ route('audio-books.index') }}" class="btn btn-primary">
                        Telusuri Katalog
                    </a>
                </div>
            </div>

            <!-- Action 2: Add My Book -->
            <div class="card bg-white border border-black/10 shadow-md">
                <div class="card-body">
                    <span class="block mb-3 text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                    </span>
                    <h4 class="text-lg font-semibold mb-2">Tambah Buku Baru</h4>
                    <p class="text-slate-600 text-sm mb-6" style="min-height: 40px;">
                        Unggah file PDF/EPUB buku milik Anda untuk diolah oleh sistem otomatis.
                    </p>
                    <a href="{{ route('user.books.create') }}" class="btn btn-ghost">
                        Tambahkan Buku Saya
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
