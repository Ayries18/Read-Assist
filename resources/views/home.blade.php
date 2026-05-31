@extends('layouts.app')

@section('content')
    <div class="mt-6 flex flex-col gap-18 mb-20">
        
        <!-- Hero & Interactive Simulation Section -->
        <section class="grid grid-cols-1 lg:grid-cols-[1.2fr_0.8fr] gap-8 items-center py-8">
            <div class="flex flex-col gap-6">
                <div class="flex items-center gap-2">
                    <span class="text-[0.72rem] bg-violet-500/10 px-3 py-1.5 rounded-full text-violet-400 font-bold border border-violet-500/15 uppercase tracking-wider">
                        Platform Aksesibilitas Buku
                    </span>
                </div>
                <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight text-white tracking-tight m-0">
                    Jembatan Audio untuk <br><span class="text-gradient">Membaca Buku Fisik</span>
                </h1>
                <p class="text-base text-slate-400 leading-relaxed max-w-xl m-0">
                    Read-Assist mendampingi penyandang tunanetra untuk membaca buku cetak secara mandiri. Cukup pindai label QR unik yang ditempel pada buku fisik untuk mendengarkan pembacaan teks otomatis langsung dari smartphone Anda.
                </p>
                <div class="flex gap-4 flex-wrap mt-3 items-center">
                    <a href="{{ route('audio-books.index') }}" class="btn btn-primary rounded-lg px-7 py-3 text-sm flex items-center gap-2">
                        Buka Katalog Buku
                    </a>
                    @if (!session()->has('auth_role'))
                        <a href="{{ route('login') }}" class="btn btn-ghost rounded-lg px-6 py-3 text-sm font-semibold">
                            Masuk Ke Akun
                        </a>
                    @endif
                </div>

                <!-- Service Stats -->
                <div class="grid grid-cols-3 gap-3 sm:gap-6 mt-8 border-t border-white/10 pt-8">
                    <div class="flex flex-col gap-1">
                        <span class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white">{{ $bookCount }}</span>
                        <span class="text-[0.6rem] sm:text-xs text-slate-400 font-semibold uppercase tracking-wider">Buku Terdaftar</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white">{{ $charCount }}</span>
                        <span class="text-[0.6rem] sm:text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Karakter</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white">{{ $readDuration }}</span>
                        <span class="text-[0.6rem] sm:text-xs text-slate-400 font-semibold uppercase tracking-wider">Estimasi Bacaan</span>
                    </div>
                </div>
            </div>

            <!-- Interactive Simulation Widget -->
            <div class="simulation-widget-container border shadow-sm p-6 flex flex-col gap-5 relative" style="background: #111216; border-color: rgba(255, 255, 255, 0.04); border-radius: 12px;">
                <div class="flex justify-between items-center border-b border-white/5 pb-3">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-400"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        <h3 class="m-0 text-sm font-bold text-white">Simulasi Pemutar Audio</h3>
                    </div>
                    <span class="text-[0.7rem] text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full font-bold">Uji Coba</span>
                </div>

                <!-- Simulation Book Cover Card -->
                <div class="border rounded-lg p-4 flex gap-4 items-center relative overflow-hidden" style="background: #16171b; border-color: rgba(255, 255, 255, 0.04);">
                    <div class="w-12 h-16 bg-violet-600 rounded flex items-center justify-center text-white shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17"/></svg>
                    </div>
                    <div class="overflow-hidden flex-1">
                        <h4 class="m-0 mb-1 text-xs font-bold text-white truncate">Panduan Awal Sistem</h4>
                        <p class="m-0 mb-1 text-[0.7rem] text-slate-400">Tim Pengembang Read-Assist</p>
                        <div class="inline-flex items-center gap-1 bg-slate-900 px-2 py-0.5 rounded text-[0.65rem] text-slate-300 border border-white/5">
                            <span class="text-violet-400 font-bold">[QR]</span> RA-GUIDE-2026
                        </div>
                    </div>
                    <!-- Mock QR Code Stamp on physical book -->
                    <div class="bg-white p-1 rounded flex items-center justify-center shrink-0">
                        <img src="{{ route('qr-code.generate', ['data' => 'demo', 'size' => 40]) }}" alt="Mock QR" class="w-8 h-8 block">
                    </div>
                </div>

                <!-- Simulation Interactive Player Box -->
                <div class="border rounded-lg p-4 flex flex-col gap-4 text-center" style="background: rgba(0, 0, 0, 0.1); border-color: rgba(255, 255, 255, 0.04);">
                    <div id="sim-text-box" class="text-xs text-slate-400 leading-relaxed min-h-[50px] flex items-center justify-center transition-colors duration-300">
                        "Klik tombol putar di bawah untuk mendengar suara panduan aksesibilitas."
                    </div>

                    <!-- Audio Wave Visualizer -->
                    <div id="sim-wave" class="flex justify-center items-end gap-0.5 h-4 opacity-30 transition-opacity duration-300">
                        <div class="w-[3px] h-1.5 bg-violet-400 rounded-sm"></div>
                        <div class="w-[3px] h-3 bg-violet-400 rounded-sm"></div>
                        <div class="w-[3px] h-[14px] bg-violet-400 rounded-sm"></div>
                        <div class="w-[3px] h-2.5 bg-violet-400 rounded-sm"></div>
                        <div class="w-[3px] h-3.5 bg-violet-400 rounded-sm"></div>
                        <div class="w-[3px] h-1.5 bg-violet-400 rounded-sm"></div>
                    </div>

                    <div class="flex justify-center gap-4 items-center">
                        <button id="sim-play-btn" onclick="toggleSimPlay()" class="w-10 h-10 rounded-full bg-violet-600 border-none text-white cursor-pointer flex items-center justify-center transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Workflow Section -->
        <section class="py-6">
            <div class="text-center mb-14">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight m-0 mb-3">Bagaimana Cara Kerjanya?</h2>
                <p class="text-slate-400 text-base m-0 max-w-[600px] mx-auto leading-relaxed">
                    Tiga pilar utama dalam menghubungkan buku cetak fisik ke suara pendengaran yang inklusif bagi tunanetra.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card border shadow-sm p-6 relative transition-all duration-300" style="background: #111216; border-color: rgba(255, 255, 255, 0.04); border-radius: 8px;">
                    <div class="w-10 h-10 bg-violet-500/10 border border-violet-500/15 text-violet-400 rounded flex items-center justify-center font-extrabold text-base mb-6">
                        1
                    </div>
                    <h3 class="text-base font-bold text-white m-0 mb-3">Pendaftaran Buku</h3>
                    <p class="text-slate-400 text-xs leading-relaxed m-0">
                        Pengajar atau relawan memasukkan buku ke katalog web dengan mengunggah naskah digital berformat PDF atau EPUB.
                    </p>
                </div>
                <div class="card border shadow-sm p-6 relative transition-all duration-300" style="background: #111216; border-color: rgba(255, 255, 255, 0.04); border-radius: 8px;">
                    <div class="w-10 h-10 bg-violet-500/10 border border-violet-500/15 text-violet-400 rounded flex items-center justify-center font-extrabold text-base mb-6">
                        2
                    </div>
                    <h3 class="text-base font-bold text-white m-0 mb-3">Pemasangan Kode QR</h3>
                    <p class="text-slate-400 text-xs leading-relaxed m-0">
                        Sistem menghasilkan kode QR unik yang dapat diunduh, dicetak, lalu ditempelkan pada sampul atau halaman buku fisik terkait.
                    </p>
                </div>
                <div class="card border shadow-sm p-6 relative transition-all duration-300" style="background: #111216; border-color: rgba(255, 255, 255, 0.04); border-radius: 8px;">
                    <div class="w-10 h-10 bg-violet-500/10 border border-violet-500/15 text-violet-400 rounded flex items-center justify-center font-extrabold text-base mb-6">
                        3
                    </div>
                    <h3 class="text-base font-bold text-white m-0 mb-3">Pemindaian & Pemutaran</h3>
                    <p class="text-slate-400 text-xs leading-relaxed m-0">
                        Siswa tunanetra cukup memindai kode QR menggunakan kamera HP untuk langsung mendengar pembacaan buku per kalimat.
                    </p>
                </div>
            </div>
        </section>

        <!-- Features Grid Section -->
        <section class="py-6">
            <div class="text-center mb-14">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight m-0 mb-3">Fitur Utama Pengalaman</h2>
                <p class="text-slate-400 text-base m-0 max-w-[600px] mx-auto leading-relaxed">
                    Didesain secara khusus agar sangat ramah bagi tunanetra dan penyandang keterbatasan penglihatan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card border shadow-sm p-6 flex flex-col gap-4 transition-all duration-300" style="background: #111216; border-color: rgba(255, 255, 255, 0.04); border-radius: 8px;">
                    <div class="text-violet-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="16" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h2"/><circle cx="9" cy="7" r="4"/><path d="M1 21v-2a4 4 0 0 1 4-4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-white m-0">Aksesibilitas Tinggi</h3>
                    <p class="text-slate-400 text-xs leading-relaxed m-0">
                        Kontras tinggi, pembesar teks, serta navigasi keyboard lengkap dengan tombol pintasan seperti <kbd class="kbd kbd-sm text-[10px]">Spasi</kbd> untuk jeda audio.
                    </p>
                </div>
                <div class="card border shadow-sm p-6 flex flex-col gap-4 transition-all duration-300" style="background: #111216; border-color: rgba(255, 255, 255, 0.04); border-radius: 8px;">
                    <div class="text-violet-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-white m-0">Penyimpanan Progres Otomatis</h3>
                    <p class="text-slate-400 text-xs leading-relaxed m-0">
                        Progres kalimat terakhir otomatis tersimpan di peranti Anda. Memindai ulang kode QR akan langsung melanjutkan ke kalimat sebelumnya.
                    </p>
                </div>
                <div class="card border shadow-sm p-6 flex flex-col gap-4 transition-all duration-300" style="background: #111216; border-color: rgba(255, 255, 255, 0.04); border-radius: 8px;">
                    <div class="text-violet-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-white m-0">Inklusif & Tanpa Hambatan</h3>
                    <p class="text-slate-400 text-xs leading-relaxed m-0">
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
                btn.innerHTML = "\u25B6";
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
                        btn.innerHTML = "\u23F8";
                        btn.style.background = "#ef4444";
                        wave.style.opacity = "1";
                        textBox.innerText = '"Selamat datang di platform pembaca buku aksesibilitas Read Assist. Silakan tempel kode QR pada buku fisik Anda..."';
                        textBox.style.color = "#a5b4fc";
                    };

                    simUtterance.onend = function() {
                        simSpeaking = false;
                        btn.innerHTML = "\u25B6";
                        btn.style.background = "#4f46e5";
                        wave.style.opacity = "0.3";
                        textBox.innerText = '"Simulasi selesai. Klik tombol putar di bawah untuk mendengar kembali."';
                        textBox.style.color = "#34d399";
                    };

                    simUtterance.onerror = function() {
                        simSpeaking = false;
                        btn.innerHTML = "\u25B6";
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
