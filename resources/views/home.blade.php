@extends('layouts.app')

@section('content')
    <div class="landing-wrapper" style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 4.5rem; margin-bottom: 5rem;">
        
        <!-- 🚀 Hero & Interactive Simulation Section -->
        <section class="hero-section" style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 4rem; align-items: center; padding: 2rem 0;">
            <div class="hero-content" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <span class="user-role" style="font-size: 0.78rem; background: rgba(79, 70, 229, 0.1); padding: 6px 14px; border-radius: 50px; color: #818cf8; font-weight: 700; border: 1px solid rgba(79, 70, 229, 0.25); text-transform: uppercase; letter-spacing: 1px;">
                        Platform Aksesibilitas Buku
                    </span>
                </div>
                <h1 class="hero-title" style="font-size: 3.2rem; font-weight: 800; line-height: 1.15; color: #ffffff; letter-spacing: -1px; margin: 0;">
                    Jembatan Audio untuk <br><span style="color: #818cf8; background: linear-gradient(to right, #a5b4fc, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Membaca Buku Fisik</span>
                </h1>
                <p class="hero-subtitle" style="font-size: 1.15rem; color: #cbd5e1; line-height: 1.65; margin: 0; max-width: 600px;">
                    Read-Assist mendampingi penyandang tunanetra untuk membaca buku cetak secara mandiri. Cukup pindai label QR unik yang ditempel pada buku fisik untuk mendengarkan pembacaan teks otomatis langsung dari smartphone Anda.
                </p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.8rem; align-items: center;">
                    <a href="{{ route('audio-books.index') }}" class="btn btn-primary btn-inline" style="padding: 0.95rem 2rem; font-size: 1rem; border-radius: 12px; background: #4f46e5; color: #fff; font-weight: 600; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3); border: none; display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s ease;">
                        📚 Buka Katalog Buku
                    </a>
                    @if (!session()->has('auth_role'))
                        <a href="{{ route('login') }}" class="btn btn-secondary btn-inline" style="padding: 0.95rem 1.8rem; font-size: 1rem; border-radius: 12px; font-weight: 600; border: 1px solid var(--border-glass); background: rgba(255,255,255,0.03); color: #fff; transition: all 0.2s ease;">
                            Masuk Ke Akun
                        </a>
                    @endif
                </div>

                <!-- 📊 Real-world Service Stats -->
                <div class="stats-bar" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 2rem; border-top: 1px solid var(--border-glass); padding-top: 2rem;">
                    <div class="stat-item" style="display: flex; flex-direction: column; gap: 0.2rem;">
                        <span class="stat-val" style="font-size: 2.2rem; font-weight: 800; color: #ffffff;">{{ $bookCount }}</span>
                        <span class="stat-lbl" style="font-size: 0.8rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Buku Terdaftar</span>
                    </div>
                    <div class="stat-item" style="display: flex; flex-direction: column; gap: 0.2rem;">
                        <span class="stat-val" style="font-size: 2.2rem; font-weight: 800; color: #ffffff;">{{ $charCount }}</span>
                        <span class="stat-lbl" style="font-size: 0.8rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Karakter</span>
                    </div>
                    <div class="stat-item" style="display: flex; flex-direction: column; gap: 0.2rem;">
                        <span class="stat-val" style="font-size: 2.2rem; font-weight: 800; color: #ffffff;">{{ $readDuration }}</span>
                        <span class="stat-lbl" style="font-size: 0.8rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Estimasi Bacaan</span>
                    </div>
                </div>
            </div>

            <!-- 📱 Human-Built Interactive Simulation Widget -->
            <div class="simulation-widget-container" style="background: rgba(30, 41, 59, 0.4); border: 1.5px solid rgba(255, 255, 255, 0.05); border-radius: 24px; padding: 2rem; box-shadow: 0 20px 40px rgba(0,0,0,0.3); display: flex; flex-direction: column; gap: 1.5rem; position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 0.8rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 1.2rem;">🔊</span>
                        <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: #fff;">Simulasi Pemutar Audio</h3>
                    </div>
                    <span style="font-size: 0.72rem; color: #34d399; background: rgba(52, 211, 153, 0.1); padding: 3px 10px; border-radius: 50px; font-weight: 700;">Uji Coba</span>
                </div>

                <!-- Simulation Book Cover Card -->
                <div style="background: #1e293b; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.2rem; display: flex; gap: 1rem; align-items: center; position: relative; overflow: hidden;">
                    <div style="width: 60px; height: 80px; background: linear-gradient(135deg, #4f46e5, #3b82f6); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 1.1rem; box-shadow: 0 4px 10px rgba(0,0,0,0.3); flex-shrink: 0;">
                        📖
                    </div>
                    <div style="overflow: hidden; flex: 1;">
                        <h4 style="margin: 0 0 0.2rem 0; font-size: 0.95rem; font-weight: 700; color: #fff; white-space: nowrap; overflow: text-overflow;">Panduan Awal Sistem</h4>
                        <p style="margin: 0 0 0.4rem 0; font-size: 0.78rem; color: #94a3b8;">Tim Pengembang Read-Assist</p>
                        <div style="display: inline-flex; align-items: center; gap: 0.3rem; background: #0f172a; padding: 2px 8px; border-radius: 6px; font-size: 0.7rem; color: #e2e8f0; border: 1px solid rgba(255,255,255,0.05);">
                            <span style="color: #818cf8; font-weight: bold;">[QR]</span> RA-GUIDE-2026
                        </div>
                    </div>
                    <!-- Mock QR Code Stamp on physical book -->
                    <div style="background: #fff; padding: 4px; border-radius: 6px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.4); flex-shrink: 0;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=48&data=demo" alt="Mock QR" style="width: 38px; height: 38px; display: block;">
                    </div>
                </div>

                <!-- Simulation Interactive Player Box -->
                <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.04); border-radius: 16px; padding: 1.2rem; display: flex; flex-direction: column; gap: 1rem; text-align: center;">
                    <div id="sim-text-box" style="font-size: 0.88rem; color: #e2e8f0; line-height: 1.5; min-height: 54px; display: flex; align-items: center; justify-content: center; transition: color 0.3s ease;">
                        "Klik tombol putar di bawah untuk mendengar suara panduan aksesibilitas."
                    </div>

                    <!-- Audio Wave Visualizer -->
                    <div id="sim-wave" style="display: flex; justify-content: center; align-items: flex-end; gap: 3px; height: 20px; opacity: 0.3; transition: opacity 0.3s ease;">
                        <div style="width: 3px; height: 6px; background: #818cf8; border-radius: 1px;"></div>
                        <div style="width: 3px; height: 12px; background: #818cf8; border-radius: 1px;"></div>
                        <div style="width: 3px; height: 18px; background: #818cf8; border-radius: 1px;"></div>
                        <div style="width: 3px; height: 10px; background: #818cf8; border-radius: 1px;"></div>
                        <div style="width: 3px; height: 14px; background: #818cf8; border-radius: 1px;"></div>
                        <div style="width: 3px; height: 6px; background: #818cf8; border-radius: 1px;"></div>
                    </div>

                    <div style="display: flex; justify-content: center; gap: 1rem; align-items: center;">
                        <button id="sim-play-btn" onclick="toggleSimPlay()" style="width: 48px; height: 48px; border-radius: 50%; background: #4f46e5; border: none; color: #fff; cursor: pointer; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4); transition: all 0.2s ease;">
                            ▶️
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- 🔄 Human-Built Professional Workflow Section -->
        <section class="workflow-section" style="padding: 1.5rem 0;">
            <div style="text-align: center; margin-bottom: 3.5rem;">
                <h2 class="workflow-title" style="font-size: 2.2rem; font-weight: 800; color: #ffffff; margin: 0 0 0.8rem 0; letter-spacing: -0.5px;">Bagaimana Cara Kerjanya?</h2>
                <p style="color: #94a3b8; font-size: 1.05rem; margin: 0; max-width: 600px; margin: 0 auto; line-height: 1.5;">
                    Tiga pilar utama dalam menghubungkan buku cetak fisik ke suara pendengaran yang inklusif bagi tunanetra.
                </p>
            </div>
            
            <div class="workflow-steps" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">
                <div class="workflow-step-card" style="background: rgba(30, 41, 59, 0.25); border: 1px solid rgba(255, 255, 255, 0.04); border-radius: 20px; padding: 2rem; position: relative; transition: all 0.3s ease;">
                    <div style="width: 44px; height: 44px; background: rgba(79, 70, 229, 0.1); border: 1.5px solid rgba(79, 70, 229, 0.3); color: #818cf8; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; margin-bottom: 1.5rem;">
                        1
                    </div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 0 0 0.8rem 0;">Pendaftaran Buku</h3>
                    <p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.6; margin: 0;">
                        Pengajar atau relawan memasukkan buku ke katalog web dengan mengunggah naskah digital berformat PDF atau EPUB.
                    </p>
                </div>
                <div class="workflow-step-card" style="background: rgba(30, 41, 59, 0.25); border: 1px solid rgba(255, 255, 255, 0.04); border-radius: 20px; padding: 2rem; position: relative; transition: all 0.3s ease;">
                    <div style="width: 44px; height: 44px; background: rgba(79, 70, 229, 0.1); border: 1.5px solid rgba(79, 70, 229, 0.3); color: #818cf8; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; margin-bottom: 1.5rem;">
                        2
                    </div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 0 0 0.8rem 0;">Pemasangan Kode QR</h3>
                    <p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.6; margin: 0;">
                        Sistem menghasilkan kode QR unik yang dapat diunduh, dicetak, lalu ditempelkan pada sampul atau halaman buku fisik terkait.
                    </p>
                </div>
                <div class="workflow-step-card" style="background: rgba(30, 41, 59, 0.25); border: 1px solid rgba(255, 255, 255, 0.04); border-radius: 20px; padding: 2rem; position: relative; transition: all 0.3s ease;">
                    <div style="width: 44px; height: 44px; background: rgba(79, 70, 229, 0.1); border: 1.5px solid rgba(79, 70, 229, 0.3); color: #818cf8; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; margin-bottom: 1.5rem;">
                        3
                    </div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 0 0 0.8rem 0;">Pemindaian & Pemutaran</h3>
                    <p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.6; margin: 0;">
                        Siswa tunanetra cukup memindai kode QR menggunakan kamera HP untuk langsung mendengar pembacaan buku per kalimat.
                    </p>
                </div>
            </div>
        </section>

        <!-- 💡 Premium Features Grid Section -->
        <section class="features-section" style="padding: 1.5rem 0;">
            <div style="text-align: center; margin-bottom: 3.5rem;">
                <h2 class="features-title" style="font-size: 2.2rem; font-weight: 800; color: #ffffff; margin: 0 0 0.8rem 0; letter-spacing: -0.5px;">Fitur Utama Pengalaman</h2>
                <p style="color: #94a3b8; font-size: 1.05rem; margin: 0; max-width: 600px; margin: 0 auto; line-height: 1.5;">
                    Didesain secara khusus agar sangat ramah bagi tunanetra dan penyandang keterbatasan penglihatan.
                </p>
            </div>

            <div class="features-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">
                <div class="feature-card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass); border-radius: 20px; padding: 2rem; display: flex; flex-direction: column; gap: 1rem; transition: all 0.3s ease;">
                    <div style="font-size: 1.8rem; color: #818cf8; margin-bottom: 0.5rem;">♿</div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 0;">Aksesibilitas Tinggi</h3>
                    <p style="color: #94a3b8; font-size: 0.88rem; line-height: 1.6; margin: 0;">
                        Kontras tinggi, pembesar teks, serta navigasi keyboard lengkap dengan tombol pintasan seperti <kbd>Spasi</kbd> untuk jeda audio.
                    </p>
                </div>
                <div class="feature-card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass); border-radius: 20px; padding: 2rem; display: flex; flex-direction: column; gap: 1rem; transition: all 0.3s ease;">
                    <div style="font-size: 1.8rem; color: #818cf8; margin-bottom: 0.5rem;">📍</div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 0;">Penyimpanan Progres Otomatis</h3>
                    <p style="color: #94a3b8; font-size: 0.88rem; line-height: 1.6; margin: 0;">
                        Progres kalimat terakhir otomatis tersimpan di peranti Anda. Memindai ulang kode QR akan langsung melanjutkan ke kalimat sebelumnya.
                    </p>
                </div>
                <div class="feature-card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass); border-radius: 20px; padding: 2rem; display: flex; flex-direction: column; gap: 1rem; transition: all 0.3s ease;">
                    <div style="font-size: 1.8rem; color: #818cf8; margin-bottom: 0.5rem;">📲</div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 0;">Inklusif & Tanpa Hambatan</h3>
                    <p style="color: #94a3b8; font-size: 0.88rem; line-height: 1.6; margin: 0;">
                        Pengguna tidak perlu menginstal aplikasi tambahan di HP. Pemutaran audio langsung berjalan dari browser bawaan HP secara responsif.
                    </p>
                </div>
            </div>
        </section>
    </div>

    <!-- Simulation Script -->
    <script>
        let simSpeaking = false;
        let simUtterance = null;

        function toggleSimPlay() {
            const btn = document.getElementById('sim-play-btn');
            const wave = document.getElementById('sim-wave');
            const textBox = document.getElementById('sim-text-box');

            if (simSpeaking) {
                // Stop speech
                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                }
                simSpeaking = false;
                btn.innerText = "▶️";
                btn.style.background = "#4f46e5";
                wave.style.opacity = "0.3";
                textBox.innerText = '"Simulasi dihentikan. Klik tombol putar kembali untuk mendengarkan."';
                textBox.style.color = "#94a3b8";
            } else {
                // Start speech
                const textToSpeak = "Selamat datang di platform pembaca buku aksesibilitas Read Assist. Silakan tempel kode QR pada buku fisik Anda, kemudian pindai stiker tersebut menggunakan kamera smartphone Anda untuk mulai mendengarkan audio pembacaan naskah secara mandiri.";
                
                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel(); // cancel any active speech
                    simUtterance = new SpeechSynthesisUtterance(textToSpeak);
                    simUtterance.lang = 'id-ID';
                    
                    simUtterance.onstart = function() {
                        simSpeaking = true;
                        btn.innerText = "⏸️";
                        btn.style.background = "#ef4444";
                        wave.style.opacity = "1";
                        textBox.innerText = '"Selamat datang di platform pembaca buku aksesibilitas Read Assist. Silakan tempel kode QR pada buku fisik Anda..."';
                        textBox.style.color = "#a5b4fc";
                    };

                    simUtterance.onend = function() {
                        simSpeaking = false;
                        btn.innerText = "▶️";
                        btn.style.background = "#4f46e5";
                        wave.style.opacity = "0.3";
                        textBox.innerText = '"Simulasi selesai. Klik tombol putar di bawah untuk mendengar kembali."';
                        textBox.style.color = "#34d399";
                    };

                    simUtterance.onerror = function() {
                        simSpeaking = false;
                        btn.innerText = "▶️";
                        btn.style.background = "#4f46e5";
                        wave.style.opacity = "0.3";
                        textBox.innerText = '"Simulasi gagal diputar. Browser Anda mungkin tidak mendukung fitur ini."';
                        textBox.style.color = "#ef4444";
                    };

                    window.speechSynthesis.speak(simUtterance);
                } else {
                    textBox.innerText = '"Simulasi gagal diputar. Peranti atau browser Anda tidak mendukung Web Speech Synthesis."';
                    textBox.style.color = "#ef4444";
                }
            }
        }
    </script>
@endsection
