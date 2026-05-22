@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto w-full">
    <!-- Book Header -->
    <div class="card bg-base-300/50 border border-white/10 shadow-md p-4 sm:p-6 mb-5">
        <div class="flex gap-4 sm:gap-6 items-start">
            <!-- Cover -->
            <div class="w-[90px] h-[125px] sm:w-[130px] sm:h-[180px] rounded-xl overflow-hidden shrink-0 shadow-lg border border-white/10">
                @if ($audioBook->cover)
                    <img src="/storage/{{ $audioBook->cover }}" alt="Cover {{ $audioBook->judul }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center p-2 sm:p-4 text-center" style="background: linear-gradient(135deg, #1e1b4b, #09090b);">
                        <span class="text-[0.6rem] sm:text-xs font-bold text-white line-clamp-3 leading-relaxed">{{ $audioBook->judul }}</span>
                    </div>
                @endif
            </div>

            <!-- Title & Meta -->
            <div class="flex-1 min-w-0">
                <h2 id="book-title" class="text-gradient text-lg sm:text-3xl font-bold leading-tight mb-1 sm:mb-2">
                    {{ $audioBook->judul }}
                </h2>
                <div class="text-xs sm:text-sm text-slate-300 flex flex-col gap-0.5 sm:gap-1">
                    <span>Kategori: <strong>{{ $audioBook->kategori ?: 'Umum' }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 mt-4 sm:mt-6">
            <div class="bg-white/[0.02] border border-white/10 rounded-lg p-2 sm:p-3 text-center">
                <span class="text-[0.6rem] sm:text-xs text-slate-400 uppercase font-bold block mb-0.5">Karakter</span>
                <span class="text-sm sm:text-lg font-bold text-blue-400">{{ strlen($audioBook->deskripsi) }}</span>
            </div>
            <div class="bg-white/[0.02] border border-white/10 rounded-lg p-2 sm:p-3 text-center">
                <span class="text-[0.6rem] sm:text-xs text-slate-400 uppercase font-bold block mb-0.5">Jumlah Kata</span>
                <span class="text-sm sm:text-lg font-bold text-indigo-400">{{ str_word_count($audioBook->deskripsi) }}</span>
            </div>
            <div class="bg-white/[0.02] border border-white/10 rounded-lg p-2 sm:p-3 text-center">
                <span class="text-[0.6rem] sm:text-xs text-slate-400 uppercase font-bold block mb-0.5">Estimasi Baca</span>
                <span class="text-sm sm:text-lg font-bold text-emerald-400">{{ ceil(str_word_count($audioBook->deskripsi) / 150) }} Menit</span>
            </div>
            <div class="bg-white/[0.02] border border-white/10 rounded-lg p-2 sm:p-3 text-center">
                <span class="text-[0.6rem] sm:text-xs text-slate-400 uppercase font-bold block mb-0.5">Status Audio</span>
                <span class="text-[0.65rem] sm:text-base font-bold text-emerald-400 block mt-0.5 sm:mt-1">Siap</span>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="card bg-base-300/50 border border-white/10 shadow-md p-4 sm:p-6 mb-5">
        <h4 class="text-xs sm:text-base text-white font-semibold mb-2">Deskripsi Buku</h4>
        <div class="bg-slate-900/40 border border-white/10 rounded-xl p-3 sm:p-5">
            <p class="text-slate-300 leading-relaxed text-xs sm:text-sm whitespace-pre-line">
                {{ isset($audioBook->deskripsi) ? \Illuminate\Support\Str::limit($audioBook->deskripsi, 300) : 'Tidak ada deskripsi.' }}
            </p>
            <div id="book-description" class="hidden">{{ $audioBook->deskripsi ?? 'Tidak ada deskripsi.' }}</div>
        </div>
    </div>

    @if ($audioBook->audio_status === 'completed' && $audioBook->file_audio && $audioBook->file_audio !== 'tts')
        <!-- Generated MP3 Player -->
        <div class="card bg-base-300/50 border border-indigo-500/15 shadow-md p-4 sm:p-6 text-center mb-5">
            <h4 class="text-sm sm:text-base mb-3">Dengarkan Audio</h4>
            <audio id="generated-audio-player" controls class="w-full max-w-md mx-auto">
                <source src="{{ route('audio.stream', $audioBook) }}" type="audio/mpeg">
                Browser Anda tidak mendukung pemutar audio.
            </audio>
            <div style="margin-top: 0.2rem;">
                <a href="{{ route('audio.stream', $audioBook) }}" download class="text-indigo-400 underline text-sm">Download MP3</a>
            </div>
        </div>
    @else
        <!-- TTS Player Section -->
        <div class="card bg-base-300/50 border border-indigo-500/15 shadow-md p-4 sm:p-6 text-center mb-5">
            <h4 class="text-sm sm:text-base mb-1">Pemutar Audio (TTS)</h4>
            <p class="text-xs text-slate-400 mb-4">Kalimat demi kalimat — progress tersimpan otomatis</p>

            <!-- Status Badge -->
            <div class="flex justify-center items-center gap-2 mb-4">
                <span class="text-xs text-slate-400">Status:</span>
                <span id="audio-status-badge" class="badge badge-ghost badge-sm text-xs">Browser TTS</span>
            </div>

            <!-- Hidden full description source -->
            <p id="book-description-tts" class="hidden">{{ $audioBook->deskripsi ?? 'Tidak ada deskripsi.' }}</p>

            <!-- Wave Animation -->
            <div class="wave-animation paused mb-3" id="wave-animation"></div>

            <!-- Subtitles -->
            <div id="subtitles-card" class="bg-slate-900/60 border border-white/10 rounded-xl p-3 sm:p-4 text-center mb-4 min-h-[60px] flex flex-col items-center justify-center" style="display: none;">
                <p id="current-spoken-text" class="text-sm sm:text-lg font-medium text-white leading-relaxed m-0"></p>
            </div>

            <!-- Progress -->
            <div id="status-message" class="text-xs text-slate-400 mb-4 min-h-[1.2em]">
                Ketuk play untuk memulai
            </div>

            <!-- Controls -->
            <div class="flex items-center justify-center gap-3 sm:gap-5 mb-3">
                <button id="btn-prev" onclick="prevTTS()" class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-full text-slate-400 hover:text-white active:scale-90 transition-all" style="background: rgba(255,255,255,0.05);">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h2v12H6zm3.5 6 8.5 6V6z"/></svg>
                </button>

                <button id="btn-play" onclick="playTTS()" class="flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-indigo-500 text-white shadow-2xl shadow-indigo-500/40 active:scale-90 transition-all duration-150" title="Putar">
                    <svg id="play-icon" class="w-7 h-7 sm:w-8 sm:h-8" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                </button>

                <button id="btn-pause" onclick="pauseTTS()" class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-full text-slate-400 hover:text-white active:scale-90 transition-all" title="Jeda" style="background: rgba(255,255,255,0.05); display: none;">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                </button>

                <button id="btn-next" onclick="nextTTS()" class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-full text-slate-400 hover:text-white active:scale-90 transition-all" title="Selanjutnya" style="background: rgba(255,255,255,0.05);">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg>
                </button>
            </div>

            <!-- Stop -->
            <button id="btn-stop" onclick="stopTTS()" class="flex items-center gap-1.5 text-xs text-slate-500 hover:text-red-400 active:scale-95 transition-all mx-auto" title="Berhenti" style="display: none;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h12v12H6z"/></svg>
                Berhenti & Reset
            </button>
        </div>
    @endif
</div>

@if (!($audioBook->audio_status === 'completed' && $audioBook->file_audio && $audioBook->file_audio !== 'tts'))
<script>
    const bookId = "{{ $audioBook->id }}";
    let chunks = [];
    let currentChunkIndex = parseInt(localStorage.getItem('read_assist_progress_' + bookId) || '0', 10);
    let isSpeaking = false;
    let isPaused = false;
    let currentUtterance = null;

    const btnPlay = document.getElementById('btn-play');
    const btnPause = document.getElementById('btn-pause');
    const btnStop = document.getElementById('btn-stop');
    const statusMessage = document.getElementById('status-message');
    const subtitlesCard = document.getElementById('subtitles-card');
    const currentSpokenText = document.getElementById('current-spoken-text');
    const waveAnimation = document.getElementById('wave-animation');
    const statusBadge = document.getElementById('audio-status-badge');

    function chunkString(str, maxLength) {
        const result = [];
        let temp = '';
        const words = str.split(' ');
        for (const word of words) {
            if ((temp + ' ' + word).trim().length <= maxLength) {
                temp = (temp + ' ' + word).trim();
            } else {
                if (temp) result.push(temp);
                temp = word;
            }
        }
        if (temp) result.push(temp);
        return result;
    }

    function getSpeechChunks(title, description) {
        const rawChunks = [];
        rawChunks.push(`Membaca buku: ${title}.`);
        if (description) {
            const paragraphs = description.split(/\r?\n/);
            for (const para of paragraphs) {
                const trimmed = para.trim();
                if (trimmed) {
                    const sentences = trimmed.split(/(?<=[.!?])\s+/);
                    for (const sentence of sentences) {
                        if (sentence.trim()) rawChunks.push(sentence.trim());
                    }
                }
            }
        }
        const finalChunks = [];
        for (const chunk of rawChunks) {
            if (chunk.length > 150) {
                const subChunks = chunkString(chunk, 150);
                finalChunks.push(...subChunks);
            } else {
                finalChunks.push(chunk);
            }
        }
        return finalChunks;
    }

    function updateUI() {
        if (isSpeaking && !isPaused) {
            waveAnimation.classList.remove('paused');
        } else {
            waveAnimation.classList.add('paused');
        }

        if (isSpeaking) {
            btnPlay.style.display = 'none';
            btnPause.style.display = 'flex';
            btnStop.style.display = 'flex';
            subtitlesCard.style.display = 'flex';

            if (isPaused) {
                btnPause.innerHTML = `<svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>`;
                btnPause.title = 'Lanjutkan';
                statusMessage.innerText = 'Dijeda';
                if (statusBadge) {
                    statusBadge.innerText = 'Dijeda';
                    statusBadge.style.color = 'var(--text-muted)';
                    statusBadge.style.borderColor = 'var(--border-glass)';
                }
            } else {
                btnPause.innerHTML = `<svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>`;
                btnPause.title = 'Jeda';
                statusMessage.innerText = `Kalimat ${currentChunkIndex + 1} dari ${chunks.length}`;
                if (statusBadge) {
                    statusBadge.innerText = `Memutar (${currentChunkIndex + 1}/${chunks.length})`;
                    statusBadge.style.color = 'var(--accent-success)';
                    statusBadge.style.borderColor = 'var(--accent-success)';
                }
            }
        } else {
            btnPlay.style.display = 'flex';
            btnPause.style.display = 'none';
            btnStop.style.display = 'none';
            subtitlesCard.style.display = 'none';
            btnPause.innerHTML = `<svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>`;

            if (chunks.length > 0 && currentChunkIndex >= chunks.length) {
                statusMessage.innerText = 'Selesai — seluruh buku telah dibacakan';
                if (statusBadge) {
                    statusBadge.innerText = 'Selesai';
                    statusBadge.style.color = 'var(--text-secondary)';
                    statusBadge.style.borderColor = 'var(--border-glass)';
                }
            } else {
                statusMessage.innerText = 'Ketuk play untuk memulai';
                if (statusBadge) {
                    statusBadge.innerText = 'Browser TTS';
                    statusBadge.style.color = '';
                    statusBadge.style.borderColor = '';
                }
            }
        }
    }

    function playTTS() {
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
        speakNext();
    }

    function speakNext() {
        if (!isSpeaking) return;
        if (currentChunkIndex >= chunks.length) {
            isSpeaking = false;
            currentChunkIndex = 0;
            localStorage.removeItem('read_assist_progress_' + bookId);
            updateUI();
            return;
        }
        const text = chunks[currentChunkIndex];
        currentSpokenText.innerText = text;
        localStorage.setItem('read_assist_progress_' + bookId, currentChunkIndex);

        currentUtterance = new SpeechSynthesisUtterance(text);
        currentUtterance.lang = 'id-ID';
        const voices = window.speechSynthesis.getVoices();
        const idVoice = voices.find(v => v.lang.includes('id') || v.lang.includes('ID'));
        if (idVoice) currentUtterance.voice = idVoice;
        currentUtterance.onstart = () => updateUI();
        currentUtterance.onend = () => { if (isSpeaking && !isPaused) { currentChunkIndex++; speakNext(); } };
        currentUtterance.onerror = (e) => {
            if (e.error === 'not-allowed' || e.error === 'interrupted' || e.error === 'canceled') {
                isSpeaking = false; updateUI(); return;
            }
            if (isSpeaking && !isPaused) { currentChunkIndex++; speakNext(); }
        };
        window.speechSynthesis.speak(currentUtterance);
    }

    function pauseTTS() {
        if (!isSpeaking) return;
        if (isPaused) {
            window.speechSynthesis.resume();
            isPaused = false;
            setTimeout(() => { if (window.speechSynthesis.paused) { window.speechSynthesis.cancel(); speakNext(); } }, 150);
        } else {
            window.speechSynthesis.pause();
            isPaused = true;
        }
        updateUI();
    }

    function stopTTS() {
        isSpeaking = false;
        isPaused = false;
        window.speechSynthesis.cancel();
        currentChunkIndex = 0;
        localStorage.removeItem('read_assist_progress_' + bookId);
        updateUI();
    }

    function prevTTS() {
        if (chunks.length === 0) return;
        if (currentChunkIndex > 0) currentChunkIndex--;
        isSpeaking = true;
        isPaused = false;
        speakNext();
    }

    function nextTTS() {
        if (chunks.length === 0) return;
        if (currentChunkIndex < chunks.length - 1) currentChunkIndex++;
        isSpeaking = true;
        isPaused = false;
        speakNext();
    }

    // Touch swipe
    let touchStartX = 0;
    document.addEventListener('touchstart', (e) => { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
    document.addEventListener('touchend', (e) => {
        const diff = touchStartX - e.changedTouches[0].screenX;
        if (Math.abs(diff) > 60) {
            if (diff > 0) nextTTS(); else prevTTS();
        }
    }, { passive: true });

    // Init
    window.addEventListener('DOMContentLoaded', () => {
        const title = document.getElementById('book-title').innerText;
        const description = document.getElementById('book-description').innerText;
        chunks = getSpeechChunks(title, description);

        if (currentChunkIndex > 0) {
            statusMessage.innerText = `Melanjutkan dari kalimat ${currentChunkIndex + 1}`;
        }
        playTTS();

        if (window.speechSynthesis.onvoiceschanged !== undefined) {
            window.speechSynthesis.onvoiceschanged = () => { if (!isSpeaking) playTTS(); };
        }

        const handleInteraction = () => {
            if (!isSpeaking && !isPaused) playTTS();
            document.removeEventListener('click', handleInteraction);
            document.removeEventListener('touchstart', handleInteraction);
        };
        document.addEventListener('click', handleInteraction);
        document.addEventListener('touchstart', handleInteraction, { passive: true });
    });

    // Keyboard
    document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        switch(e.key) {
            case ' ': e.preventDefault(); if (!isSpeaking) playTTS(); else pauseTTS(); break;
            case 'ArrowLeft': e.preventDefault(); prevTTS(); break;
            case 'ArrowRight': e.preventDefault(); nextTTS(); break;
            case 'Escape': e.preventDefault(); stopTTS(); break;
        }
    });

    window.addEventListener('beforeunload', () => window.speechSynthesis.cancel());
</script>
@endif
@endsection
