@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto w-full">
    <!-- Book Header -->
    <div class="card border shadow-sm p-4 sm:p-6 mb-5" style="background: #ffffff; border-color: rgba(0, 0, 0, 0.12);">
        <div class="flex gap-4 sm:gap-6 items-start">
            <!-- Cover -->
            <div class="w-[90px] h-[125px] sm:w-[130px] sm:h-[180px] rounded-xl overflow-hidden shrink-0 shadow-lg border border-white/10">
                @if ($audioBook->cover)
                    <img src="/storage/{{ $audioBook->cover }}" alt="Cover {{ $audioBook->judul }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center p-2 sm:p-4 text-center" style="background: #ffffff;">
                        <span class="text-[0.6rem] sm:text-xs font-bold text-black line-clamp-3 leading-relaxed">{{ $audioBook->judul }}</span>
                    </div>
                @endif
            </div>

            <!-- Title & Meta -->
            <div class="flex-1 min-w-0">
                <h1 id="book-title" class="text-gradient text-lg sm:text-3xl font-bold leading-tight mb-1 sm:mb-2">
                    {{ $audioBook->judul }}
                </h1>
                <div class="text-xs sm:text-sm text-slate-600 flex flex-col gap-0.5 sm:gap-1">
                    <span>Kategori: <strong>{{ $audioBook->kategori ?: 'Umum' }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 mt-4 sm:mt-6">
            <div class="bg-black/5 border border-black/10 rounded-lg p-2 sm:p-3 text-center">
                <span class="text-[0.6rem] sm:text-xs text-slate-600 uppercase font-bold block mb-0.5">Karakter</span>
                <span class="text-sm sm:text-lg font-bold text-blue-600">{{ strlen($audioBook->deskripsi) }}</span>
            </div>
            <div class="bg-black/5 border border-black/10 rounded-lg p-2 sm:p-3 text-center">
                <span class="text-[0.6rem] sm:text-xs text-slate-600 uppercase font-bold block mb-0.5">Jumlah Kata</span>
                <span class="text-sm sm:text-lg font-bold text-indigo-600">{{ str_word_count($audioBook->deskripsi) }}</span>
            </div>
            <div class="bg-black/5 border border-black/10 rounded-lg p-2 sm:p-3 text-center">
                <span class="text-[0.6rem] sm:text-xs text-slate-600 uppercase font-bold block mb-0.5">Estimasi Baca</span>
                <span class="text-sm sm:text-lg font-bold text-emerald-600">{{ ceil(str_word_count($audioBook->deskripsi) / 150) }} Menit</span>
            </div>
            <div class="bg-black/5 border border-black/10 rounded-lg p-2 sm:p-3 text-center">
                <span class="text-[0.6rem] sm:text-xs text-slate-600 uppercase font-bold block mb-0.5">Status Audio</span>
                <span class="text-[0.65rem] sm:text-base font-bold text-emerald-600 block mt-0.5 sm:mt-1">Siap</span>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="card border shadow-sm p-4 sm:p-6 mb-5" style="background: #ffffff; border-color: rgba(0, 0, 0, 0.12);">
        <h2 class="text-xs sm:text-base text-black font-semibold mb-2">Deskripsi Buku</h2>
        <div class="bg-black/5 border border-black/10 rounded-xl p-3 sm:p-5">
            <p id="display-description-play" class="text-slate-700 leading-relaxed text-xs sm:text-sm whitespace-pre-line">
                {{ isset($audioBook->deskripsi) ? \Illuminate\Support\Str::limit($audioBook->deskripsi, 300) : 'Tidak ada deskripsi.' }}
            </p>
            <div id="book-description" class="hidden">{{ $audioBook->deskripsi ?? 'Tidak ada deskripsi.' }}</div>
        </div>
    </div>

    @if ($audioBook->audio_status === 'completed' && $audioBook->file_audio && $audioBook->file_audio !== 'tts')
        <!-- Generated MP3 Player -->
        <div class="card border shadow-sm p-4 sm:p-6 text-center mb-5" style="background: #ffffff; border-color: rgba(0, 0, 0, 0.12);">
            <h2 class="text-sm sm:text-base text-black mb-3">Dengarkan Audio</h2>
            <audio id="generated-audio-player" controls class="w-full max-w-md mx-auto">
                <source src="{{ route('audio.stream', $audioBook) }}" type="audio/mpeg">
                Browser Anda tidak mendukung pemutar audio.
            </audio>
            <div class="flex items-center justify-center gap-3 mt-3">
                <button type="button" onclick="skipGeneratedAudio(-10)" title="Mundur 10 detik" aria-label="Mundur 10 detik" class="flex items-center justify-center w-11 h-11 sm:w-12 sm:h-12 rounded-full border-2 border-slate-300 text-slate-700 hover:border-indigo-500 hover:text-indigo-600 active:scale-90 transition-all">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/><path d="M10.29 16.38h-1.1v-3.82l-1.07.6v-1.01l2.17-1.32v5.55z"/><path d="M13.42 10.98c1.7 0 3.03 1.34 3.03 3 0 1.66-1.33 3-3.03 3-1.7 0-3.03-1.34-3.03-3 0-1.66 1.33-3 3.03-3zm0 4.83c.83 0 1.5-.6 1.5-1.83 0-1.23-.67-1.83-1.5-1.83s-1.5.6-1.5 1.83c0 1.23.67 1.83 1.5 1.83z"/></svg>
                </button>
                <button type="button" onclick="skipGeneratedAudio(10)" title="Maju 10 detik" aria-label="Maju 10 detik" class="flex items-center justify-center w-11 h-11 sm:w-12 sm:h-12 rounded-full border-2 border-slate-300 text-slate-700 hover:border-indigo-500 hover:text-indigo-600 active:scale-90 transition-all">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M18 13c0 3.31-2.69 6-6 6s-6-2.69-6-6 2.69-6 6-6v4l5-5-5-5v4c-4.42 0-8 3.58-8 8s3.58 8 8 8 8-3.58 8-8h-2z"/><path d="M10.29 16.38h-1.1v-3.82l-1.07.6v-1.01l2.17-1.32v5.55z"/><path d="M13.42 10.98c1.7 0 3.03 1.34 3.03 3 0 1.66-1.33 3-3.03 3-1.7 0-3.03-1.34-3.03-3 0-1.66 1.33-3 3.03-3zm0 4.83c.83 0 1.5-.6 1.5-1.83 0-1.23-.67-1.83-1.5-1.83s-1.5.6-1.5 1.83c0 1.23.67 1.83 1.5 1.83z"/></svg>
                </button>
            </div>
            <div style="margin-top: 0.2rem;">
                <a href="{{ route('audio.stream', $audioBook) }}" download class="text-indigo-600 underline text-sm">Download MP3</a>
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
        <!-- TTS Player Section -->
        <div id="play-reader-card" class="card border shadow-sm p-4 sm:p-6 text-center mb-5" style="background: #121316; border-color: rgba(255, 255, 255, 0.08);">
            <h2 class="text-sm sm:text-base mb-1">Pemutar Audio (TTS)</h2>
            <p class="text-xs text-slate-400 mb-4">Kalimat demi kalimat — progress tersimpan otomatis</p>

            <!-- Status Badge -->
            <div class="flex justify-center items-center gap-2 mb-4">
                <span class="text-xs text-slate-400">Status:</span>
                <span id="audio-status-badge" class="badge badge-sm text-xs bg-black/5 text-black border border-black/10">Browser TTS</span>
            </div>

            @if (in_array($audioBook->audio_status, ['pending', 'processing']))
            <div id="audio-gen-progress-wrap" class="w-full max-w-md mx-auto mb-4" role="group" aria-label="Progres pembuatan audio">
                <div class="flex justify-between items-center text-xs mb-1">
                    <span id="audio-gen-message" class="text-slate-400">Menunggu antrian...</span>
                    <span id="audio-gen-percent" class="font-bold text-[#ffff00]">0%</span>
                </div>
                <div class="w-full bg-white/10 rounded-full h-2.5 overflow-hidden" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-valuetext="0 persen" aria-label="Progres pembuatan audio">
                    <div id="audio-gen-bar" class="bg-[#ffff00] h-2.5 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
            </div>
            @endif

            <style>
                #play-reader-card.high-contrast-mode {
                    background: #000000 !important;
                    color: #ffff00 !important;
                    border-color: #ffff00 !important;
                }
                #play-reader-card.high-contrast-mode h1,
                #play-reader-card.high-contrast-mode h2,
                #play-reader-card.high-contrast-mode h3,
                #play-reader-card.high-contrast-mode h4,
                #play-reader-card.high-contrast-mode p,
                #play-reader-card.high-contrast-mode span,
                #play-reader-card.high-contrast-mode label,
                #play-reader-card.high-contrast-mode strong,
                #play-reader-card.high-contrast-mode a {
                    color: #ffff00 !important;
                }
                #play-reader-card.high-contrast-mode button,
                #play-reader-card.high-contrast-mode select,
                #play-reader-card.high-contrast-mode input,
                #play-reader-card.high-contrast-mode textarea,
                #play-reader-card.high-contrast-mode .btn,
                #play-reader-card.high-contrast-mode a.btn {
                    background: #000 !important;
                    color: #ffff00 !important;
                    border: 2px solid #ffff00 !important;
                }
                #play-reader-card.high-contrast-mode #btn-play {
                    background: #ffff00 !important;
                    color: #000000 !important;
                    border: 2px solid #ffff00 !important;
                }
                #play-reader-card.light-mode {
                    background: #ffffff !important;
                    color: #000000 !important;
                    border-color: #000000 !important;
                }
                #play-reader-card.light-mode h1,
                #play-reader-card.light-mode h2,
                #play-reader-card.light-mode h3,
                #play-reader-card.light-mode h4,
                #play-reader-card.light-mode p,
                #play-reader-card.light-mode span,
                #play-reader-card.light-mode label,
                #play-reader-card.light-mode strong,
                #play-reader-card.light-mode a {
                    color: #000000 !important;
                }
                #play-reader-card.light-mode button {
                    background: #ffffff !important;
                    color: #000000 !important;
                    border: 2px solid #000000 !important;
                }
                #play-reader-card.light-mode #btn-play {
                    background: #b8860b !important;
                    color: #ffffff !important;
                    border: 2px solid #000000 !important;
                }
            </style>

            <!-- Hidden full description source -->
            <p id="book-description-tts" class="hidden">{{ $audioBook->deskripsi ?? 'Tidak ada deskripsi.' }}</p>

            <!-- Wave Animation -->
            <div class="wave-animation paused mb-3" id="wave-animation"></div>

            <!-- Subtitles -->
            <div id="subtitles-card" class="bg-slate-900/60 border border-white/10 rounded-xl p-3 sm:p-4 text-center mb-4 min-h-[60px] flex flex-col items-center justify-center" style="display: none;">
                <p id="current-spoken-text" class="text-sm sm:text-lg font-medium text-white leading-relaxed m-0" aria-live="polite" aria-atomic="true"></p>
            </div>

            <!-- Progress -->
            <div id="status-message" class="text-xs text-slate-400 mb-4 min-h-[1.2em]" role="status" aria-live="polite" aria-atomic="true">
                Ketuk play untuk memulai
            </div>

            <!-- Controls -->
            <div class="flex items-center justify-center gap-3 sm:gap-5 mb-3">
                <button id="btn-start" onclick="startTTS()" class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-full text-slate-400 hover:text-white active:scale-90 transition-all" title="Mulai dari Awal" aria-label="Mulai dari Awal" style="background: rgba(255,255,255,0.05);">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                </button>

                <button id="btn-prev" onclick="prevTTS()" class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-full text-slate-400 hover:text-white active:scale-90 transition-all" title="Sebelumnya" style="background: rgba(255,255,255,0.05);">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h2v12H6zm3.5 6 8.5 6V6z"/></svg>
                </button>

                <button id="btn-play" onclick="playTTS()" class="flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-[#ffff00] text-black shadow-2xl shadow-black/60 active:scale-90 transition-all duration-150" title="Lanjutkan" aria-label="Putar atau lanjutkan">
                    <svg id="play-icon" class="w-7 h-7 sm:w-8 sm:h-8" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                </button>

                <button id="btn-pause" onclick="pauseTTS()" class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-full text-slate-400 hover:text-white active:scale-90 transition-all" title="Jeda" aria-label="Jeda" style="background: rgba(255,255,255,0.05); display: none;">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                </button>

                <button id="btn-next" onclick="nextTTS()" class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-full text-slate-400 hover:text-white active:scale-90 transition-all" title="Selanjutnya" aria-label="Kalimat selanjutnya" style="background: rgba(255,255,255,0.05);">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg>
                </button>
            </div>

            <!-- Speed & Settings -->
            <div class="flex items-center justify-center gap-2 sm:gap-3 mb-3">
                <button id="btn-speed" onclick="cycleTTSRate()" class="flex items-center gap-1.5 text-xs text-white border-2 border-white rounded-full px-4 py-2 hover:border-[#ffff00] active:scale-95 transition-all" title="Ubah kecepatan suara">
                    Kecepatan: <strong id="speed-label">1.0x</strong>
                </button>
                <button id="btn-settings" onclick="openReaderSettings()" class="flex items-center gap-1.5 text-xs text-white border-2 border-white rounded-full px-4 py-2 hover:border-[#ffff00] active:scale-95 transition-all" title="Pengaturan Membaca">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                    Pengaturan
                </button>
                <button id="btn-voice" onclick="toggleVoiceCommand()" class="flex items-center gap-1.5 text-xs text-white border-2 border-white rounded-full px-4 py-2 hover:border-[#ffff00] active:scale-95 transition-all" title="Kontrol Suara (perintah via mikrofon)" aria-label="Aktifkan kontrol suara" aria-pressed="false">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
                    Kontrol Suara
                </button>
            </div>

            <!-- Voice Command Hint -->
            <p id="voice-hint" class="text-[0.65rem] text-slate-500 mt-1 mb-3" role="status" aria-live="polite">
                Perintah suara: "putar", "jeda", "lanjut", "ulang", "berhenti", "kecepatan", "pengaturan"
            </p>

            <!-- Stop -->
            <button id="btn-stop" onclick="stopTTS()" class="flex items-center gap-1.5 text-xs text-slate-500 hover:text-red-400 active:scale-95 transition-all mx-auto" title="Berhenti" style="display: none;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h12v12H6z"/></svg>
                Berhenti & Reset
            </button>
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

