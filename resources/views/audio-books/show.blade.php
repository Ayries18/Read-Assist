@extends('layouts.app')

@section('content')
    <div style="margin-bottom: 2rem;">
        <a href="/katalog-audio" style="color: var(--text-muted); font-weight: 500; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem;">
            ← Kembali ke Katalog
        </a>
    </div>

    <div class="detail-grid">
        <!-- Left Side: Book Details & TTS Player -->
        <div style="display: flex; flex-direction: column; gap: 1.8rem;">
            <div class="card">
                <div class="card-content">
                    <span class="user-role" style="font-size: 0.75rem; background: rgba(99, 102, 241, 0.15); padding: 4px 10px; border-radius: 4px; display: inline-block; margin-bottom: 1rem;">
                        {{ $audioBook->kategori ?: 'Tanpa Kategori' }}
                    </span>
                    <h2 id="book-title" class="text-gradient" style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1.3;">
                        {{ $audioBook->judul }}
                    </h2>
                    
                    <p style="color: var(--text-secondary); margin-bottom: 1.5rem; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span>✍️ {{ $audioBook->penulis ?: 'Penulis tidak diketahui' }}</span>
                        @if ($audioBook->user_id)
                            <span style="color: var(--text-muted);">|</span>
                            <span style="color: var(--text-muted);">Pengunggah ID: {{ $audioBook->user_id }}</span>
                        @endif
                    </p>

                    <h4 style="font-size: 1rem; color: var(--text-primary); margin-bottom: 0.5rem; font-weight: 600;">Deskripsi Buku</h4>
                    <div style="background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border-glass); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
                        <p style="color: var(--text-secondary); line-height: 1.7; font-size: 0.98rem; white-space: pre-line;">
                            {{ isset($audioBook->deskripsi) ? \Illuminate\Support\Str::limit($audioBook->deskripsi, 600) : 'Tidak ada deskripsi.' }}
                        </p>
                        <div id="book-description" style="display: none;">{{ $audioBook->deskripsi }}</div>
                    </div>

                    @if ($audioBook->file_buku)
                        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.8rem 1rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">📄 File PDF/EPUB Terlampir</span>
                            <a href="/storage/{{ $audioBook->file_buku }}" target="_blank" class="btn btn-secondary btn-inline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                Buka File
                            </a>
                        </div>
                    @endif

                    <!-- Local Player Area -->
                    <div style="background: rgba(99, 102, 241, 0.05); border: 1px solid rgba(99, 102, 241, 0.15); border-radius: 12px; padding: 1.5rem; text-align: center;">
                        <h4 style="margin-bottom: 1rem; font-size: 1.05rem;">Dengarkan di Laptop Ini</h4>
                        
                        <div style="display: flex; gap: 1rem; justify-content: center;">
                            <button id="btn-play-show" onclick="playTTS()" class="btn btn-primary btn-inline" style="padding: 0.8rem 1.8rem; font-size: 0.95rem;">
                                🔊 Putar Suara (Play)
                            </button>
                            <button id="btn-stop-show" onclick="stopTTS()" class="btn btn-secondary btn-inline" style="padding: 0.8rem 1.8rem; font-size: 0.95rem;">
                                ⏹️ Berhenti
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Action Panel -->
            @if (session('auth_role') === 'admin')
                <div class="card" style="border-color: rgba(239, 68, 68, 0.15); background: rgba(239, 68, 68, 0.02); padding: 1.5rem;">
                    <h4 style="font-size: 1rem; color: var(--accent-danger); margin-bottom: 0.8rem; font-weight: 600;">Panel Kelola Admin</h4>
                    <div style="display: flex; gap: 1rem;">
                        <a href="/katalog-audio/{{ $audioBook->id }}/edit" class="btn btn-secondary btn-inline" style="flex: 1; border-color: var(--text-muted);">
                            ✏️ Edit Buku
                        </a>
                        <form method="POST" action="/katalog-audio/{{ $audioBook->id }}" onsubmit="return confirm('Yakin ingin menghapus buku ini?')" style="flex: 1; margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                🗑️ Hapus Buku
                            </button>
                        </form>
                    </div>
                </div>
                  <!-- Right Side: QR Code -->
        <div style="display: flex; flex-direction: column; gap: 1.8rem;">
            <div class="card" style="text-align: center; display: flex; flex-direction: column; align-items: center; padding: 2.5rem 1.8rem;">
                <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem; text-align: center;">QR-Audio untuk Tunanetra</h3>
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 2rem;">Pindai QR ini melalui HP Anda untuk mendengarkan buku.</p>

                <div class="qr-box" style="background: #ffffff; padding: 1.2rem; border-radius: 12px; display: inline-block;">
                    <img
                        id="qr-code-img"
                        src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrUrl) }}"
                        alt="QR Code untuk membuka audio {{ $audioBook->judul }}"
                        style="display: block; margin: 0 auto; width: 170px; height: 170px;"
                    >
                </div>
            </div>
        </div>
    </div>

    <script>
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

        function playTTS() {
            window.speechSynthesis.cancel();
            
            const title = document.getElementById('book-title').innerText;
            const description = document.getElementById('book-description').innerText;
            chunks = getSpeechChunks(title, description);
            currentChunkIndex = 0;
            isSpeaking = true;
            
            speakNext();
        }

        function speakNext() {
            if (!isSpeaking) return;
            
            if (currentChunkIndex >= chunks.length) {
                isSpeaking = false;
                currentChunkIndex = 0;
                document.getElementById('btn-play-show').innerHTML = '🔊 Putar Suara (Play)';
                return;
            }
            
            const text = chunks[currentChunkIndex];
            document.getElementById('btn-play-show').innerHTML = `🔊 Membaca Bagian ${currentChunkIndex + 1}...`;
            
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
                    document.getElementById('btn-play-show').innerHTML = '🔊 Putar Suara (Play)';
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
            document.getElementById('btn-play-show').innerHTML = '🔊 Putar Suara (Play)';
        }

        const ipInput = document.getElementById('laptop-ip');
        const qrImage = document.getElementById('qr-code-img');
        const qrLink = document.getElementById('qr-code-link');
        const basePlayUrlPath = "{{ route('audio-books.play', $audioBook->qr_token, false) }}";
        const port = "8000";

        function updateQR() {
            if (!ipInput || !qrLink) return;
            const ip = ipInput.value.trim();
            if (!ip) return;
            
            const newUrl = `http://${ip}:${port}${basePlayUrlPath}`;
            qrLink.href = newUrl;
            qrLink.innerText = newUrl;
            
            qrImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(newUrl)}`;
        }

        function changeIp(newIp) {
            if (!ipInput) return;
            ipInput.value = newIp;
            updateQR();
        }

        if (ipInput) {
            ipInput.addEventListener('input', updateQR);
        }

        // Clean up when leaving page
        window.addEventListener('beforeunload', () => {
            window.speechSynthesis.cancel();
        });
    </script>
@endsection
