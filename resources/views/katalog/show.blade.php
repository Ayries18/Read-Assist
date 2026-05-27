@extends('layouts.app')

@section('content')
    @php
        $host = request()->getSchemeAndHttpHost();
        if (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) {
            $detectedIp = \App\Http\Controllers\AudioBukuController::getDetectedIp();
            $port = request()->getPort();
            $host = 'http://' . $detectedIp . ($port ? ':' . $port : ':8000');
        }
        $qrUrl = rtrim($host, '/') . '/katalog-audio/' . $book->id;
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
            <div class="card border shadow-sm p-6" style="background: #121316; border-color: rgba(255, 255, 255, 0.08);">
                <div>
                    <!-- Book Header with Cover and Metadata -->
                    <div class="flex gap-6 flex-wrap mb-6 items-start">
                        <!-- Book Cover -->
                        <div class="w-[140px] h-[190px] rounded-xl overflow-hidden shrink-0 shadow-lg border border-white/10">
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
                            <h2 id="book-title" class="text-gradient text-3xl font-bold mb-2 leading-tight">
                                {{ $book->judul }}
                            </h2>

                            <!-- Metadata bar -->
                            <div class="text-sm text-slate-300 flex flex-col gap-1">
                                <span>Kategori: <strong>{{ $book->kategori ?: 'Umum' }}</strong></span>
                                <span id="upload-time" data-utc="{{ $book->created_at->toIso8601String() }}">Diunggah: <strong>{{ $book->created_at->format('d M Y, H:i') }}</strong></span>
                                <span id="update-time-relative" data-utc-updated="{{ $book->updated_at->toIso8601String() }}">Diperbarui: <strong></strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Metadata Buku Grid -->
                    <h4 class="text-base text-white font-semibold mb-3">Metadata Buku</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white/[0.02] border border-white/10 rounded-lg p-3 text-center">
                            <span class="text-xs text-slate-400 uppercase font-bold block mb-1">Karakter</span>
                            <span class="text-lg font-bold text-blue-400">{{ strlen($book->deskripsi) }}</span>
                        </div>
                        <div class="bg-white/[0.02] border border-white/10 rounded-lg p-3 text-center">
                            <span class="text-xs text-slate-400 uppercase font-bold block mb-1">Jumlah Kata</span>
                            <span class="text-lg font-bold text-indigo-400">{{ str_word_count($book->deskripsi) }}</span>
                        </div>
                        <div class="bg-white/[0.02] border border-white/10 rounded-lg p-3 text-center">
                            <span class="text-xs text-slate-400 uppercase font-bold block mb-1">Estimasi Baca</span>
                            <span class="text-lg font-bold text-emerald-400">{{ ceil(str_word_count($book->deskripsi) / 150) }} Menit</span>
                        </div>
                        <div class="bg-white/[0.02] border border-white/10 rounded-lg p-3 text-center">
                            <span class="text-xs text-slate-400 uppercase font-bold block mb-1">Status Audio</span>
                            <span class="text-base font-bold text-emerald-400 block mt-1">Siap</span>
                        </div>
                    </div>

                    @if ($book->user_id)
                        <p class="text-slate-300 mb-6 text-sm flex items-center gap-2">
                            <span class="text-slate-400">Pengunggah ID: {{ $book->user_id }}</span>
                        </p>
                    @endif

                    <h4 class="text-base text-white font-semibold mb-2">Deskripsi Buku</h4>
                    <div class="bg-slate-900/40 border border-white/10 rounded-xl p-5 mb-6">
                        <p class="text-slate-300 leading-relaxed text-sm whitespace-pre-line">
                            {{ isset($book->deskripsi) ? \Illuminate\Support\Str::limit($book->deskripsi, 600) : 'Tidak ada deskripsi.' }}
                        </p>
                        <div id="book-description" class="hidden">{{ $book->deskripsi ?? 'Tidak ada deskripsi.' }}</div>
                    </div>

                    @if ($book->file_buku)
                        <div class="bg-white/[0.03] border border-white/10 rounded-lg p-3 px-4 flex justify-between items-center mb-8">
                            <span class="text-sm text-slate-300">File PDF/EPUB Terlampir</span>
                            <a href="/storage/{{ $book->file_buku }}" target="_blank" class="btn btn-ghost btn-sm py-1 px-4 text-sm">
                                Buka File
                            </a>
                        </div>
                    @endif

                    <!-- Audio Player Area -->
                    <div class="bg-indigo-500/5 border border-indigo-500/15 rounded-xl p-6 text-center">
                        <h4 class="mb-2 text-base">Dengarkan di Laptop Ini</h4>

                        @if ($book->audio_status === 'completed' && $book->file_audio && $book->file_audio !== 'tts')
                            <div class="mb-4">
                                <audio id="generated-audio-player" controls class="w-full max-w-md mx-auto">
                                    <source src="{{ route('audio.stream', $book) }}" type="audio/mpeg">
                                    Browser Anda tidak mendukung pemutar audio.
                                </audio>
                                <div style="margin-top: 0.5rem;">
                                    <a href="{{ route('audio.stream', $book) }}" download class="btn btn-primary btn-sm px-7 py-3 text-sm">
                                    Download MP3
                                </a>
                                <a href="{{ route('audio-books.play', $book->qr_token) }}" class="btn btn-ghost btn-sm px-7 py-3 text-sm">
                                    Buka Mode Tunanetra
                                </a>
                            </div>
                        @else
                            <div class="mb-5 flex justify-center items-center gap-2">
                                <span class="text-sm text-slate-300">Status Audio:</span>
                                <span id="audio-status-badge" class="badge badge-ghost badge-sm">Browser TTS</span>
                            </div>
                            <div class="flex gap-4 justify-center">
                                <button id="btn-play-show" onclick="playTTS()" class="btn btn-primary btn-sm px-7 py-3 text-sm">
                                    Putar Suara (Play)
                                </button>
                                <button id="btn-stop-show" onclick="stopTTS()" class="btn btn-ghost btn-sm px-7 py-3 text-sm">
                                    Berhenti
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Admin Action Panel -->
            @if (session('auth_role') === 'admin')
                <div class="card border shadow-sm p-6" style="background: rgba(239, 68, 68, 0.02); border-color: rgba(239, 68, 68, 0.15);">
                    <h4 class="text-base text-red-400 font-semibold mb-3">Panel Kelola Admin</h4>
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
                                <button type="submit" class="btn btn-ghost border-indigo-500/30 text-indigo-400 w-full">
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
            <div class="card border shadow-sm p-6 text-center flex flex-col items-center" style="padding: 2.5rem 1.8rem; background: #121316; border-color: rgba(255, 255, 255, 0.08);">
                <h3 class="text-xl font-bold mb-2 text-center">QR-Audio untuk Tunanetra</h3>
                <p class="text-sm text-slate-300 mb-8">Pindai QR ini melalui HP Anda untuk mendengarkan buku.</p>

                <div class="bg-white p-5 rounded-xl inline-block shadow-xl">
                    <img
                        id="qr-code-img"
                        src="{{ route('qr-code.generate', ['data' => $qrUrl, 'size' => 320]) }}"
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

    @if (!($book->audio_status === 'completed' && $book->file_audio && $book->file_audio !== 'tts'))
    <script>
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
        let currentUtterance = null;

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
            rawChunks.push(`Membaca judul buku: ${title}.`);
            
            if (description) {
                const paragraphs = description.split(/\r?\n/);
                for (const para of paragraphs) {
                    const trimmed = para.trim();
                    if (trimmed) {
                        const sentences = trimmed.split(/(?<=[.!?])\s+/);
                        for (const sentence of sentences) {
                            if (sentence.trim()) {
                                rawChunks.push(sentence.trim());
                            }
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

        const statusBadge = document.getElementById('audio-status-badge');

        function playTTS() {
            // Stop mini player if running
            if (typeof closeMiniPlayer === 'function') {
                closeMiniPlayer();
            }

            window.speechSynthesis.cancel();
            
            const title = document.getElementById('book-title').innerText;
            const description = document.getElementById('book-description').innerText;
            chunks = getSpeechChunks(title, description);
            currentChunkIndex = 0;
            isSpeaking = true;
            
            if (statusBadge) {
                statusBadge.innerText = "Memulai...";
                statusBadge.style.color = "var(--accent-primary)";
                statusBadge.style.borderColor = "var(--accent-primary)";
                statusBadge.style.background = "rgba(99, 102, 241, 0.15)";
            }
            
            speakNext();
        }

        function speakNext() {
            if (!isSpeaking) return;
            
            if (currentChunkIndex >= chunks.length) {
                isSpeaking = false;
                currentChunkIndex = 0;
                document.getElementById('btn-play-show').innerHTML = 'Putar Suara (Play)';
                
                if (statusBadge) {
                    statusBadge.innerText = "Selesai";
                    statusBadge.style.color = "var(--text-secondary)";
                    statusBadge.style.borderColor = "var(--border-glass)";
                    statusBadge.style.background = "rgba(255, 255, 255, 0.05)";
                }
                return;
            }
            
            const text = chunks[currentChunkIndex];
            document.getElementById('btn-play-show').innerHTML = `Membaca Bagian ${currentChunkIndex + 1}...`;
            
            if (statusBadge) {
                statusBadge.innerText = `Memutar (${currentChunkIndex + 1}/${chunks.length})`;
                statusBadge.style.color = "var(--accent-success)";
                statusBadge.style.borderColor = "var(--accent-success)";
                statusBadge.style.background = "rgba(16, 185, 129, 0.15)";
            }
            
            currentUtterance = new SpeechSynthesisUtterance(text);
            currentUtterance.lang = 'id-ID';
            
            const voices = window.speechSynthesis.getVoices();
            const idVoice = voices.find(voice => voice.lang.includes('id') || voice.lang.includes('ID'));
            if (idVoice) {
                currentUtterance.voice = idVoice;
            }
            
            currentUtterance.onend = function() {
                if (isSpeaking) {
                    currentChunkIndex++;
                    speakNext();
                }
            };
            
            currentUtterance.onerror = function(e) {
                console.error(e);
                if (e.error === 'not-allowed' || e.error === 'interrupted' || e.error === 'canceled') {
                    isSpeaking = false;
                    document.getElementById('btn-play-show').innerHTML = 'Putar Suara (Play)';
                    return;
                }
                if (isSpeaking) {
                    currentChunkIndex++;
                    speakNext();
                }
            };

            window.speechSynthesis.speak(currentUtterance);
        }

        function stopTTS() {
            isSpeaking = false;
            window.speechSynthesis.cancel();
            currentUtterance = null;
            chunks = [];
            currentChunkIndex = 0;

            const playBtn = document.getElementById('btn-play-show');
            if (playBtn) {
                playBtn.innerHTML = 'Putar Suara (Play)';
            }

            if (statusBadge) {
                statusBadge.innerText = 'Browser TTS';
                statusBadge.style.color = '';
                statusBadge.style.borderColor = '';
                statusBadge.style.background = '';
            }
        }

        function printQR() {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Cetak QR - {{ $book->judul }}</title>
                    <style>
                        body { font-family: system-ui, -apple-system, sans-serif; text-align: center; padding: 40px; color: #000; background: #fff; }
                        .container { border: 3px dashed #6366f1; padding: 30px; display: inline-block; border-radius: 15px; max-width: 350px; }
                        h2 { margin: 0 0 10px 0; font-size: 1.5rem; color: #1e1b4b; }
                        p { margin: 0 0 20px 0; font-size: 0.95rem; color: #4b5563; }
                        img { width: 220px; height: 220px; display: block; margin: 0 auto; border: 1px solid #e5e7eb; padding: 8px; border-radius: 8px; }
                        .footer { margin-top: 20px; font-size: 0.8rem; border-top: 1px solid #e5e7eb; padding-top: 15px; font-weight: bold; color: #6366f1; letter-spacing: 1px; }
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

        document.addEventListener('DOMContentLoaded', function() {
            updateRelativeTimes();
            setInterval(updateRelativeTimes, 15000);
        });

        // Clean up when leaving page
        window.addEventListener('beforeunload', () => {
            window.speechSynthesis.cancel();
        });
    </script>
    @endif
@endsection
