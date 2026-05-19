@extends('layouts.app')

@section('content')
    <div class="landing-wrapper" style="margin-top: 1rem;">
        <!-- 🚀 Hero & Illustration Section -->
        <section class="hero-section">
            <div class="hero-content">
                <span class="user-role" style="font-size: 0.8rem; background: rgba(99, 102, 241, 0.15); padding: 5px 12px; border-radius: 20px; color: var(--accent-primary); width: fit-content; font-weight: 700; border: 1px solid rgba(99, 102, 241, 0.3);">
                    ⚡ SISTEM READ-ASSIST QR-AUDIO
                </span>
                <h1 class="hero-title text-gradient">Sistem Pendukung Belajar Mandiri untuk Tunanetra</h1>
                <p class="hero-subtitle">
                    Gabungan inovasi kode QR unik dan pemutar audio pintar berbasis web. Membantu rekan tunanetra membaca buku fisik secara mandiri melalui smartphone tanpa aplikasi tambahan.
                </p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.5rem;">
                    <a href="{{ route('audio-books.index') }}" class="btn-cta">
                        📚 Mulai Belajar Mandiri
                    </a>
                </div>

                <!-- 📊 Statistik Realtime -->
                <div class="stats-bar">
                    <div class="stat-item">
                        <span class="stat-val">{{ $bookCount }}</span>
                        <span class="stat-lbl">Buku Audio</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-val">{{ $charCount }}</span>
                        <span class="stat-lbl">Karakter</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-val">{{ $readDuration }}</span>
                        <span class="stat-lbl">Durasi Baca</span>
                    </div>
                </div>
            </div>

            <!-- 📱 Ilustrasi QR & Audio (CSS-based & Glowing) -->
            <div class="illustration-container">
                <div class="phone-mockup">
                    <div class="phone-notch"></div>
                    <div class="phone-screen">
                        <div style="width: 100%; height: 20px; display: flex; justify-content: space-between; font-size: 0.6rem; opacity: 0.6; padding: 0 4px;">
                            <span>9:41</span>
                            <span>📶 🔋</span>
                        </div>
                        <div class="phone-play-btn">🔊</div>
                        <div style="display: flex; flex-direction: column; width: 100%; gap: 6px; align-items: center;">
                            <span style="font-size: 0.7rem; font-weight: 700; color: #fff; text-align: center;">Membaca Buku...</span>
                            <div class="phone-progress-bar">
                                <div class="phone-progress-fill"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Glowing QR Box -->
                <div class="glowing-qr-box">
                    <div class="scan-beam"></div>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('audio-books.index')) }}" alt="QR Code" style="width: 100px; height: 100px; display: block; border-radius: 4px;">
                </div>

                <!-- Animated Audio Waves -->
                <div class="audio-waves-container">
                    <div class="audio-wave-bar"></div>
                    <div class="audio-wave-bar"></div>
                    <div class="audio-wave-bar"></div>
                    <div class="audio-wave-bar"></div>
                    <div class="audio-wave-bar"></div>
                </div>
            </div>
        </section>

        <!-- 🔄 Workflow Visual Section -->
        <section class="workflow-section">
            <h2 class="workflow-title text-gradient">Bagaimana Cara Kerjanya?</h2>
            <p style="text-align: center; color: var(--text-secondary); max-width: 600px; margin: -1.5rem auto 1rem auto; font-size: 1.05rem;">Tiga langkah mudah menghubungkan media cetak fisik ke platform pendengaran audio mandiri.</p>
            
            <div class="workflow-steps">
                <div class="workflow-step">
                    <div class="workflow-num">1</div>
                    <h3 class="workflow-step-title">Unggah Berkas Buku</h3>
                    <p class="workflow-step-desc">Pengajar atau relawan mengunggah berkas PDF/EPUB bahan ajar ke katalog sistem web.</p>
                </div>
                <div class="workflow-step">
                    <div class="workflow-num">2</div>
                    <h3 class="workflow-step-title">Pindai Kode QR</h3>
                    <p class="workflow-step-desc">Tuna netra memindai label QR code yang ditempelkan di buku fisik menggunakan smartphone.</p>
                </div>
                <div class="workflow-step">
                    <div class="workflow-num">3</div>
                    <h3 class="workflow-step-title">Dengarkan Audio</h3>
                    <p class="workflow-step-desc">Pemutar suara interaktif terbuka otomatis dan membacakan buku per kalimat secara realtime.</p>
                </div>
            </div>
        </section>

        <!-- 💡 Feature Cards Section -->
        <section class="features-section">
            <h2 class="features-title text-gradient">Fitur Unggulan Sistem</h2>
            <p style="text-align: center; color: var(--text-secondary); max-width: 600px; margin: -1.5rem auto 1rem auto; font-size: 1.05rem;">Dibuat dengan memprioritaskan kenyamanan aksesibilitas tunanetra.</p>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon-wrapper">♿</div>
                    <h3 class="feature-card-title">Aksesibilitas Khusus</h3>
                    <p class="feature-card-desc">Dilengkapi kontrol kontras tinggi, pembesar teks, serta pintasan keyboard spasi/arah panah untuk navigasi audio.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-wrapper">📍</div>
                    <h3 class="feature-card-title">Progress Auto-Save</h3>
                    <p class="feature-card-desc">Progress kalimat terakhir disimpan di browser. Pemindaian ulang QR otomatis melanjutkan ke kalimat terakhir dibaca.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-wrapper">📲</div>
                    <h3 class="feature-card-title">Tanpa Instalasi Apps</h3>
                    <p class="feature-card-desc">Tidak perlu mengunduh aplikasi terpisah. Cukup gunakan browser bawaan HP setelah melakukan pemindaian QR.</p>
                </div>
            </div>
        </section>
    </div>
@endsection