@if (!($audioBook->audio_status === 'completed' && $audioBook->file_audio && $audioBook->file_audio !== 'tts'))
<script>
    const bookId = "{{ $audioBook->id }}";

    // Poll audio generation progress in real time
    (function pollAudioProgress() {
        const progressWrap = document.getElementById('audio-gen-progress-wrap');
        if (!progressWrap) return;
        fetch('/audio-progress/' + bookId)
            .then(function (resp) { return resp.json(); })
            .then(function (data) {
                const messageEl = document.getElementById('audio-gen-message');
                const percentEl = document.getElementById('audio-gen-percent');
                const barEl = document.getElementById('audio-gen-bar');
                const pct = Math.max(0, Math.min(100, parseInt(data.audio_progress || 0, 10)));
                if (barEl) barEl.style.width = pct + '%';
                if (percentEl) percentEl.innerText = pct + '%';
                if (messageEl) messageEl.innerText = data.audio_message || 'Sedang diproses...';
                if (data.audio_status === 'completed') {
                    window.location.reload();
                    return;
                }
                if (data.audio_status === 'pending' || data.audio_status === 'processing') {
                    setTimeout(pollAudioProgress, 2000);
                }
            })
            .catch(function () { setTimeout(pollAudioProgress, 3000); });
    })();

    let chunks = [];
    let currentChunkIndex = parseInt(localStorage.getItem('read_assist_progress_' + bookId) || '0', 10);
    let isSpeaking = false;
    let isPaused = false;
    let currentUtterance = null;

    const btnStart = document.getElementById('btn-start');
    const btnPlay = document.getElementById('btn-play');
    const btnPause = document.getElementById('btn-pause');
    const btnStop = document.getElementById('btn-stop');
    const statusMessage = document.getElementById('status-message');
    const subtitlesCard = document.getElementById('subtitles-card');
    const currentSpokenText = document.getElementById('current-spoken-text');
    const waveAnimation = document.getElementById('wave-animation');
    const statusBadge = document.getElementById('audio-status-badge');

    // ── Pengaturan Membaca (gear), Kecepatan TTS, & Sinkronisasi Progres ──
    const progressKey = 'read_assist_progress_' + bookId;
    const TTS_RATES = [0.75, 1, 1.25, 1.5, 2.0];
    let serverSyncedIndex = -1;

    function getSavedTTSRate() {
        const v = parseFloat(localStorage.getItem('read_assist_tts_rate') || '1.0');
        return isNaN(v) ? 1.0 : v;
    }

    function syncRateUI() {
        const rate = getSavedTTSRate();
        const label = document.getElementById('speed-label');
        if (label) label.innerText = rate + 'x';
        const sel = document.getElementById('setting-rate-select');
        if (sel) sel.value = String(rate);
    }

    function cycleTTSRate() {
        const idx = TTS_RATES.indexOf(getSavedTTSRate());
        const next = TTS_RATES[(idx + 1) % TTS_RATES.length];
        localStorage.setItem('read_assist_tts_rate', next);
        syncRateUI();
        if (statusMessage) statusMessage.innerText = 'Kecepatan: ' + next + 'x';
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
        const settingsBtn = document.getElementById('btn-settings');
        if (settingsBtn) settingsBtn.focus();
    }

    function setReaderRate(v) {
        localStorage.setItem('read_assist_tts_rate', v);
        syncRateUI();
    }

    function setReaderFont(v) {
        localStorage.setItem('read_assist_font_size', v);
        applyReaderSettings();
    }

    function setReaderContrast(v) {
        localStorage.setItem('read_assist_contrast', v);
        applyReaderSettings();
    }

    function applyReaderSettings() {
        const fontSize = localStorage.getItem('read_assist_font_size') || 'medium';
        const descEl = document.getElementById('display-description-play');
        if (descEl) {
            const sizes = { small: '0.75rem', medium: '0.875rem', large: '1.125rem', xlarge: '1.5rem' };
            descEl.style.fontSize = sizes[fontSize] || '0.875rem';
        }
        const contrast = localStorage.getItem('read_assist_contrast') || 'normal';
        const cardEl = document.getElementById('play-reader-card');
        if (cardEl) {
            cardEl.classList.remove('high-contrast-mode');
            cardEl.classList.remove('light-mode');
            if (contrast === 'high-contrast') {
                cardEl.classList.add('high-contrast-mode');
            } else {
                // normal & light sama-sama putih-kuning (default)
                cardEl.classList.add('light-mode');
            }
        }
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
        chunks.push({ text: `Membaca buku: ${title}.`, pause: 400 });
        if (!description) return chunks;

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

        return chunks;
    }

    function updateUI() {
        if (isSpeaking && !isPaused) {
            if (waveAnimation) waveAnimation.classList.remove('paused');
        } else {
            if (waveAnimation) waveAnimation.classList.add('paused');
        }

        if (btnStart) btnStart.style.display = 'flex';

        if (isSpeaking) {
            if (btnStop) btnStop.style.display = 'flex';
            if (subtitlesCard) subtitlesCard.style.display = 'flex';

            if (isPaused) {
                if (btnPlay) btnPlay.style.display = 'flex';
                if (btnPause) btnPause.style.display = 'none';
                if (statusMessage) statusMessage.innerText = 'Dijeda';
                if (statusBadge) {
                    statusBadge.innerText = 'Dijeda';
                    statusBadge.style.color = 'var(--text-muted)';
                    statusBadge.style.borderColor = 'var(--border-glass)';
                }
            } else {
                if (btnPlay) btnPlay.style.display = 'none';
                if (btnPause) btnPause.style.display = 'flex';
                if (statusMessage) statusMessage.innerText = `Kalimat ${currentChunkIndex + 1} dari ${chunks.length}`;
                if (statusBadge) {
                    statusBadge.innerText = `Memutar (${currentChunkIndex + 1}/${chunks.length})`;
                    statusBadge.style.color = 'var(--accent-success)';
                    statusBadge.style.borderColor = 'var(--accent-success)';
                }
            }
        } else {
            if (btnPlay) btnPlay.style.display = 'flex';
            if (btnPause) btnPause.style.display = 'none';
            if (btnStop) btnStop.style.display = 'none';
            if (subtitlesCard) subtitlesCard.style.display = 'none';

            if (chunks.length > 0 && currentChunkIndex >= chunks.length) {
                if (statusMessage) statusMessage.innerText = 'Selesai — seluruh buku telah dibacakan';
                if (statusBadge) {
                    statusBadge.innerText = 'Selesai';
                    statusBadge.style.color = 'var(--text-secondary)';
                    statusBadge.style.borderColor = 'var(--border-glass)';
                }
            } else {
                if (statusMessage) statusMessage.innerText = 'Ketuk play untuk memulai';
                if (statusBadge) {
                    statusBadge.innerText = 'Browser TTS';
                    statusBadge.style.color = '';
                    statusBadge.style.borderColor = '';
                }
            }
        }
    }

    function startTTS() {
        if (currentUtterance) {
            currentUtterance.onend = null;
            currentUtterance.onerror = null;
        }
        window.speechSynthesis.resume();
        window.speechSynthesis.cancel();

        const title = document.getElementById('book-title').innerText;
        const description = document.getElementById('book-description').innerText;
        if (chunks.length === 0) chunks = getSpeechChunks(title, description);

        currentChunkIndex = 0;
        localStorage.removeItem('read_assist_progress_' + bookId);
        serverSyncedIndex = 0;
        syncProgressToServer(0, false);
        isSpeaking = true;
        isPaused = false;

        if (statusBadge) {
            statusBadge.innerText = 'Memulai...';
            statusBadge.style.color = 'var(--accent-primary)';
            statusBadge.style.borderColor = 'var(--accent-primary)';
        }
        speakNext(true);
    }

    function playTTS() {
        if (isSpeaking && !isPaused) return; // Prevent double trigger

        if (currentUtterance) {
            currentUtterance.onend = null;
            currentUtterance.onerror = null;
        }
        window.speechSynthesis.resume();
        window.speechSynthesis.cancel();
        
        const title = document.getElementById('book-title').innerText;
        const description = document.getElementById('book-description').innerText;
        if (chunks.length === 0) chunks = getSpeechChunks(title, description);
        
        isSpeaking = true;
        isPaused = false;
        
        if (statusBadge) {
            statusBadge.innerText = 'Memulai...';
            statusBadge.style.color = 'var(--accent-primary)';
            statusBadge.style.borderColor = 'var(--accent-primary)';
        }
        speakNext(true); // Is first chunk (synchronous)
    }

    function speakNext(isFirst = false) {
        if (!isSpeaking) return;
        if (currentChunkIndex >= chunks.length) {
            isSpeaking = false;
            currentChunkIndex = 0;
            localStorage.removeItem('read_assist_progress_' + bookId);
            syncProgressToServer(0, true);
            updateUI();
            return;
        }

        const chunk = chunks[currentChunkIndex];
        const text = typeof chunk === 'string' ? chunk : chunk.text;
        const pause = (chunk && typeof chunk === 'object' && chunk.pause) ? chunk.pause : 300;
        if (currentSpokenText) currentSpokenText.innerText = text;
        localStorage.setItem('read_assist_progress_' + bookId, currentChunkIndex);
        syncProgressToServer(currentChunkIndex, false);

        if (currentUtterance) {
            currentUtterance.onend = null;
            currentUtterance.onerror = null;
        }

        currentUtterance = new SpeechSynthesisUtterance(text);
        currentUtterance.lang = 'id-ID';
        currentUtterance.rate = parseFloat(localStorage.getItem('read_assist_tts_rate') || '1.0');
        
        const voices = window.speechSynthesis.getVoices();
        const idVoice = voices.find(v => v.lang.includes('id') || v.lang.includes('ID'));
        if (idVoice) currentUtterance.voice = idVoice;
        
        currentUtterance.onstart = () => updateUI();
        currentUtterance.onend = () => {
            if (isSpeaking && !isPaused) {
                currentChunkIndex++;
                setTimeout(() => speakNext(false), pause);
            }
        };
        currentUtterance.onerror = (e) => {
            console.error('TTS error:', e);
            if (e.error === 'not-allowed') {
                isSpeaking = false;
                isPaused = false;
                updateUI();
                return;
            }
            if (e.error === 'interrupted' || e.error === 'canceled') {
                return;
            }
            if (isSpeaking && !isPaused) {
                currentChunkIndex++;
                speakNext(false);
            }
        };

        window.speechSynthesis.resume();
        if (isFirst) {
            window.speechSynthesis.speak(currentUtterance);
        } else {
            setTimeout(() => {
                if (isSpeaking && !isPaused) {
                    window.speechSynthesis.resume();
                    window.speechSynthesis.speak(currentUtterance);
                }
            }, 50);
        }
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
        updateUI();
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
        currentChunkIndex = 0;
        localStorage.removeItem('read_assist_progress_' + bookId);
        serverSyncedIndex = 0;
        syncProgressToServer(0, false);
        updateUI();
    }

    function prevTTS() {
        if (chunks.length === 0) return;
        window.speechSynthesis.resume();
        window.speechSynthesis.cancel();
        if (currentChunkIndex > 0) {
            currentChunkIndex--;
        }
        isSpeaking = true;
        isPaused = false;
        speakNext();
    }

    function nextTTS() {
        if (chunks.length === 0) return;
        window.speechSynthesis.resume();
        window.speechSynthesis.cancel();
        if (currentChunkIndex < chunks.length - 1) {
            currentChunkIndex++;
            isSpeaking = true;
            isPaused = false;
            speakNext();
        } else {
            stopTTS();
        }
    }

    let touchStartX = 0;
    document.addEventListener('touchstart', (e) => { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
    document.addEventListener('touchend', (e) => {
        const diff = touchStartX - e.changedTouches[0].screenX;
        if (Math.abs(diff) > 60) {
            if (diff > 0) nextTTS(); else prevTTS();
        }
    }, { passive: true });

    window.addEventListener('DOMContentLoaded', () => {
        const title = document.getElementById('book-title').innerText;
        const description = document.getElementById('book-description').innerText;
        chunks = getSpeechChunks(title, description);

        applyReaderSettings();
        syncRateUI();

        if (!isSpeechRecognitionSupported()) {
            if (btnVoice) btnVoice.style.display = 'none';
            const voiceHint = document.getElementById('voice-hint');
            if (voiceHint) voiceHint.style.display = 'none';
        }

        const localIndex = parseInt(localStorage.getItem(progressKey) || '0', 10);
        fetch('/progress/' + bookId)
            .then((resp) => resp.json())
            .then((data) => {
                if (data && data.completed) {
                    localStorage.removeItem(progressKey);
                    return;
                }
                const serverSaved = (data && typeof data.sentence_index === 'number') ? data.sentence_index : 0;
                if (serverSaved > 0 && serverSaved > currentChunkIndex && serverSaved < chunks.length) {
                    currentChunkIndex = serverSaved;
                    localStorage.setItem(progressKey, serverSaved);
                    if (statusMessage) statusMessage.innerText = `Melanjutkan dari kalimat ${currentChunkIndex + 1}`;
                }
                if (localIndex > 0) {
                    if (statusMessage) statusMessage.innerText = `Melanjutkan dari kalimat ${currentChunkIndex + 1}`;
                }
                updateUI();
            })
            .catch(() => {});

        if (currentChunkIndex > 0) {
            if (statusMessage) statusMessage.innerText = `Melanjutkan dari kalimat ${currentChunkIndex + 1}`;
        }
        
        // Initialize UI without auto-playing immediately
        updateUI();

        if (window.speechSynthesis.onvoiceschanged !== undefined) {
            window.speechSynthesis.onvoiceschanged = () => {
                // Populate voices list, do not auto-play to avoid browser blocking
                window.speechSynthesis.getVoices();
            };
        }
    });

    // ── Kontrol Suara (Voice Commands) ──
    let voiceRecognition = null;
    let voiceListening = false;
    let allowVoiceRestart = false;
    const btnVoice = document.getElementById('btn-voice');

    function isSpeechRecognitionSupported() {
        return !!(window.SpeechRecognition || window.webkitSpeechRecognition);
    }

    function setVoiceState(active) {
        voiceListening = active;
        const hint = document.getElementById('voice-hint');
        if (btnVoice) {
            btnVoice.setAttribute('aria-pressed', active ? 'true' : 'false');
            btnVoice.style.borderColor = active ? '#ffff00' : '';
            btnVoice.style.color = active ? '#ffff00' : '';
        }
        if (hint) {
            hint.innerText = active
                ? 'Mendengarkan... Ucapkan perintah suara.'
                : 'Perintah suara: "putar", "jeda", "lanjut", "ulang", "berhenti", "kecepatan", "pengaturan"';
        }
    }

    function toggleVoiceCommand() {
        if (!isSpeechRecognitionSupported()) {
            if (statusMessage) statusMessage.innerText = 'Browser tidak mendukung kontrol suara.';
            return;
        }
        if (voiceListening) {
            stopVoiceCommand();
        } else {
            startVoiceCommand();
        }
    }

    function startVoiceCommand() {
        const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SR) return;

        voiceRecognition = new SR();
        voiceRecognition.lang = 'id-ID';
        voiceRecognition.continuous = true;
        voiceRecognition.interimResults = false;
        voiceRecognition.maxAlternatives = 1;

        voiceRecognition.onstart = () => {
            allowVoiceRestart = true;
            setVoiceState(true);
            if (statusMessage) statusMessage.innerText = 'Kontrol suara aktif — silakan ucapkan perintah.';
        };

        voiceRecognition.onend = () => {
            if (voiceListening && allowVoiceRestart) {
                try {
                    voiceRecognition.start();
                } catch (e) {}
            } else {
                setVoiceState(false);
            }
        };

        voiceRecognition.onerror = (event) => {
            if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                allowVoiceRestart = false;
                voiceListening = false;
                setVoiceState(false);
                if (statusMessage) statusMessage.innerText = 'Izin mikrofon ditolak.';
            } else if (event.error === 'audio-capture') {
                allowVoiceRestart = false;
                voiceListening = false;
                setVoiceState(false);
                if (statusMessage) statusMessage.innerText = 'Mikrofon tidak tersedia.';
            }
        };

        voiceRecognition.onresult = (event) => {
            let transcript = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
                if (event.results[i] && event.results[i][0]) {
                    transcript += event.results[i][0].transcript;
                }
            }
            handleVoiceCommand(transcript);
        };

        try {
            voiceRecognition.start();
        } catch (e) {}
    }

    function stopVoiceCommand() {
        allowVoiceRestart = false;
        voiceListening = false;
        if (voiceRecognition) {
            voiceRecognition.onend = null;
            try { voiceRecognition.stop(); } catch (e) {}
            voiceRecognition = null;
        }
        setVoiceState(false);
        if (statusMessage) statusMessage.innerText = 'Kontrol suara dimatikan.';
    }

    function handleVoiceCommand(transcript) {
        const t = (transcript || '').toLowerCase().trim();

        if (t.includes('berikutnya') || t.includes('selanjutnya') || t.includes('kalimat berikut') || t.includes('lanjut ke')) {
            runVoiceAction('next');
            return;
        }
        if (t.includes('sebelumnya') || t.includes('ulang') || t.includes('kalimat sebelum') || t.includes('mundur')) {
            runVoiceAction('prev');
            return;
        }
        if (t.includes('berhenti') || t.includes('stop') || t.includes('reset')) {
            runVoiceAction('stop');
            return;
        }
        if (t.includes('jeda') || t.includes('pause') || t.includes('diam sejenak')) {
            runVoiceAction('pause');
            return;
        }
        if (t.includes('kecepatan') || t.includes('cepat') || t.includes('lambat') || t.includes('perlahan')) {
            runVoiceAction('speed');
            return;
        }
        if (t.includes('pengaturan') || t.includes('setting')) {
            runVoiceAction('settings');
            return;
        }
        if (t.includes('main') || t.includes('putar') || t.includes('mulai') || t.includes('lanjut')) {
            runVoiceAction('play');
        }
    }

    function runVoiceAction(action) {
        switch (action) {
            case 'play':
                if (!isSpeaking || isPaused) playTTS();
                break;
            case 'pause':
                if (isSpeaking && !isPaused) pauseTTS();
                break;
            case 'next':
                nextTTS();
                break;
            case 'prev':
                prevTTS();
                break;
            case 'stop':
                stopTTS();
                break;
            case 'speed':
                cycleTTSRate();
                break;
            case 'settings':
                openReaderSettings();
                break;
        }
    }

    document.addEventListener('keydown', (e) => {
        const settingsModal = document.getElementById('reader-settings-modal');
        if (settingsModal && settingsModal.style.display === 'flex' && e.key === 'Escape') {
            e.preventDefault();
            e.stopPropagation();
            closeReaderSettings();
            return;
        }
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;
        switch(e.key) {
            case ' ': e.preventDefault(); if (isSpeaking && !isPaused) pauseTTS(); else playTTS(); break;
            case 'ArrowLeft': e.preventDefault(); prevTTS(); break;
            case 'ArrowRight': e.preventDefault(); nextTTS(); break;
            case 'Escape': e.preventDefault(); stopTTS(); break;
        }
    });

    window.addEventListener('beforeunload', () => window.speechSynthesis.cancel());
</script>
@endif
@endsection
