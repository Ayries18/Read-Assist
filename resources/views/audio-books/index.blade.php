@extends('layouts.app')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="text-gradient" style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.5px;">Katalog Buku Audio</h1>
            <p style="color: var(--text-secondary); margin-top: 0.3rem;">Telusuri dan pelajari koleksi buku yang tersedia untuk belajar mandiri.</p>
        </div>
        
        @if (in_array(session('auth_role'), ['admin', 'user'], true))
            <a href="{{ session('auth_role') === 'user' ? '/user/tambah-buku' : '/katalog-audio/tambah' }}" class="btn btn-primary btn-inline">
                + Tambah Buku Baru
            </a>
        @endif
    </div>

    <!-- Search Form -->
    <div class="card" style="padding: 1.5rem; margin-bottom: 2rem;">
        <form method="GET" action="/katalog-audio" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px; position: relative;">
                <input
                    type="text"
                    id="search"
                    name="search"
                    class="form-control"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari berdasarkan judul..."
                    style="padding-left: 2.5rem;"
                >
                <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">🔍</span>
            </div>
            <button type="submit" class="btn btn-primary btn-inline" style="padding: 0.8rem 2rem;">Cari</button>

            @if (!empty($search))
                <a href="/katalog-audio" class="btn btn-secondary btn-inline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Books Grid -->
    <div class="grid-layout">
        @forelse ($audioBooks as $book)
            <div class="card">
                <div class="card-content">
                    <span class="user-role" style="font-size: 0.7rem; background: rgba(99, 102, 241, 0.15); padding: 2px 8px; border-radius: 4px; display: inline-block; margin-bottom: 0.8rem;">
                        {{ $book->kategori ?: 'Tanpa Kategori' }}
                    </span>
                    <h3 class="card-title">{{ $book->judul }}</h3>
                    
                    <div class="card-meta">
                        @if (!empty($book->penulis))
                            <span>✍️ {{ $book->penulis }}</span>
                        @else
                            <span>✍️ Penulis tidak diketahui</span>
                        @endif
                    </div>
                    
                    <p class="card-desc">
                        {{ $book->deskripsi ?: 'Tidak ada deskripsi yang tersedia untuk buku ini.' }}
                    </p>
                    
                    <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem;">
                        <a href="/katalog-audio/{{ $book->id }}" class="btn btn-secondary btn-inline" style="flex: 1; padding: 0.6rem 1rem; font-size: 0.9rem;">
                            Detail
                        </a>
                        <a href="/qr-audio/{{ $book->qr_token }}" class="btn btn-primary btn-inline" style="flex: 1.5; padding: 0.6rem 1rem; font-size: 0.9rem;">
                            🔊 Dengar
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 1rem;">Buku tidak ditemukan.</p>
                <a href="{{ route('audio-books.index') }}" class="btn btn-secondary btn-inline">Lihat Semua Buku</a>
            </div>
        @endforelse
    </div>

    <div style="margin-top: 2rem;">
        {{ $audioBooks->links() }}
    </div>
@endsection
