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
