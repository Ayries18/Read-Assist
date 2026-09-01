@extends('layouts.app')

@section('content')
    @php
        if (!isset($qrUrl)) {
            $qrUrl = \App\Http\Controllers\AudioBukuController::buildQrUrl($book);
        }
    @endphp
    @if (!session()->has('qr_restricted_token') || session()->has('auth_role'))
    <div class="mb-8">
        <a href="/katalog-audio" class="text-slate-400 font-medium text-sm inline-flex items-center gap-2">
            ← Kembali ke Katalog
        </a>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-8 items-start">
        <!-- Left Side: Book Details & TTS Player -->
        <div class="flex flex-col gap-7">
            <div id="reader-main-card" class="card border shadow-sm p-6" style="background: #ffffff; border-color: rgba(0, 0, 0, 0.12);">
                <div>
                    <!-- Book Header with Cover and Metadata -->
                    <div class="flex gap-6 flex-wrap mb-6 items-start">
                        <!-- Book Cover -->
                        <div class="w-[140px] h-[190px] rounded-xl overflow-hidden shrink-0 shadow-lg border border-black/10">
                            @if ($book->cover)
                                <img src="/storage/{{ $book->cover }}" alt="Cover {{ $book->judul }}" class="w-full h-full object-cover">
                            @else
                                <div class="book-cover-placeholder">
                                    <span class="book-cover-placeholder-title text-sm">{{ $book->judul }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Title and Quick Stats -->
                        <div class="flex-1 flex flex-col justify-center">
                            <h1 id="book-title" class="text-gradient text-3xl font-bold mb-2 leading-tight">
                                {{ $book->judul }}
                            </h1>

                            <!-- Metadata bar -->
                            <div class="text-sm text-slate-600 flex flex-col gap-1">
                                <span>Kategori: <strong>{{ $book->kategori ?: 'Umum' }}</strong></span>
                                <span id="upload-time" data-utc="{{ $book->created_at->toIso8601String() }}">Diunggah: <strong>{{ $book->created_at->format('d M Y, H:i') }}</strong></span>
                                <span id="update-time-relative" data-utc-updated="{{ $book->updated_at->toIso8601String() }}">Diperbarui: <strong></strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Metadata Buku Grid -->
                    <h2 class="text-base text-black font-semibold mb-3">Metadata Buku</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                        <div class="bg-black/5 border border-black/10 rounded-lg p-3 text-center">
                            <span class="text-xs text-slate-600 uppercase font-bold block mb-1">Karakter</span>
                            <span class="text-lg font-bold text-blue-600">{{ strlen($book->deskripsi) }}</span>
                        </div>
                        <div class="bg-black/5 border border-black/10 rounded-lg p-3 text-center">
                            <span class="text-xs text-slate-600 uppercase font-bold block mb-1">Jumlah Kata</span>
                            <span class="text-lg font-bold text-indigo-600">{{ str_word_count($book->deskripsi) }}</span>
                        </div>
                        <div class="bg-black/5 border border-black/10 rounded-lg p-3 text-center">
                            <span class="text-xs text-slate-600 uppercase font-bold block mb-1">Estimasi Baca</span>
                            <span class="text-lg font-bold text-emerald-600">{{ ceil(str_word_count($book->deskripsi) / 150) }} Menit</span>
                        </div>
                        <div class="bg-black/5 border border-black/10 rounded-lg p-3 text-center">
                            <span class="text-xs text-slate-600 uppercase font-bold block mb-1">Status Audio</span>
                            <span class="text-base font-bold text-emerald-600 block mt-1">Siap</span>
                        </div>
                    </div>

                    @if ($book->user_id)
                        <p class="text-slate-600 mb-6 text-sm flex items-center gap-2">
                            <span class="text-slate-600">Pengunggah ID: {{ $book->user_id }}</span>
                        </p>
                    @endif

                    <style>
                        .high-contrast-mode {
                            background: #000000 !important;
                            color: #ffff00 !important;
                            border-color: #ffff00 !important;
                        }
                        .high-contrast-mode h2,
                        .high-contrast-mode h3,
                        .high-contrast-mode h4,
                        .high-contrast-mode p,
                        .high-contrast-mode span,
                        .high-contrast-mode label,
                        .high-contrast-mode strong,
                        .high-contrast-mode a {
                            color: #ffff00 !important;
                        }
                        .high-contrast-mode button,
                        .high-contrast-mode select,
                        .high-contrast-mode input,
                        .high-contrast-mode textarea,
                        .high-contrast-mode .btn,
                        .high-contrast-mode a.btn {
                            background: #000 !important;
                            color: #ffff00 !important;
                            border: 2px solid #ffff00 !important;
                        }
                        .light-mode {
                            background: #ffffff !important;
                            color: #000000 !important;
                            border-color: #000000 !important;
                        }
                        .light-mode h2,
                        .light-mode h3,
                        .light-mode h4,
                        .light-mode p,
                        .light-mode span,
                        .light-mode label,
                        .light-mode strong,
                        .light-mode a {
                            color: #000000 !important;
                        }
                        .light-mode button,
                        .light-mode select,
                        .light-mode input,
                        .light-mode textarea {
                            background: #ffffff !important;
                            color: #000000 !important;
                            border: 2px solid #000000 !important;
                        }
                        .light-mode .highlight,
                        .light-mode .accent {
                            color: #7a5a00 !important;
                        }
                    </style>

                    <h2 class="text-base text-black font-semibold mb-2">Deskripsi Buku</h2>
                    <div class="bg-black/5 border border-black/10 rounded-xl p-5 mb-6">
                        <p id="display-description" class="text-slate-700 leading-relaxed text-sm whitespace-pre-line">
                            {{ isset($book->deskripsi) ? \Illuminate\Support\Str::limit($book->deskripsi, 600) : 'Tidak ada deskripsi.' }}
                        </p>
                        <div id="book-description" class="hidden">{{ $book->deskripsi ?? 'Tidak ada deskripsi.' }}</div>
                    </div>

                    <a href="/read-assist?text={{ urlencode(\Illuminate\Support\Str::limit($book->deskripsi, 5000, '')) }}" target="_blank" rel="noopener" class="btn btn-primary w-full sm:w-auto px-7 py-3 text-sm mt-1 mb-6 inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Analisis dengan Read Assist
                    </a>

                    @if ($book->file_buku)
                        <div class="bg-black/5 border border-black/10 rounded-lg p-3 px-4 flex justify-between items-center mb-8">
                            <span class="text-sm text-slate-600">File PDF/EPUB Terlampir</span>
                            <a href="/storage/{{ $book->file_buku }}" target="_blank" class="btn btn-ghost btn-sm py-1 px-4 text-sm">
                                Buka File
                            </a>
                        </div>
                    @endif

                    <!-- Audio Player Area -->
                    <div class="bg-[#b8860b]/10 border border-[#b8860b]/25 rounded-xl p-6 text-center">
                        <h2 class="mb-2 text-base">Dengarkan di Laptop Ini</h2>

                        @if ($book->audio_status === 'completed' && $book->file_audio && $book->file_audio !== 'tts')
                            <div class="mb-4">
                                <audio id="generated-audio-player" controls class="w-full max-w-md mx-auto">
                                    <source src="{{ route('audio.stream', $book) }}" type="audio/mpeg">
                                    Browser Anda tidak mendukung pemutar audio.
                                </audio>
                                <div class="flex items-center justify-center gap-3 mt-3">
                                    <button type="button" onclick="skipGeneratedAudio(-10)" title="Mundur 10 detik" aria-label="Mundur 10 detik" class="flex items-center justify-center w-11 h-11 rounded-full border-2 border-slate-300 text-slate-700 hover:border-indigo-500 hover:text-indigo-600 active:scale-90 transition-all">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/><path d="M10.29 16.38h-1.1v-3.82l-1.07.6v-1.01l2.17-1.32v5.55z"/><path d="M13.42 10.98c1.7 0 3.03 1.34 3.03 3 0 1.66-1.33 3-3.03 3-1.7 0-3.03-1.34-3.03-3 0-1.66 1.33-3 3.03-3zm0 4.83c.83 0 1.5-.6 1.5-1.83 0-1.23-.67-1.83-1.5-1.83s-1.5.6-1.5 1.83c0 1.23.67 1.83 1.5 1.83z"/></svg>
                                    </button>
                                    <button type="button" onclick="skipGeneratedAudio(10)" title="Maju 10 detik" aria-label="Maju 10 detik" class="flex items-center justify-center w-11 h-11 rounded-full border-2 border-slate-300 text-slate-700 hover:border-indigo-500 hover:text-indigo-600 active:scale-90 transition-all">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M18 13c0 3.31-2.69 6-6 6s-6-2.69-6-6 2.69-6 6-6v4l5-5-5-5v4c-4.42 0-8 3.58-8 8s3.58 8 8 8 8-3.58 8-8h-2z"/><path d="M10.29 16.38h-1.1v-3.82l-1.07.6v-1.01l2.17-1.32v5.55z"/><path d="M13.42 10.98c1.7 0 3.03 1.34 3.03 3 0 1.66-1.33 3-3.03 3-1.7 0-3.03-1.34-3.03-3 0-1.66 1.33-3 3.03-3zm0 4.83c.83 0 1.5-.6 1.5-1.83 0-1.23-.67-1.83-1.5-1.83s-1.5.6-1.5 1.83c0 1.23.67 1.83 1.5 1.83z"/></svg>
                                    </button>
                                </div>
                                <div style="margin-top: 0.5rem;">
                                    <a href="{{ route('audio.stream', $book) }}" download class="btn btn-primary btn-sm px-7 py-3 text-sm">
                                    Download MP3
                                </a>
                                <a href="{{ route('audio-books.play', $book->qr_token) }}" class="btn btn-ghost btn-sm px-7 py-3 text-sm">
                                    Buka Mode Tunanetra
                                </a>
                            </div>
                        </div>

                        <script>
                            function skipGeneratedAudio(seconds) {
                                const audio = document.getElementById('generated-audio-player');
                                if (!audio) return;
                                audio.currentTime = Math.max(0, (audio.currentTime || 0) + seconds);
                            }
                        </script>
                        @else
                            <div class="mb-5 flex justify-center items-center gap-2">
                                <span class="text-sm text-slate-600">Status Audio:</span>
                                <span id="audio-status-badge" class="badge badge-sm bg-black/5 text-black border border-black/10">Browser TTS</span>
                            </div>
                            @if (in_array($book->audio_status, ['pending', 'processing']))
                            <div id="audio-gen-progress-wrap" class="w-full max-w-md mx-auto mb-4" role="group" aria-label="Progres pembuatan audio">
                                <div class="flex justify-between items-center text-xs mb-1">
                                    <span id="audio-gen-message" class="text-slate-600">Menunggu antrian...</span>
                                    <span id="audio-gen-percent" class="font-bold text-indigo-600">0%</span>
                                </div>
                                <div class="w-full bg-black/10 rounded-full h-2.5 overflow-hidden" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-valuetext="0 persen" aria-label="Progres pembuatan audio">
                                    <div id="audio-gen-bar" class="bg-indigo-500 h-2.5 rounded-full transition-all duration-500" style="width: 0%"></div>
                                </div>
                                <p class="text-xs text-slate-500 mt-2">Halaman akan diperbarui otomatis saat audio selesai.</p>
                            </div>
                            @endif
                            <div id="resume-banner" class="hidden flex flex-wrap items-center justify-center gap-3 mb-3 border-2 border-[#ffff00] rounded-xl p-3" style="display: none;">
                                <span id="resume-banner-text" class="text-sm text-black font-semibold"></span>
                                <button onclick="startTTS()" class="btn btn-ghost btn-xs border-2 border-white">Mulai Awal</button>
                                <button onclick="resumeTTS()" class="btn btn-primary btn-xs">Lanjutkan</button>
                            </div>
                            <div class="flex items-center justify-center gap-3 sm:gap-4">
                                <button id="btn-prev-show" onclick="prevTTS()" class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-full border-2 border-slate-300 text-slate-700 hover:border-indigo-500 hover:text-indigo-600 active:scale-90 transition-all" title="Kalimat Sebelumnya" aria-label="Kalimat sebelumnya">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h2v12H6zm3.5 6 8.5 6V6z"/></svg>
                                </button>
                                <button id="btn-start-show" onclick="startTTS()" class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-full border-2 border-slate-300 text-slate-700 hover:border-indigo-500 hover:text-indigo-600 active:scale-90 transition-all" title="Mulai dari Awal" aria-label="Mulai dari Awal">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                </button>
                                <button id="btn-play-show" onclick="playTTS()" class="flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-[#1DB954] text-black shadow-lg active:scale-90 transition-all duration-150" title="Putar" aria-label="Putar">
                                    <svg id="icon-play-show" class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                </button>
                                <button id="btn-pause-show" onclick="pauseTTS()" class="flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-[#1DB954] text-black shadow-lg active:scale-90 transition-all duration-150" title="Jeda" aria-label="Jeda" style="display: none;">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                </button>
                                <button id="btn-stop-show" onclick="stopTTS()" class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-full border-2 border-slate-300 text-slate-700 hover:border-red-500 hover:text-red-500 active:scale-90 transition-all" title="Berhenti & Reset" aria-label="Berhenti">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h12v12H6z"/></svg>
                                </button>
                                <button id="btn-next-show" onclick="nextTTS()" class="flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-full border-2 border-slate-300 text-slate-700 hover:border-indigo-500 hover:text-indigo-600 active:scale-90 transition-all" title="Kalimat Berikutnya" aria-label="Kalimat berikutnya">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg>
                                </button>
                            </div>
                            <div class="flex items-center justify-center gap-3 mt-4">
                                <button id="btn-speed-show" onclick="cycleTTSRate()" class="flex items-center gap-1.5 text-xs font-medium border-2 border-slate-300 text-slate-700 rounded-full px-4 py-2 hover:border-indigo-500 hover:text-indigo-600 active:scale-95 transition-all" title="Ubah kecepatan suara">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20c4 0 8-3.6 8-8v-1l-2-1-1.5 3-2.5.5V6l2.5-.5.8-2.2A18 18 0 0 0 12 3c-4.9 0-9 3.6-9 8s4 9 9 9z"/><path d="M4.5 11.5c.5-2 2-3.5 3.5-4"/></svg>
                                    <strong id="speed-label-show">1.0x</strong>
                                </button>
                                <button id="btn-settings-show" onclick="openReaderSettings()" class="flex items-center gap-1.5 text-xs font-medium border-2 border-slate-300 text-slate-700 rounded-full px-4 py-2 hover:border-indigo-500 hover:text-indigo-600 active:scale-95 transition-all" title="Pengaturan Membaca">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Pengaturan
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Admin Action Panel -->
            @if (session('auth_role') === 'admin')
                <div class="card border shadow-sm p-6" style="background: rgba(239, 68, 68, 0.02); border-color: rgba(239, 68, 68, 0.15);">
                    <h2 class="text-base text-red-400 font-semibold mb-3">Panel Kelola Admin</h2>
                    <div class="flex gap-4 flex-wrap">
                        <a href="/katalog-audio/{{ $book->id }}/edit" class="btn btn-ghost btn-sm flex-1 border-slate-400 text-center flex items-center justify-center">
                            Edit Buku
                        </a>
                        <form method="POST" action="/katalog-audio/{{ $book->id }}" onsubmit="return confirm('Yakin ingin menghapus buku ini?')" class="flex-1 m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-error w-full">
                                Hapus Buku
                            </button>
                        </form>
                        @if ($book->audio_status === 'failed')
                            <form method="POST" action="/katalog-audio/{{ $book->id }}/retry-audio" class="w-full mt-2 m-0">
                                @csrf
                                <button type="submit" class="btn btn-ghost border-[#b8860b]/30 text-indigo-400 w-full">
                                    Ulang Generate Audio
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        @if (!session()->has('qr_restricted_token') || session()->has('auth_role'))
        <!-- Right Side: QR Code -->
        <div class="flex flex-col gap-7">
            <div class="card border shadow-sm p-6 text-center flex flex-col items-center" style="padding: 2.5rem 1.8rem; background: #ffffff; border-color: rgba(0, 0, 0, 0.12);">
                <h3 class="text-xl font-bold mb-2 text-black text-center">QR-Audio untuk Tunanetra</h3>
                <p class="text-sm text-slate-600 mb-8">Pindai QR ini melalui HP Anda untuk mendengarkan buku.</p>

                <div class="bg-white p-5 rounded-xl inline-block shadow-xl">
                    <img
                        id="qr-code-img"
                        src="{{ route('qr-code.generate', ['data' => $qrUrl, 'size' => 320], false) }}"
                        alt="QR Code untuk membuka audio {{ $book->judul }}"
                        class="block mx-auto w-[260px] h-[260px]"
                    >
                </div>

                <!-- QR Action Buttons -->
                <button onclick="printQR()" class="btn btn-ghost w-full mt-7 flex items-center justify-center gap-2 text-sm">
                    Cetak QR Code
                </button>
                <button onclick="downloadQR()" class="btn btn-ghost w-full mt-2 flex items-center justify-center gap-2 text-sm">
                    Unduh QR Code
                </button>

            </div>
        </div>
        @endif
    </div>

    <div id="reader-settings-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4" role="dialog" aria-modal="true" aria-label="Pengaturan Membaca" style="display: none;">
        <div class="card border shadow-sm p-6 w-full max-w-md" style="background: #ffffff; border-color: #000000;">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-black font-bold text-lg">Pengaturan Membaca</h3>
                <button onclick="closeReaderSettings()" class="text-black border-2 border-black rounded-lg w-9 h-9 flex items-center justify-center hover:bg-black hover:text-white transition-colors" aria-label="Tutup Pengaturan">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label for="setting-rate-select" class="text-black font-medium block mb-1 text-sm">Kecepatan Suara (TTS Rate)</label>
                    <select id="setting-rate-select" onchange="setReaderRate(this.value)" class="input input-bordered w-full text-base">
                        <option value="0.75">0.75x (Lambat)</option>
                        <option value="1">1.0x (Normal)</option>
                        <option value="1.25">1.25x</option>
                        <option value="1.5">1.5x (Cepat)</option>
                        <option value="1.75">1.75x</option>
                        <option value="2">2.0x (Sangat Cepat)</option>
                    </select>
                </div>
                <div>
                    <label for="setting-font-select" class="text-black font-medium block mb-1 text-sm">Ukuran Font</label>
                    <select id="setting-font-select" onchange="setReaderFont(this.value)" class="input input-bordered w-full text-base">
                        <option value="small">Kecil (Small)</option>
                        <option value="medium" selected>Sedang (Medium)</option>
                        <option value="large">Besar (Large)</option>
                        <option value="xlarge">Sangat Besar (X-Large)</option>
                    </select>
                </div>
                <div>
                    <label for="setting-contrast-select" class="text-black font-medium block mb-1 text-sm">Kontras</label>
                    <select id="setting-contrast-select" onchange="setReaderContrast(this.value)" class="input input-bordered w-full text-base">
                        <option value="normal">Normal (Putih-Kuning)</option>
                        <option value="light">Terang (Putih-Kuning)</option>
                        <option value="high-contrast">Kontras Tinggi (Kuning-Hitam maksimum)</option>
                    </select>
                </div>
                <p class="text-xs text-[#7a5a00]">Pengaturan tersimpan otomatis di perangkat ini.</p>
                <button onclick="closeReaderSettings()" class="btn btn-primary w-full">Selesai</button>
            </div>
        </div>
    </div>

    @if (!($book->audio_status === 'completed' && $book->file_audio && $book->file_audio !== 'tts'))
    <script>
        // Poll audio generation progress in real time
        document.addEventListener('DOMContentLoaded', function () {
            const bookId = {{ $book->id }};
            const progressWrap = document.getElementById('audio-gen-progress-wrap');
            if (!progressWrap) return;

            const messageEl = document.getElementById('audio-gen-message');
            const percentEl = document.getElementById('audio-gen-percent');
            const barEl = document.getElementById('audio-gen-bar');
            const statusBadge = document.getElementById('audio-status-badge');

            function updateProgressBar(data) {
                const pct = Math.max(0, Math.min(100, parseInt(data.audio_progress || 0, 10)));
                if (barEl) barEl.style.width = pct + '%';
                if (barEl) barEl.setAttribute('aria-valuenow', String(pct));
                if (barEl) barEl.setAttribute('aria-valuetext', pct + ' persen');
                if (percentEl) percentEl.innerText = pct + '%';
                if (messageEl) messageEl.innerText = data.audio_message || 'Sedang diproses...';
                if (statusBadge) {
                    statusBadge.innerText = 'Memproses (' + pct + '%)';
                    statusBadge.style.color = '#4338ca';
                    statusBadge.style.borderColor = '#4338ca';
                }

                if (data.audio_status === 'completed') {
                    window.location.reload();
                }
                if (data.audio_status === 'failed') {
                    if (messageEl) messageEl.innerText = 'Proses gagal. Silakan coba lagi.';
                    if (percentEl) percentEl.innerText = 'Gagal';
                    if (statusBadge) {
                        statusBadge.innerText = 'Gagal';
                        statusBadge.style.color = '#dc2626';
                        statusBadge.style.borderColor = '#dc2626';
                    }
                }
            }

            function pollProgress() {
                fetch('/audio-progress/' + bookId)
                    .then(function (resp) { return resp.json(); })
                    .then(function (data) {
                        updateProgressBar(data);
                        if (data.audio_status === 'pending' || data.audio_status === 'processing') {
                            setTimeout(pollProgress, 2000);
                        }
                    })
                    .catch(function () {
                        setTimeout(pollProgress, 3000);
                    });
            }

            setTimeout(pollProgress, 1000);
        });

        // Realtime & Localized Upload Time Formatter
        document.addEventListener('DOMContentLoaded', function () {
            const uploadTimeEl = document.getElementById('upload-time');
            if (uploadTimeEl) {
                const utcDateStr = uploadTimeEl.getAttribute('data-utc');
                if (utcDateStr) {
                    const date = new Date(utcDateStr);
                    
                    const options = { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    };
                    
                    const now = new Date();
                    const diffMs = now - date;
                    const diffMins = Math.floor(diffMs / 60000);
                    const diffHours = Math.floor(diffMs / 3600000);
                    
                    let timeString = date.toLocaleDateString('id-ID', options);
                    
                    if (diffMins < 1) {
                        timeString = "Baru saja";
                    } else if (diffMins < 60) {
                        timeString = `${diffMins} menit yang lalu`;
                    } else if (diffHours < 24) {
                        timeString = `${diffHours} jam yang lalu (${date.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})})`;
                    }
                    
                    uploadTimeEl.querySelector('strong').innerText = timeString;
                }
            }
        });

        let chunks = [];
        let currentChunkIndex = 0;
        let isSpeaking = false;
        let isPaused = false;
        let currentUtterance = null;

        function splitLongSentence(sentence, maxLength) {
            const parts = [];
            const fragments = sentence.split(/(?<=[,;:])\s+/);
            let segment = '';
            for (const frag of fragments) {
                const f = frag.trim();
                if (!f) continue;
                if (segment && (segment + ' ' + f).length <= maxLength) {
                    segment += ' ' + f;
                } else {
                    if (segment) parts.push(segment);
                    segment = f;
                }
            }
            if (segment) parts.push(segment);

            const result = [];
            for (const p of parts) {
                if (p.length <= maxLength) {
                    result.push(p);
                    continue;
                }
                let temp = '';
                for (const word of p.split(' ')) {
                    const candidate = (temp + ' ' + word).trim();
                    if (candidate.length <= maxLength) {
                        temp = candidate;
                    } else {
                        if (temp) result.push(temp);
                        temp = word;
                    }
                }
                if (temp) result.push(temp);
            }
            return result;
        }

        function getSpeechChunks(title, description) {
            const MAX_LEN = 180;
            const chunks = [];
            chunks.push({ text: `Membaca judul buku: ${title}.`, pause: 400 });

            if (description) {
                const paragraphs = description.split(/\r?\n/);
                for (const para of paragraphs) {
                    const trimmed = para.trim();
                    if (!trimmed) continue;

                    const sentences = trimmed.split(/(?<=[.!?])\s+/);
                    let buffer = '';

                    for (const s of sentences) {
                        const sentence = s.trim();
                        if (!sentence) continue;

                        if (buffer && (buffer + ' ' + sentence).length <= MAX_LEN) {
                            buffer += ' ' + sentence;
                            continue;
                        }
                        if (buffer) {
                            chunks.push({ text: buffer, pause: 320 });
                            buffer = '';
                        }

                        if (sentence.length <= MAX_LEN) {
                            buffer = sentence;
                        } else {
                            const parts = splitLongSentence(sentence, MAX_LEN);
                            for (let i = 0; i < parts.length; i++) {
                                chunks.push({ text: parts[i], pause: i < parts.length - 1 ? 160 : 320 });
                            }
                        }
                    }

                    if (buffer) {
                        chunks.push({ text: buffer, pause: 550 });
                    }
                }
            }
            return chunks;
        }

        const statusBadge = document.getElementById('audio-status-badge');

        // ── Pengaturan Membaca (gear), Kecepatan TTS, Progres & Lanjut Baca ──
        const bookId = "{{ $book->id }}";
        const progressKey = 'read_assist_progress_' + bookId;
        const TTS_RATES = [0.75, 1, 1.25, 1.5, 2.0];
        let serverSyncedIndex = -1;

        function getSavedTTSRate() {
            const v = parseFloat(localStorage.getItem('read_assist_tts_rate') || '1.0');
            return isNaN(v) ? 1.0 : v;
        }

        function syncRateUI() {
            const rate = getSavedTTSRate();
            const label = document.getElementById('speed-label-show');
            if (label) label.innerText = rate + 'x';
            const sel = document.getElementById('setting-rate-select');
            if (sel) sel.value = String(rate);
        }

        function cycleTTSRate() {
            const idx = TTS_RATES.indexOf(getSavedTTSRate());
            const next = TTS_RATES[(idx + 1) % TTS_RATES.length];
            localStorage.setItem('read_assist_tts_rate', next);
            syncRateUI();
            if (statusBadge) {
                statusBadge.innerText = 'Kecepatan: ' + next + 'x';
                statusBadge.style.color = 'var(--accent-primary)';
                statusBadge.style.borderColor = 'var(--accent-primary)';
                statusBadge.style.background = 'rgba(255, 255, 0, 0.15)';
            }
        }

        function syncSettingsUI() {
            syncRateUI();
            const fontSel = document.getElementById('setting-font-select');
            const font = localStorage.getItem('read_assist_font_size') || 'medium';
            if (fontSel) fontSel.value = font;
            const conSel = document.getElementById('setting-contrast-select');
            const contrast = localStorage.getItem('read_assist_contrast') || 'normal';
            if (conSel) conSel.value = contrast;
        }

        function openReaderSettings() {
            syncSettingsUI();
            const modal = document.getElementById('reader-settings-modal');
            if (modal) {
                modal.style.display = 'flex';
                modal.classList.remove('hidden');
                const first = modal.querySelector('select, button');
                if (first) first.focus();
            }
        }

        function closeReaderSettings() {
            const modal = document.getElementById('reader-settings-modal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            }
            const settingsBtn = document.getElementById('btn-settings-show');
            if (settingsBtn) settingsBtn.focus();
        }

        document.addEventListener('keydown', (e) => {
            const modal = document.getElementById('reader-settings-modal');
            if (!modal || modal.style.display !== 'flex') return;
            if (e.key === 'Escape') {
                e.preventDefault();
                closeReaderSettings();
                return;
            }
            if (e.key === 'Tab') {
                const focusable = modal.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                if (!focusable.length) return;
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (e.shiftKey) {
                    if (document.activeElement === first || document.activeElement === modal) {
                        e.preventDefault();
                        last.focus();
                    }
                } else {
                    if (document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            }
        });

        function setReaderRate(v) {
            localStorage.setItem('read_assist_tts_rate', v);
            syncRateUI();
        }

        function setReaderFont(v) {
            localStorage.setItem('read_assist_font_size', v);
            loadReaderSettings();
        }

        function setReaderContrast(v) {
            localStorage.setItem('read_assist_contrast', v);
            loadReaderSettings();
        }

        function saveLocalProgress(index) {
            localStorage.setItem(progressKey, index);
        }

        function syncProgressToServer(index, completed) {
            if (index === serverSyncedIndex) return;
            serverSyncedIndex = index;
            const csrf = document.querySelector('meta[name="csrf-token"]');
            fetch('/progress/sync/' + bookId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : ''
                },
                body: JSON.stringify({ sentence_index: index, completed: completed || false })
            }).catch(function() {});
        }

        function resumeTTS() {
            if (typeof closeMiniPlayer === 'function') closeMiniPlayer();
            if (currentUtterance) {
                currentUtterance.onend = null;
                currentUtterance.onerror = null;
            }
            window.speechSynthesis.resume();
            window.speechSynthesis.cancel();

            const title = document.getElementById('book-title').innerText;
            const description = document.getElementById('book-description').innerText;
            if (chunks.length === 0) chunks = getSpeechChunks(title, description);

            const saved = parseInt(localStorage.getItem(progressKey) || '0', 10);
            currentChunkIndex = (saved > 0 && saved < chunks.length) ? saved : 0;
            isSpeaking = true;
            isPaused = false;

            const banner = document.getElementById('resume-banner');
            if (banner) {
                banner.style.display = 'none';
                banner.classList.add('hidden');
            }

            if (statusBadge) {
                statusBadge.innerText = 'Melanjutkan...';
                statusBadge.style.color = 'var(--accent-primary)';
                statusBadge.style.borderColor = 'var(--accent-primary)';
                statusBadge.style.background = 'rgba(255, 255, 0, 0.15)';
            }
            speakNext();
            updateAudioButtons();
        }

        function showResumeBanner(saved) {
            const banner = document.getElementById('resume-banner');
            const textEl = document.getElementById('resume-banner-text');
            if (!banner || !textEl) return;
            if (!(saved > 0 && saved < chunks.length)) return;
            textEl.innerText = 'Lanjut dari kalimat ' + (saved + 1) + ' dari ' + chunks.length + '?';
            banner.style.display = 'flex';
            banner.classList.remove('hidden');
        }

        function updateAudioButtons() {
            const playBtn = document.getElementById('btn-play-show');
            const pauseBtn = document.getElementById('btn-pause-show');

            if (isSpeaking && !isPaused) {
                if (playBtn) playBtn.style.display = 'none';
                if (pauseBtn) pauseBtn.style.display = 'flex';
            } else {
                if (playBtn) {
                    playBtn.style.display = 'flex';
                    playBtn.innerHTML = '<svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>';
                }
                if (pauseBtn) pauseBtn.style.display = 'none';
            }
        }

        function startTTS() {
            if (typeof closeMiniPlayer === 'function') {
                closeMiniPlayer();
            }

            if (currentUtterance) {
                currentUtterance.onend = null;
                currentUtterance.onerror = null;
            }
            window.speechSynthesis.resume();
            window.speechSynthesis.cancel();

            const title = document.getElementById('book-title').innerText;
            const description = document.getElementById('book-description').innerText;
            chunks = getSpeechChunks(title, description);
            currentChunkIndex = 0;
            isSpeaking = true;
            isPaused = false;

            localStorage.removeItem(progressKey);
            serverSyncedIndex = 0;
            syncProgressToServer(0, false);

            const banner = document.getElementById('resume-banner');
            if (banner) {
                banner.style.display = 'none';
                banner.classList.add('hidden');
            }

            if (statusBadge) {
                statusBadge.innerText = "Memulai...";
                statusBadge.style.color = "var(--accent-primary)";
                statusBadge.style.borderColor = "var(--accent-primary)";
                statusBadge.style.background = "rgba(184, 134, 11, 0.15)";
            }

            speakNext();
            updateAudioButtons();
        }

        function playTTS() {
            if (isSpeaking && !isPaused) return;

            if (typeof closeMiniPlayer === 'function') {
                closeMiniPlayer();
            }

            if (currentUtterance) {
                currentUtterance.onend = null;
                currentUtterance.onerror = null;
            }
            window.speechSynthesis.resume();
            window.speechSynthesis.cancel();

            if (chunks.length === 0) {
                const title = document.getElementById('book-title').innerText;
                const description = document.getElementById('book-description').innerText;
                chunks = getSpeechChunks(title, description);
            }

            if (currentChunkIndex >= chunks.length) {
                currentChunkIndex = 0;
            }
            isSpeaking = true;
            isPaused = false;

            if (statusBadge) {
                statusBadge.innerText = "Memulai...";
                statusBadge.style.color = "var(--accent-primary)";
                statusBadge.style.borderColor = "var(--accent-primary)";
                statusBadge.style.background = "rgba(184, 134, 11, 0.15)";
            }

            speakNext();
            updateAudioButtons();
        }

        function pauseTTS() {
            if (!isSpeaking || isPaused) return;
            isPaused = true;
            if (currentUtterance) {
                currentUtterance.onend = null;
                currentUtterance.onerror = null;
            }
            window.speechSynthesis.resume();
            window.speechSynthesis.cancel();

            if (statusBadge) {
                statusBadge.innerText = "Dijeda";
                statusBadge.style.color = "var(--text-muted)";
                statusBadge.style.borderColor = "var(--border-glass)";
                statusBadge.style.background = "rgba(255, 255, 255, 0.05)";
            }
            updateAudioButtons();
        }

        function speakNext() {
            if (!isSpeaking) return;

            if (currentChunkIndex >= chunks.length) {
                isSpeaking = false;
                currentChunkIndex = 0;
                updateAudioButtons();
                localStorage.removeItem(progressKey);
                syncProgressToServer(0, true);

                if (statusBadge) {
                    statusBadge.innerText = "Selesai";
                    statusBadge.style.color = "var(--text-secondary)";
                    statusBadge.style.borderColor = "var(--border-glass)";
                    statusBadge.style.background = "rgba(255, 255, 255, 0.05)";
                }
                return;
            }

            const chunk = chunks[currentChunkIndex];
            const text = typeof chunk === 'string' ? chunk : chunk.text;
            const pause = (chunk && typeof chunk === 'object' && chunk.pause) ? chunk.pause : 300;

            saveLocalProgress(currentChunkIndex);
            syncProgressToServer(currentChunkIndex, false);

            if (statusBadge) {
                statusBadge.innerText = `Memutar (${currentChunkIndex + 1}/${chunks.length})`;
                statusBadge.style.color = "var(--accent-success)";
                statusBadge.style.borderColor = "var(--accent-success)";
                statusBadge.style.background = "rgba(16, 185, 129, 0.15)";
            }

            currentUtterance = new SpeechSynthesisUtterance(text);
            currentUtterance.lang = 'id-ID';

            const savedRate = localStorage.getItem('read_assist_tts_rate') || '1.0';
            currentUtterance.rate = parseFloat(savedRate);

            const voices = window.speechSynthesis.getVoices();
            const idVoice = voices.find(voice => voice.lang.includes('id') || voice.lang.includes('ID'));
            if (idVoice) {
                currentUtterance.voice = idVoice;
            }

            currentUtterance.onstart = function() {
                updateAudioButtons();
            };

            currentUtterance.onend = function() {
                if (isSpeaking && !isPaused) {
                    currentChunkIndex++;
                    setTimeout(function() { speakNext(); }, pause);
                }
            };

            currentUtterance.onerror = function(e) {
                console.error(e);
                if (e.error === 'not-allowed') {
                    isSpeaking = false;
                    isPaused = false;
                    updateAudioButtons();
                    return;
                }
                if (e.error === 'interrupted' || e.error === 'canceled') {
                    return;
                }
                if (isSpeaking && !isPaused) {
                    currentChunkIndex++;
                    speakNext();
                }
            };

            window.speechSynthesis.resume();
            window.speechSynthesis.speak(currentUtterance);
        }

        function stopTTS() {
            isSpeaking = false;
            isPaused = false;
            if (currentUtterance) {
                currentUtterance.onend = null;
                currentUtterance.onerror = null;
            }
            window.speechSynthesis.resume();
            window.speechSynthesis.cancel();
            currentUtterance = null;
            chunks = [];
            currentChunkIndex = 0;
            updateAudioButtons();
            localStorage.removeItem(progressKey);
            serverSyncedIndex = 0;
            syncProgressToServer(0, false);

            if (statusBadge) {
                statusBadge.innerText = 'Browser TTS';
                statusBadge.style.color = '';
                statusBadge.style.borderColor = '';
                statusBadge.style.background = '';
            }
        }

        function prevTTS() {
            if (chunks.length === 0) return;
            if (currentUtterance) {
                currentUtterance.onend = null;
                currentUtterance.onerror = null;
            }
            window.speechSynthesis.resume();
            window.speechSynthesis.cancel();
            if (currentChunkIndex > 0) {
                currentChunkIndex--;
            }
            isSpeaking = true;
            isPaused = false;
            const banner = document.getElementById('resume-banner');
            if (banner) {
                banner.style.display = 'none';
                banner.classList.add('hidden');
            }
            speakNext();
            updateAudioButtons();
        }

        function nextTTS() {
            if (chunks.length === 0) return;
            if (currentChunkIndex >= chunks.length - 1) {
                stopTTS();
                return;
            }
            if (currentUtterance) {
                currentUtterance.onend = null;
                currentUtterance.onerror = null;
            }
            window.speechSynthesis.resume();
            window.speechSynthesis.cancel();
            currentChunkIndex++;
            isSpeaking = true;
            isPaused = false;
            const banner = document.getElementById('resume-banner');
            if (banner) {
                banner.style.display = 'none';
                banner.classList.add('hidden');
            }
            speakNext();
            updateAudioButtons();
        }

        function printQR() {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Cetak QR - {{ $book->judul }}</title>
                    <style>
                        body { font-family: system-ui, -apple-system, sans-serif; text-align: center; padding: 40px; color: #000; background: #fff; }
                        .container { border: 3px dashed #b8860b; padding: 30px; display: inline-block; border-radius: 15px; max-width: 350px; }
                        h2 { margin: 0 0 10px 0; font-size: 1.5rem; color: #000; }
                        p { margin: 0 0 20px 0; font-size: 0.95rem; color: #4b5563; }
                        img { width: 220px; height: 220px; display: block; margin: 0 auto; border: 1px solid #e5e7eb; padding: 8px; border-radius: 8px; }
                        .footer { margin-top: 20px; font-size: 0.8rem; border-top: 1px solid #e5e7eb; padding-top: 15px; font-weight: bold; color: #7a5a00; letter-spacing: 1px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h2>{{ $book->judul }}</h2>
                        <img src="${document.getElementById('qr-code-img').src}" />
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

        function downloadQR() {
            const qrImg = document.getElementById('qr-code-img');
            fetch(qrImg.src)
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = "{{ \Illuminate\Support\Str::slug($book->judul) }}_qr_code.svg";
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                })
                .catch(err => {
                    window.open(qrImg.src, '_blank');
                });
        }

        function updateRelativeTimes() {
            // Update upload time (realtime formatting)
            const uploadEl = document.getElementById('upload-time');
            if (uploadEl) {
                const utcStr = uploadEl.getAttribute('data-utc');
                if (utcStr) {
                    const date = new Date(utcStr);
                    const formatted = date.toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    uploadEl.querySelector('strong').innerText = formatted;
                }
            }

            // Update relative update time
            const updateEl = document.getElementById('update-time-relative');
            if (updateEl) {
                const utcStr = updateEl.getAttribute('data-utc-updated');
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
                    updateEl.querySelector('strong').innerText = relativeText;
                }
            }
        }

        function loadReaderSettings() {
            const rate = localStorage.getItem('read_assist_tts_rate') || '1.0';
            const fontSize = localStorage.getItem('read_assist_font_size') || 'medium';
            const contrast = localStorage.getItem('read_assist_contrast') || 'normal';

            applyReaderSettings(rate, fontSize, contrast);
        }

        function applyReaderSettings(rate, fontSize, contrast) {
            // 1. Font Size
            const descEl = document.getElementById('display-description');
            if (descEl) {
                descEl.className = 'leading-relaxed whitespace-pre-line transition-all duration-200';
                if (fontSize === 'small') {
                    descEl.classList.add('text-xs');
                } else if (fontSize === 'medium') {
                    descEl.classList.add('text-sm');
                } else if (fontSize === 'large') {
                    descEl.classList.add('text-lg');
                } else if (fontSize === 'xlarge') {
                    descEl.classList.add('text-2xl');
                }
            }

            // 2. Contrast
            const containerEl = document.getElementById('reader-main-card');
            if (containerEl) {
                containerEl.classList.remove('high-contrast-mode');
                containerEl.classList.remove('light-mode');
                if (contrast === 'high-contrast') {
                    containerEl.classList.add('high-contrast-mode');
                } else {
                    // normal & light sama-sama putih-kuning (default)
                    containerEl.classList.add('light-mode');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateRelativeTimes();
            setInterval(updateRelativeTimes, 15000);
            loadReaderSettings();

            // Resume: gabungkan progres lokal (perangkat) dengan progres akun (server)
            const titleEl = document.getElementById('book-title');
            const descElHidden = document.getElementById('book-description');
            if (titleEl && descElHidden) {
                chunks = getSpeechChunks(titleEl.innerText, descElHidden.innerText);
                const localSaved = parseInt(localStorage.getItem(progressKey) || '0', 10);

                fetch('/progress/' + bookId)
                    .then(function(resp) { return resp.json(); })
                    .then(function(data) {
                        if (data && data.completed) {
                            localStorage.removeItem(progressKey);
                            return;
                        }
                        const serverSaved = (data && typeof data.sentence_index === 'number') ? data.sentence_index : 0;
                        if (serverSaved > 0 && serverSaved > localSaved) {
                            localStorage.setItem(progressKey, serverSaved);
                            serverSyncedIndex = serverSaved;
                            showResumeBanner(serverSaved);
                        } else if (localSaved > 0) {
                            serverSyncedIndex = localSaved;
                            showResumeBanner(localSaved);
                        }
                    })
                    .catch(function() {
                        if (localSaved > 0) {
                            serverSyncedIndex = localSaved;
                            showResumeBanner(localSaved);
                        }
                    });
            }
        });

        // Clean up when leaving page
        window.addEventListener('beforeunload', () => {
            window.speechSynthesis.resume();
            window.speechSynthesis.cancel();
        });
    </script>
    @endif
@endsection
