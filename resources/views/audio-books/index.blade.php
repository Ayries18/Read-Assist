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
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div class="card-content">
                    <!-- Book Cover -->
                    <div class="book-cover-wrapper">
                        @if ($book->cover)
                            <img src="/storage/{{ $book->cover }}" alt="Cover {{ $book->judul }}" class="book-cover-img">
                        @else
                            <div class="book-cover-placeholder">
                                <span class="book-cover-placeholder-title">{{ $book->judul }}</span>
                            </div>
                        @endif
                    </div>

                    <h3 class="card-title" style="margin-top: 0.5rem; margin-bottom: 0.4rem; font-size: 1.15rem; line-height: 1.4;">{{ $book->judul }}</h3>
                    
                    <!-- Metadata Buku -->
                    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.8rem; display: flex; flex-direction: column; gap: 0.3rem;">
                        <div style="display: flex; gap: 0.8rem; align-items: center; flex-wrap: wrap;">
                            <span title="Kategori">🏷️ {{ $book->kategori ?: 'Umum' }}</span>
                        </div>
                        <span class="card-update-time" data-utc-updated="{{ $book->updated_at->toIso8601String() }}" style="font-size: 0.75rem; color: var(--text-muted);">
                            🔄 Diperbarui: <strong></strong>
                        </span>
                    </div>

                    <p class="card-desc" style="margin-bottom: 1rem; font-size: 0.88rem; line-height: 1.6;">
                        {{ $book->deskripsi ?: 'Tidak ada deskripsi yang tersedia untuk buku ini.' }}
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.5rem; border-top: 1px solid var(--border-glass); padding-top: 0.8rem; margin-top: auto; z-index: 1;">
                    <!-- Mini Player Trigger -->
                    <button onclick="window.playMiniPlayer('{{ addslashes($book->judul) }}', '', '{{ addslashes($book->deskripsi) }}', '{{ $book->cover ? '/storage/'.$book->cover : '' }}')" class="btn btn-secondary" style="width: 100%; padding: 0.5rem; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                        ⚡ Putar Instan
                    </button>
                    <div style="display: flex; gap: 0.5rem; width: 100%;">
                        <a href="/katalog-audio/{{ $book->id }}" class="btn btn-secondary btn-inline" style="flex: 1; padding: 0.5rem; font-size: 0.85rem; text-align: center;">
                            Detail
                        </a>
                        <a href="/qr-audio/{{ $book->qr_token }}" class="btn btn-primary btn-inline" style="flex: 1.2; padding: 0.5rem; font-size: 0.85rem; text-align: center;">
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

    <script>
        function updateCardRelativeTimes() {
            document.querySelectorAll('.card-update-time').forEach(el => {
                const utcStr = el.getAttribute('data-utc-updated');
                if (utcStr) {
                    const diffMs = new Date() - new Date(utcStr);
                    const diffMins = Math.floor(diffMs / 60000);
                    let relativeText = '';
                    
                    if (diffMins < 1) {
                        relativeText = 'baru saja';
                    } else if (diffMins < 60) {
                        relativeText = `${diffMins} menit yang lalu`;
                    } else {
                        const diffHours = Math.floor(diffMins / 60);
                        if (diffHours < 24) {
                            relativeText = `${diffHours} jam yang lalu`;
                        } else {
                            const diffDays = Math.floor(diffHours / 24);
                            relativeText = `${diffDays} hari yang lalu`;
                        }
                    }
                    el.querySelector('strong').innerText = relativeText;
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateCardRelativeTimes();
            setInterval(updateCardRelativeTimes, 15000);
        });
    </script>
@endsection
