@extends('layouts.app')

@section('content')
    @if (!session()->has('qr_restricted_token') || session()->has('auth_role'))
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('audio-books.index') }}" style="color: var(--text-muted); font-weight: 500; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem;">
            ← Kembali ke Katalog
        </a>
    </div>
    @endif

    <div class="player-container">
        <!-- Brand accent text -->
        <span class="user-role" style="font-size: 0.75rem; background: rgba(99, 102, 241, 0.15); padding: 4px 10px; border-radius: 4px; display: inline-block; margin-bottom: 1.2rem;">
            Mode Pemutar QR-Audio
        </span>
        
        <!-- Book Cover -->
        <div style="width: 120px; height: 165px; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.4); border: 1px solid var(--border-glass); margin: 0 auto 1.2rem auto;">
            @if ($audioBook->cover)
                <img src="/storage/{{ $audioBook->cover }}" alt="Cover {{ $audioBook->judul }}" style="width: 100%; height: 100%; object-fit: cover;">
            @else
                <div class="book-cover-placeholder" style="background: linear-gradient(135deg, #1e1b4b, #09090b);">
                    <span class="book-cover-placeholder-title" style="font-size: 0.75rem; -webkit-line-clamp: 3; line-clamp: 3;">{{ $audioBook->judul }}</span>
                </div>
            @endif
        </div>

        <h2 id="book-title" class="player-title text-gradient" style="margin-bottom: 0.4rem;">{{ $audioBook->judul }}</h2>

        <!-- Author & Category Metadata -->
        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: center; justify-content: center; flex-wrap: wrap;">
            <span>🏷️ {{ $audioBook->kategori ?: 'Umum' }}</span>
        </div>

        <!-- Hidden full description source -->
        <p id="book-description" style="display: none;">{{ $audioBook->deskripsi ?? 'Tidak ada deskripsi.' }}</p>

        <!-- Animated Sound Wave -->
        <div class="wave-animation paused" id="wave-animation">
            <div class="wave-bar"></div>
            <div class="wave-bar"></div>
            <div class="wave-bar"></div>
            <div class="wave-bar"></div>
            <div class="wave-bar"></div>
            <div class="wave-bar"></div>
            <div class="wave-bar"></div>
            <div class="wave-bar"></div>
            <div class="wave-bar"></div>
            <div class="wave-bar"></div>
        </div>

        <!-- Current Spoken Sentence (Subtitles Box) -->
        <div id="subtitles-card" class="player-text-box" style="display: none; min-height: 120px;">
            <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--accent-primary); font-weight: bold; letter-spacing: 0.5px; display: block; margin-bottom: 0.8rem;">
                Membacakan Kalimat:
            </span>
            <p id="current-spoken-text" style="font-size: 1.25rem; font-weight: 500; color: var(--text-primary); margin: 0; line-height: 1.6;"></p>
        </div>

        <!-- Progress status -->
        <div id="status-message" style="margin-bottom: 2rem; color: var(--text-secondary); font-size: 0.92rem; font-style: italic;">
            Siap memutar.
        </div>

        <!-- Player Controls -->
        <div class="player-controls">
            <button id="btn-prev" onclick="prevTTS()" class="round-btn" title="Mundur ke Kalimat Sebelumnya">
                ⏮️
            </button>
            <button id="btn-play" onclick="playTTS()" class="round-btn play-btn" title="Putar">
                🔊
            </button>
            <button id="btn-pause" onclick="pauseTTS()" class="round-btn" title="Jeda / Lanjutkan" style="width: 58px; height: 58px;">
                ⏸️
            </button>
            <button id="btn-stop" onclick="stopTTS()" class="round-btn" title="Berhenti dan Reset">
                ⏹️
            </button>
            <button id="btn-next" onclick="nextTTS()" class="round-btn" title="Maju ke Kalimat Berikutnya">
                ⏭️
            </button>
        </div>

        <div class="accessibility-info-box" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass); border-radius: 12px; padding: 1.2rem; text-align: left; max-width: 550px; margin: 2.5rem auto 0 auto;">
            <span style="font-weight: 600; font-size: 0.85rem; color: var(--accent-secondary); display: block; margin-bottom: 0.3rem;">ℹ️ Fitur Aksesibilitas Tunanetra</span>
            <p style="font-size: 0.78rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                Buku dibacakan kalimat demi kalimat secara terus-menerus. Sistem secara otomatis menyimpan progress bacaan terakhir Anda, sehingga Anda dapat mendengarkan kembali dari bagian terakhir saat memindai QR code ini di lain waktu.
            </p>
            <div style="margin-top: 1rem; border-top: 1px solid var(--border-glass); padding-top: 0.8rem; font-size: 0.78rem;">
                <span style="font-weight: 600; color: var(--text-primary); display: block; margin-bottom: 0.4rem;">🎹 Pintasan Keyboard (Audio Interaction):</span>
                <ul style="list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem; color: var(--text-secondary);">
                    <li><kbd style="background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">Spasi</kbd> : Putar / Jeda</li>
                    <li><kbd style="background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">Esc</kbd> : Berhenti & Reset</li>
                    <li><kbd style="background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">Panah Kanan</kbd> : Kalimat Selanjutnya</li>
                    <li><kbd style="background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">Panah Kiri</kbd> : Kalimat Sebelumnya</li>
                </ul>
            </div>
        </div>
    </div>

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

        // Helper to split long strings by space count
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

        // Get robust sentences list
        function getSpeechChunks(title, description) {
            const rawChunks = [];
            rawChunks.push(`Membaca buku: ${title}.`);
            
            if (description) {
                const paragraphs = description.split(/\r?\n/);
                for (const para of paragraphs) {
                    const trimmed = para.trim();
                    if (trimmed) {
                        // Split by sentence endings (., !, ?)
                        const sentences = trimmed.split(/(?<=[.!?])\s+/);
                        for (const sentence of sentences) {
                            if (sentence.trim()) {
                                rawChunks.push(sentence.trim());
                            }
                        }
                    }
                }
            }
            
            // Sub-chunk any chunk that is too long (> 150 chars) to prevent speech cutting off
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
            // Manage Sound Wave animation state
            if (isSpeaking && !isPaused) {
                waveAnimation.classList.remove('paused');
            } else {
                waveAnimation.classList.add('paused');
            }

            if (isSpeaking) {
                subtitlesCard.style.display = 'block';
                if (isPaused) {
                    btnPause.innerHTML = '▶️';
                    btnPause.title = 'Lanjutkan (Resume)';
                    statusMessage.innerText = 'Pemutaran dijeda.';
                } else {
                    btnPause.innerHTML = '⏸️';
                    btnPause.title = 'Jeda (Pause)';
                    statusMessage.innerText = `Membacakan kalimat ${currentChunkIndex + 1} dari ${chunks.length}...`;
                }
            } else {
                subtitlesCard.style.display = 'none';
                btnPause.innerHTML = '⏸️';
                btnPause.title = 'Jeda (Pause)';
                statusMessage.innerText = 'Selesai membacakan seluruh buku.';
            }
        }

        function playTTS() {
            // Stop any ongoing speech
            window.speechSynthesis.cancel();
            
            if (chunks.length === 0) {
                const title = document.getElementById('book-title').innerText;
                const description = document.getElementById('book-description').innerText;
                chunks = getSpeechChunks(title, description);
            }
            
            isSpeaking = true;
            isPaused = false;
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
            
            // Select Indonesian voice if possible
            const voices = window.speechSynthesis.getVoices();
            const idVoice = voices.find(voice => voice.lang.includes('id') || voice.lang.includes('ID'));
            if (idVoice) {
                currentUtterance.voice = idVoice;
            }
            
            currentUtterance.onstart = function() {
                updateUI();
            };
            
            currentUtterance.onend = function() {
                if (isSpeaking && !isPaused) {
                    currentChunkIndex++;
                    speakNext();
                }
            };
            
            currentUtterance.onerror = function(e) {
                console.error('SpeechSynthesis error:', e);
                // If blocked by browser autoplay or interrupted/canceled, do not skip sentences.
                // Stop the loop and wait for user gesture to play from the current index.
                if (e.error === 'not-allowed' || e.error === 'interrupted' || e.error === 'canceled') {
                    isSpeaking = false;
                    updateUI();
                    return;
                }
                if (isSpeaking && !isPaused) {
                    currentChunkIndex++;
                    speakNext();
                }
            };
            
            window.speechSynthesis.speak(currentUtterance);
        }

        function pauseTTS() {
            if (isSpeaking) {
                if (isPaused) {
                    window.speechSynthesis.resume();
                    isPaused = false;
                    
                    // Chrome/Safari resume workaround
                    setTimeout(() => {
                        if (window.speechSynthesis.paused) {
                            window.speechSynthesis.cancel();
                            speakNext();
                        }
                    }, 150);
                } else {
                    window.speechSynthesis.pause();
                    isPaused = true;
                }
                updateUI();
            }
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
            if (currentChunkIndex > 0) {
                currentChunkIndex--;
                isSpeaking = true;
                isPaused = false;
                speakNext();
            } else {
                playTTS();
            }
        }

        function nextTTS() {
            if (chunks.length === 0) return;
            if (currentChunkIndex < chunks.length - 1) {
                currentChunkIndex++;
                isSpeaking = true;
                isPaused = false;
                speakNext();
            }
        }

        // Attempt immediate autoplay on load
        window.addEventListener('DOMContentLoaded', () => {
            // Populate chunks
            const title = document.getElementById('book-title').innerText;
            const description = document.getElementById('book-description').innerText;
            chunks = getSpeechChunks(title, description);

            // Attempt to play immediately
            playTTS();

            // Setup voiceschanged listener to retry if voices loaded late
            if (window.speechSynthesis.onvoiceschanged !== undefined) {
                window.speechSynthesis.onvoiceschanged = () => {
                    if (!isSpeaking) {
                        playTTS();
                    }
                };
            }

            // Fallback: If autoplay is blocked by the mobile browser,
            // the very first touch, tap, or scroll anywhere on the document will trigger it.
            const handleInteraction = () => {
                if (!isSpeaking && !isPaused) {
                    playTTS();
                }
                document.removeEventListener('click', handleInteraction);
                document.removeEventListener('touchstart', handleInteraction);
            };

            document.addEventListener('click', handleInteraction);
            document.addEventListener('touchstart', handleInteraction, { passive: true });

            // Initial voice instructions announcement for tunanetra users
            setTimeout(() => {
                const instructions = "Halaman pemutar buku audio sedang dibuka. Tekan Spasi untuk putar atau jeda suara. Panah Kanan untuk kalimat berikutnya. Panah Kiri untuk kalimat sebelumnya. Escape untuk berhenti.";
                if ('speechSynthesis' in window && localStorage.getItem('acc_speech') === 'true') {
                    window.speechSynthesis.cancel();
                    const utter = new SpeechSynthesisUtterance(instructions);
                    utter.lang = 'id-ID';
                    window.speechSynthesis.speak(utter);
                }
            }, 1200);
        });

        // Keyboard Hotkeys for Audio Interaction
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }

            switch(e.key) {
                case ' ':
                    e.preventDefault();
                    if (!isSpeaking) {
                        playTTS();
                    } else {
                        pauseTTS();
                    }
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    prevTTS();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    nextTTS();
                    break;
                case 'Escape':
                    e.preventDefault();
                    stopTTS();
                    break;
            }
        });

        // Clean up speech synthesis when leaving the page
        window.addEventListener('beforeunload', () => {
            window.speechSynthesis.cancel();
        });
    </script>
@endsection
