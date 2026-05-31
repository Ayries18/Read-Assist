@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
        <div>
            <h1 class="text-gradient text-4xl font-extrabold tracking-tight">Katalog Buku Audio</h1>
            <p class="text-slate-300 mt-1">Telusuri dan pelajari koleksi buku yang tersedia untuk belajar mandiri.</p>
        </div>
        
        @if (in_array(session('auth_role'), ['admin', 'user'], true))
            <a href="{{ session('auth_role') === 'user' ? '/user/tambah-buku' : '/katalog-audio/tambah' }}" class="btn btn-primary btn-sm">
                + Tambah Buku Baru
            </a>
        @endif
    </div>

    <!-- Search & Filter Form -->
    <div class="card border shadow-sm p-4 sm:p-6 mb-8" style="background: #121316; border-color: rgba(255, 255, 255, 0.08);">
        <form method="GET" action="/katalog-audio" class="flex flex-col sm:flex-row gap-3 sm:gap-4 items-stretch sm:items-center flex-wrap">
            <div class="flex-1 min-w-0 sm:min-w-[200px] relative">
                <input
                    type="text"
                    id="search"
                    name="search"
                    class="input input-bordered w-full bg-base-300/60 text-white placeholder:text-slate-500 pl-10"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari judul, penulis..."
                >
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs">Cari</span>
            </div>
            <div class="flex gap-3 flex-wrap sm:flex-nowrap">
                <select name="category" class="select select-bordered bg-base-300/60 text-white min-w-0 sm:min-w-[130px] flex-1 sm:flex-none">
                    <option value="">Semua</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" @selected(($selectedCategory ?? '') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
                <select name="sort" class="select select-bordered bg-base-300/60 text-white min-w-0 sm:min-w-[120px] flex-1 sm:flex-none">
                    <option value="terbaru" @selected(($sort ?? '') === 'terbaru' || ($sort ?? 'terbaru') === 'terbaru')>Terbaru</option>
                    <option value="terlama" @selected(($sort ?? '') === 'terlama')>Terlama</option>
                    <option value="judul" @selected(($sort ?? '') === 'judul')>A-Z</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm px-5">Cari</button>

            @if (!empty($search) || !empty($selectedCategory) || ($sort ?? '') !== 'terbaru')
                <a href="/katalog-audio" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
    </div>
            <select name="category" class="select select-bordered bg-base-300/60 text-white min-w-[140px]">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}" @selected(($selectedCategory ?? '') === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
            <select name="sort" class="select select-bordered bg-base-300/60 text-white min-w-[150px]">
                <option value="terbaru" @selected(($sort ?? 'terbaru') === 'terbaru')>Terbaru</option>
                <option value="terlama" @selected(($sort ?? '') === 'terlama')>Terlama</option>
                <option value="judul" @selected(($sort ?? '') === 'judul')>Judul A-Z</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm px-6">Terapkan</button>

            @if (!empty($search) || !empty($selectedCategory) || ($sort ?? '') !== 'terbaru')
                <a href="/katalog-audio" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
    </div>

    <!-- Books Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mt-6">
        @forelse ($audioBooks as $book)
            <div class="card border shadow-sm p-4 sm:p-6 flex flex-col justify-between" style="background: #121316; border-color: rgba(255, 255, 255, 0.08);">
                <div>
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

                    <h3 class="card-title text-white mt-2 mb-1 text-lg leading-snug">{{ $book->judul }}</h3>
                    
                    <!-- Metadata Buku -->
                    <div class="text-xs text-slate-300 mb-3 flex flex-col gap-1">
                        <div class="flex gap-3 items-center flex-wrap">
                            <span title="Kategori">{{ $book->kategori ?: 'Umum' }}</span>
                        </div>
                        <span class="card-update-time text-xs text-slate-400" data-utc-updated="{{ $book->updated_at->toIso8601String() }}">
                            Diperbarui: <strong></strong>
                        </span>
                    </div>

                    <p class="text-sm text-slate-400 line-clamp-3 mb-4 leading-relaxed">
                        {{ $book->deskripsi ?: 'Tidak ada deskripsi yang tersedia untuk buku ini.' }}
                    </p>
                </div>

                <div class="flex flex-col gap-2 border-t border-white/10 pt-3 mt-auto z-10">
                    <!-- Mini Player Trigger -->
                    <button onclick="window.playMiniPlayer('{{ addslashes($book->judul) }}', '', '{{ addslashes($book->deskripsi) }}', '{{ $book->cover ? '/storage/'.$book->cover : '' }}')" class="btn btn-ghost w-full py-2 text-sm flex items-center justify-center gap-1">
                        Putar Instan
                    </button>
                    <div class="flex gap-2 w-full">
                        <a href="/katalog-audio/{{ $book->id }}" class="btn btn-ghost btn-sm flex-1 py-2 text-sm text-center">
                            Detail
                        </a>
                        <a href="/katalog/{{ $book->qr_token }}" class="btn btn-primary btn-sm flex-[1.2] py-2 text-sm text-center">
                            Dengar
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="card border shadow-sm p-12 text-center col-span-full" style="background: #121316; border-color: rgba(255, 255, 255, 0.08);">
                <p class="text-slate-300 text-lg mb-4">Buku tidak ditemukan.</p>
                <a href="{{ route('audio-books.index') }}" class="btn btn-ghost btn-sm">Lihat Semua Buku</a>
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
