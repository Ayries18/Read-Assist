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
                    <!-- Book Header with Cover and Metadata -->
                    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1.5rem; align-items: flex-start;">
                        <!-- Book Cover -->
                        <div style="width: 140px; height: 190px; border-radius: 10px; overflow: hidden; flex-shrink: 0; box-shadow: 0 4px 15px rgba(0,0,0,0.4); border: 1px solid var(--border-glass);">
                            @if ($audioBook->cover)
                                <img src="/storage/{{ $audioBook->cover }}" alt="Cover {{ $audioBook->judul }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <div class="book-cover-placeholder">
                                    <span class="book-cover-placeholder-title" style="font-size: 0.8rem;">{{ $audioBook->judul }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Title and Quick Stats -->
                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
                            <h2 id="book-title" class="text-gradient" style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.6rem; line-height: 1.3;">
                                {{ $audioBook->judul }}
                            </h2>

                            <!-- Metadata bar -->
                            <div style="font-size: 0.9rem; color: var(--text-secondary); display: flex; flex-direction: column; gap: 0.4rem;">
                                <span>🏷️ Kategori: <strong>{{ $audioBook->kategori ?: 'Umum' }}</strong></span>
                                <span id="upload-time" data-utc="{{ $audioBook->created_at->toIso8601String() }}">📅 Diunggah: <strong>{{ $audioBook->created_at->format('d M Y, H:i') }}</strong></span>
                                <span id="update-time-relative" data-utc-updated="{{ $audioBook->updated_at->toIso8601String() }}">🔄 Diperbarui: <strong></strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Metadata Buku Grid -->
                    <h4 style="font-size: 1rem; color: var(--text-primary); margin-bottom: 0.8rem; font-weight: 600;">📊 Metadata Buku</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.8rem; text-align: center;">
                            <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 0.2rem;">Karakter</span>
                            <span style="font-size: 1.1rem; font-weight: 700; color: var(--accent-secondary);">{{ strlen($audioBook->deskripsi) }}</span>
                        </div>
                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.8rem; text-align: center;">
                            <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 0.2rem;">Jumlah Kata</span>
                            <span style="font-size: 1.1rem; font-weight: 700; color: var(--accent-primary);">{{ str_word_count($audioBook->deskripsi) }}</span>
                        </div>
                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.8rem; text-align: center;">
                            <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 0.2rem;">Estimasi Baca</span>
                            <span style="font-size: 1.1rem; font-weight: 700; color: var(--accent-success);">{{ ceil(str_word_count($audioBook->deskripsi) / 150) }} Menit</span>
                        </div>
                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.8rem; text-align: center;">
                            <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 0.2rem;">Audio Engine</span>
                            <span style="font-size: 0.9rem; font-weight: 700; color: #a855f7; display: block; margin-top: 0.2rem;">TTS id-ID</span>
                        </div>
                    </div>

                    @if ($audioBook->user_id)
                        <p style="color: var(--text-secondary); margin-bottom: 1.5rem; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span style="color: var(--text-muted);">Pengunggah ID: {{ $audioBook->user_id }}</span>
                        </p>
                    @endif

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
                        <h4 style="margin-bottom: 0.5rem; font-size: 1.05rem;">Dengarkan di Laptop Ini</h4>
                        
                        <!-- Dynamic Audio Status -->
                        <div style="margin-bottom: 1.2rem; display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 0.85rem; color: var(--text-secondary);">Status Audio:</span>
                            <span id="audio-status-badge" style="font-size: 0.8rem; font-weight: bold; padding: 3px 10px; border-radius: 20px; background: rgba(255, 255, 255, 0.05); color: var(--text-secondary); border: 1px solid var(--border-glass);">
                                Siap
                            </span>
                        </div>
                        
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
                        <a href="/katalog-audio/{{ $audioBook->id }}/edit" class="btn btn-secondary btn-inline" style="flex: 1; border-color: var(--text-muted); text-align: center; display: flex; align-items: center; justify-content: center;">
                            ✏️ Edit Buku
                        </a>
                        <form method="POST" action="/katalog-audio/{{ $audioBook->id }}" onsubmit="return confirm('Yakin ingin menghapus buku ini?')" style="flex: 1; margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="width: 100%;">
                                🗑️ Hapus Buku
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Side: QR Code -->
        <div style="display: flex; flex-direction: column; gap: 1.8rem;">
            <div class="card" style="text-align: center; display: flex; flex-direction: column; align-items: center; padding: 2.5rem 1.8rem;">
                <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem; text-align: center;">QR-Audio untuk Tunanetra</h3>
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 2rem;">Pindai QR ini melalui HP Anda untuk mendengarkan buku.</p>

                <div class="qr-box" style="background: #ffffff; padding: 1.2rem; border-radius: 12px; display: inline-block; box-shadow: 0 4px 20px rgba(0,0,0,0.5);">
                    <img
                        id="qr-code-img"
                        src="https://api.qrserver.com/v1/create-qr-code/?size=320x320&margin=10&ecc=M&data={{ urlencode($qrUrl) }}"
                        alt="QR Code untuk membuka audio {{ $audioBook->judul }}"
                        style="display: block; margin: 0 auto; width: 260px; height: 260px;"
                    >
                </div>

                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 1rem; max-width: 320px;">
                    Pastikan HP terhubung ke jaringan yang sama dengan komputer. Jika pemindaian belum berhasil, gunakan tombol di bawah untuk membuka buku langsung dari HP.
                </p>
                <a href="{{ $qrUrl }}" target="_blank" rel="noreferrer noopener" class="btn btn-primary btn-inline" style="width: 100%; margin-top: 0.6rem; font-size: 0.9rem;">
                    Buka Buku Ini di HP
                </a>

                <!-- QR Action Buttons -->
                <button onclick="printQR()" class="btn btn-secondary" style="width: 100%; margin-top: 1.8rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-size: 0.9rem;">
                    🖨️ Cetak QR Code
                </button>
                <button onclick="downloadQR()" class="btn btn-secondary" style="width: 100%; margin-top: 0.5rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-size: 0.9rem;">
                    📥 Unduh QR Code
                </button>

                @if (empty($tunnelUrl) && count($localIps) > 1)
                    <div style="margin-top: 1.5rem; width: 100%; text-align: left; border-top: 1px solid var(--border-glass); padding-top: 1.2rem;">
                        <label for="ip-selector" style="font-size: 0.75rem; font-weight: bold; color: var(--text-muted); text-transform: uppercase;">🔗 Hubungkan Jaringan HP:</label>
                        <select id="ip-selector" onchange="updateSelectedIp(this.value)" class="form-control" style="font-size: 0.85rem; padding: 0.5rem 0.8rem; margin-top: 0.3rem;">
                            @foreach ($localIps as $name => $ip)
                                <option value="{{ $ip }}" {{ $ip === $detectedIp ? 'selected' : '' }}>
                                    {{ $name }} ({{ $ip }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // ⏰ Realtime & Localized Upload Time Formatter
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
                document.getElementById('btn-play-show').innerHTML = '🔊 Putar Suara (Play)';
                
                if (statusBadge) {
                    statusBadge.innerText = "Selesai";
                    statusBadge.style.color = "var(--text-secondary)";
                    statusBadge.style.borderColor = "var(--border-glass)";
                    statusBadge.style.background = "rgba(255, 255, 255, 0.05)";
                }
                return;
            }
            
            const text = chunks[currentChunkIndex];
            document.getElementById('btn-play-show').innerHTML = `🔊 Membaca Bagian ${currentChunkIndex + 1}...`;
            
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
                    document.getElementById('btn-play-show').innerHTML = '🔊 Putar Suara (Play)';
                    if (statusBadge) {
                        statusBadge.innerText = "Siap";
                        statusBadge.style.color = "var(--text-secondary)";
                        statusBadge.style.borderColor = "var(--border-glass)";
                        statusBadge.style.background = "rgba(255, 255, 255, 0.05)";
                    }
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
            
            if (statusBadge) {
                statusBadge.innerText = "Berhenti";
                statusBadge.style.color = "var(--accent-danger)";
                statusBadge.style.borderColor = "var(--accent-danger)";
                statusBadge.style.background = "rgba(239, 68, 68, 0.15)";
            }
        }

        function printQR() {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Cetak QR - {{ $audioBook->judul }}</title>
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
                        <h2>{{ $audioBook->judul }}</h2>
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
                    a.download = "{{ \Illuminate\Support\Str::slug($audioBook->judul) }}_qr_code.png";
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                })
                .catch(err => {
                    window.open(qrImg.src, '_blank');
                });
        }

        const basePlayUrlPath = "{{ route('audio-books.play', $audioBook->qr_token, false) }}";
        const port = "{{ request()->getPort() }}";

        function updateSelectedIp(ip) {
            if (!ip) return;
            localStorage.setItem('selected_local_ip', ip);
            
            const newUrl = `${window.location.protocol}//${ip}${port ? ':' + port : ''}${basePlayUrlPath}`;
            const qrImage = document.getElementById('qr-code-img');
            if (qrImage) {
                qrImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=320x320&margin=10&ecc=M&data=${encodeURIComponent(newUrl)}`;
            }
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
            const savedIp = localStorage.getItem('selected_local_ip');
            const ipSelector = document.getElementById('ip-selector');
            if (savedIp && ipSelector) {
                const hasOption = Array.from(ipSelector.options).some(opt => opt.value === savedIp);
                if (hasOption) {
                    ipSelector.value = savedIp;
                    updateSelectedIp(savedIp);
                }
            }
            
            // Run relative time calculations and update periodically
            updateRelativeTimes();
            setInterval(updateRelativeTimes, 15000);
        });

        // Clean up when leaving page
        window.addEventListener('beforeunload', () => {
            window.speechSynthesis.cancel();
        });
    </script>
@endsection
