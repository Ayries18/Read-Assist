@extends('layouts.app')

@section('content')
    <div style="max-width: 800px; margin: 0 auto;">
        <!-- Welcome Card -->
        <div class="card" style="padding: 2.5rem; margin-bottom: 2rem; background: linear-gradient(135deg, rgba(20, 30, 55, 0.7), rgba(99, 102, 241, 0.1)); border-color: rgba(255, 255, 255, 0.1);">
            <div class="card-content">
                <span class="user-role" style="font-size: 0.8rem; background: rgba(99, 102, 241, 0.2); padding: 4px 12px; border-radius: 4px; display: inline-block; margin-bottom: 1rem;">
                    👤 Member Area
                </span>
                <h1 class="text-gradient" style="font-size: 2.4rem; font-weight: 800; line-height: 1.2; margin-bottom: 0.8rem;">
                    Selamat Datang, {{ session('auth_name') }}!
                </h1>
                <p style="color: var(--text-secondary); font-size: 1.05rem; max-width: 600px; line-height: 1.6;">
                    Anda masuk sebagai pengguna biasa. Anda dapat menelusuri katalog buku audio untuk didengarkan, serta menambahkan buku baru yang Anda miliki ke dalam sistem.
                </p>
            </div>
        </div>

        <!-- 📊 Statistik Dashboard -->
        <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.2rem; display: flex; align-items: center; gap: 0.5rem;">
            <span>📊</span> Statistik Dashboard
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1.2rem; margin-bottom: 2.5rem;">
            <!-- Stat 1 -->
            <div class="card" style="padding: 1.25rem; text-align: center;">
                <div class="card-content">
                    <span style="font-size: 1.8rem; display: block; margin-bottom: 0.25rem;">📚</span>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; letter-spacing: 0.5px;">Buku Audio</span>
                    <h2 style="font-size: 2rem; font-weight: 800; margin-top: 0.25rem; color: var(--accent-secondary);">
                        {{ \App\Models\AudioBuku::count() }}
                    </h2>
                </div>
            </div>
            <!-- Stat 2 -->
            <div class="card" style="padding: 1.25rem; text-align: center;">
                <div class="card-content">
                    <span style="font-size: 1.8rem; display: block; margin-bottom: 0.25rem;">📤</span>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; letter-spacing: 0.5px;">Unggahan Saya</span>
                    <h2 style="font-size: 2rem; font-weight: 800; margin-top: 0.25rem; color: var(--accent-primary);">
                        {{ \App\Models\AudioBuku::where('user_id', session('auth_id'))->count() }}
                    </h2>
                </div>
            </div>
            <!-- Stat 3 -->
            <div class="card" style="padding: 1.25rem; text-align: center;">
                <div class="card-content">
                    <span style="font-size: 1.8rem; display: block; margin-bottom: 0.25rem;">👥</span>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; letter-spacing: 0.5px;">Total Anggota</span>
                    <h2 style="font-size: 2rem; font-weight: 800; margin-top: 0.25rem; color: var(--accent-success);">
                        {{ \App\Models\User::count() }}
                    </h2>
                </div>
            </div>
            <!-- Stat 4 -->
            <div class="card" style="padding: 1.25rem; text-align: center;">
                <div class="card-content">
                    <span style="font-size: 1.8rem; display: block; margin-bottom: 0.25rem;">📱</span>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; letter-spacing: 0.5px;">QR Code Terbit</span>
                    <h2 style="font-size: 2rem; font-weight: 800; margin-top: 0.25rem; color: #a855f7;">
                        {{ \App\Models\AudioBuku::count() }}
                    </h2>
                </div>
            </div>
        </div>

        <!-- 🛠️ Accessibility Guide & QR Quick Actions -->
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
            <div class="card" style="padding: 1.8rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div class="card-content" style="height: 100%; display: flex; flex-direction: column;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.6rem; display: flex; align-items: center; gap: 0.6rem; color: var(--accent-primary);">
                        ⌨️ Pintasan Keyboard Player (Aksesibilitas)
                    </h3>
                    <p style="color: var(--text-secondary); font-size: 0.88rem; margin-bottom: 1.5rem;">
                        Sistem pemutar audio pintar dilengkapi pintasan tombol keyboard untuk mempermudah rekan tunanetra bernavigasi secara mandiri:
                    </p>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; flex-grow: 1;">
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-glass); padding: 0.8rem 1rem; border-radius: 8px; display: flex; align-items: center; gap: 0.8rem; justify-content: space-between;">
                            <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Main / Jeda Suara</span>
                            <kbd style="background: #334155; color: #fff; padding: 0.3rem 0.6rem; border-radius: 4px; font-family: monospace; font-size: 0.8rem; box-shadow: 0 2px 0 #1e293b;">Spasi</kbd>
                        </div>
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-glass); padding: 0.8rem 1rem; border-radius: 8px; display: flex; align-items: center; gap: 0.8rem; justify-content: space-between;">
                            <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Kalimat Sebelumnya</span>
                            <kbd style="background: #334155; color: #fff; padding: 0.3rem 0.6rem; border-radius: 4px; font-family: monospace; font-size: 0.8rem; box-shadow: 0 2px 0 #1e293b;">← Panah Kiri</kbd>
                        </div>
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-glass); padding: 0.8rem 1rem; border-radius: 8px; display: flex; align-items: center; gap: 0.8rem; justify-content: space-between;">
                            <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Kalimat Selanjutnya</span>
                            <kbd style="background: #334155; color: #fff; padding: 0.3rem 0.6rem; border-radius: 4px; font-family: monospace; font-size: 0.8rem; box-shadow: 0 2px 0 #1e293b;">Panah Kanan →</kbd>
                        </div>
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-glass); padding: 0.8rem 1rem; border-radius: 8px; display: flex; align-items: center; gap: 0.8rem; justify-content: space-between;">
                            <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Ulangi Kalimat Aktif</span>
                            <kbd style="background: #334155; color: #fff; padding: 0.3rem 0.6rem; border-radius: 4px; font-family: monospace; font-size: 0.8rem; box-shadow: 0 2px 0 #1e293b;">Tombol R</kbd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Quick QR Generator -->
            <div class="card" style="padding: 1.8rem; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                <div class="card-content" style="width: 100%;">
                    <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.8rem; text-align: left;">
                        ⚡ Pintasan QR Code
                    </h3>
                    <div style="margin-bottom: 1rem; text-align: left;">
                        <label for="quick-book-selector" style="font-size: 0.72rem; font-weight: bold; color: var(--text-muted); text-transform: uppercase;">Pilih Buku:</label>
                        <select id="quick-book-selector" onchange="updateQuickQR(this.value)" class="form-control" style="font-size: 0.85rem; padding: 0.5rem 0.8rem; margin-top: 0.3rem; width: 100%;">
                            @forelse (\App\Models\AudioBuku::all() as $b)
                                <option value="{{ $b->qr_token }}" data-title="{{ $b->judul }}">
                                    {{ \Illuminate\Support\Str::limit($b->judul, 28) }}
                                </option>
                            @empty
                                <option value="">Tidak ada buku</option>
                            @endforelse
                        </select>
                    </div>

                    <div style="background: #ffffff; padding: 0.8rem; border-radius: 12px; display: inline-block; box-shadow: 0 4px 20px rgba(0,0,0,0.4); margin: 0.2rem 0;">
                        <img id="quick-qr-img" src="" alt="Quick QR Preview" style="width: 110px; height: 110px; display: block; border-radius: 4px;">
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem; width: 100%; margin-top: 1rem;">
                        <button onclick="printQuickQR()" class="btn btn-secondary" style="flex: 1; padding: 0.5rem; font-size: 0.78rem; display: flex; align-items: center; justify-content: center; gap: 0.3rem;">🖨️ Cetak</button>
                        <button onclick="downloadQuickQR()" class="btn btn-primary" style="flex: 1; padding: 0.5rem; font-size: 0.78rem; display: flex; align-items: center; justify-content: center; gap: 0.3rem;">📥 Unduh</button>
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
                
                // Build the URL to qr-audio
                const playUrl = `${window.location.origin}/qr-audio/${token}`;
                const qrImg = document.getElementById('quick-qr-img');
                if (qrImg) {
                    qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(playUrl)}`;
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
                            .container { border: 3px dashed #6366f1; padding: 30px; display: inline-block; border-radius: 15px; max-width: 350px; }
                            h2 { margin: 0 0 10px 0; font-size: 1.5rem; color: #1e1b4b; }
                            img { width: 220px; height: 220px; display: block; margin: 0 auto; border: 1px solid #e5e7eb; padding: 8px; border-radius: 8px; }
                            .footer { margin-top: 20px; font-size: 0.8rem; border-top: 1px solid #e5e7eb; padding-top: 15px; font-weight: bold; color: #6366f1; }
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
                        a.download = `${slug}_qr_code.png`;
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
        <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.2rem;">⚡ Akses Cepat</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <!-- Action 1: Browse Catalog -->
            <div class="card" style="padding: 1.8rem;">
                <div class="card-content">
                    <span style="font-size: 2rem; display: block; margin-bottom: 0.8rem;">📖</span>
                    <h4 style="font-size: 1.15rem; font-weight: 600; margin-bottom: 0.5rem;">Buka Katalog Buku</h4>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 1.5rem; min-height: 40px;">
                        Cari buku audio favorit Anda dan mulai mendengarkan kalimat demi kalimat.
                    </p>
                    <a href="{{ route('audio-books.index') }}" class="btn btn-primary" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;">
                        Telusuri Katalog
                    </a>
                </div>
            </div>

            <!-- Action 2: Add My Book -->
            <div class="card" style="padding: 1.8rem;">
                <div class="card-content">
                    <span style="font-size: 2rem; display: block; margin-bottom: 0.8rem;">📤</span>
                    <h4 style="font-size: 1.15rem; font-weight: 600; margin-bottom: 0.5rem;">Tambah Buku Baru</h4>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 1.5rem; min-height: 40px;">
                        Unggah file PDF/EPUB buku milik Anda untuk diolah oleh sistem otomatis.
                    </p>
                    <a href="{{ route('user.books.create') }}" class="btn btn-secondary" style="padding: 0.6rem 1.2rem; font-size: 0.9rem; border-color: var(--accent-primary); color: var(--accent-primary);">
                        Tambahkan Buku Saya
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
