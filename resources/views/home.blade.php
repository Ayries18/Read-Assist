@extends('layouts.app')

@section('content')
    <div class="mt-2 flex flex-col gap-18 mb-20">
        
        <!-- Hero & Interactive Simulation Section -->
        <section class="grid grid-cols-1 lg:grid-cols-[1.2fr_0.8fr] gap-10 lg:gap-12 items-center py-4">
            <div class="flex flex-col gap-5">
                <div class="flex items-center gap-2">
                    <span class="text-[0.72rem] bg-[#b8860b]/10 px-3 py-1.5 rounded-full font-bold border border-[#b8860b]/25 uppercase tracking-wider">
                        Platform Aksesibilitas Buku
                    </span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-extrabold leading-[1.15] text-black tracking-tight m-0 max-w-2xl">
                    Jembatan Audio untuk <br class="hidden sm:block"><span class="text-gradient">Membaca Buku Fisik</span>
                </h1>
                <p class="text-base text-slate-600 leading-[1.7] max-w-xl m-0">
                    Read-Assist mendampingi penyandang tunanetra untuk membaca buku cetak secara mandiri. Cukup pindai label QR unik yang ditempel pada buku fisik untuk mendengarkan pembacaan teks otomatis langsung dari smartphone Anda.
                </p>
                <div class="flex gap-4 flex-wrap mt-1 items-center">
<a href="{{ route('audio-books.index') }}" class="btn btn-primary btn-hero px-7 py-3 text-sm flex items-center gap-2">
                        Buka Katalog Buku
                    </a>
                    @if (!session()->has('auth_role'))
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-hero--ghost px-6 py-3 text-sm font-semibold">
                            Masuk Ke Akun
                        </a>
                    @endif
                </div>

<!-- Service Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-3 mt-7 border-t pt-6 gap-y-5 sm:gap-y-0 stat-row">
                    <div class="flex flex-col gap-1 sm:pr-4">
                        <span class="text-4xl font-extrabold text-black leading-none tracking-tight">{{ $bookCount }}</span>
                        <span class="text-[0.7rem] font-medium text-gray-600 uppercase tracking-wider">Buku Terdaftar</span>
                    </div>
                    <div class="flex flex-col gap-1 pt-5 sm:pt-0 border-t sm:border-t-0 sm:border-l sm:px-4 stat-cell">
                        <span class="text-4xl font-extrabold text-black leading-none tracking-tight">{{ $charCount }}</span>
                        <span class="text-[0.7rem] font-medium text-gray-600 uppercase tracking-wider">Total Karakter</span>
                    </div>
                    <div class="flex flex-col gap-1 pt-5 sm:pt-0 border-t sm:border-t-0 sm:border-l sm:pl-4 stat-cell">
                        <span class="text-4xl font-extrabold text-black leading-none tracking-tight">{{ $readDuration }}</span>
                        <span class="text-[0.7rem] font-medium text-gray-600 uppercase tracking-wider">Estimasi Bacaan</span>
                    </div>
                </div>
            </div>

            <!-- Ilustrasi Penyandang Disabilitas Netra -->
            <figure class="hero-visual relative overflow-hidden">
                <img
                    src="https://images.pexels.com/photos/6606144/pexels-photo-6606144.jpeg?auto=compress&cs=tinysrgb&w=2000&h=1500&dpr=1"
                    alt="Wanita penyandang tunanetra membaca buku braille dengan sentuhan jarinya"
                    class="w-full h-full object-cover block"
                    loading="lazy"
                    width="2000"
                    height="1500"
                >
<figcaption class="hero-caption absolute inset-x-0 bottom-0 p-5 sm:p-6 flex items-center gap-3">
                    <div class="hero-badge w-10 h-10 rounded-full flex items-center justify-center text-[#b8860b] shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </div>
                    <div>
                        <span class="hero-tagline block text-[0.68rem] uppercase tracking-wider font-bold mb-0.5">Disabilitas Netra</span>
                        <p class="m-0 text-xs text-slate-200 leading-snug">Membaca buku braille secara mandiri - kini didukung audio Read-Assist.</p>
                    </div>
                </figcaption>
            </figure>
        </section>

        <!-- Workflow Section -->
        <section class="py-6">
            <div class="text-center mb-14">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-black tracking-tight m-0 mb-3">Bagaimana Cara Kerjanya?</h2>
                <p class="text-slate-600 text-base m-0 max-w-[600px] mx-auto leading-relaxed">
                    Tiga pilar utama dalam menghubungkan buku cetak fisik ke suara pendengaran yang inklusif bagi tunanetra.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<div class="card border shadow-sm p-6 relative transition-all duration-300 hero-card">
                    <div class="w-10 h-10 bg-[#b8860b]/10 border border-[#b8860b]/25 text-[#7a5a00] rounded flex items-center justify-center font-extrabold text-base mb-6">
                        1
                    </div>
                    <h3 class="text-base font-bold text-black m-0 mb-3">Pendaftaran Buku</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Pengajar atau relawan memasukkan buku ke katalog web dengan mengunggah naskah digital berformat PDF atau EPUB.
                    </p>
                </div>
<div class="card border shadow-sm p-6 relative transition-all duration-300 hero-card">
                    <div class="w-10 h-10 bg-[#b8860b]/10 border border-[#b8860b]/25 text-[#7a5a00] rounded flex items-center justify-center font-extrabold text-base mb-6">
                        2
                    </div>
                    <h3 class="text-base font-bold text-black m-0 mb-3">Pemasangan Kode QR</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Sistem menghasilkan kode QR unik yang dapat diunduh, dicetak, lalu ditempelkan pada sampul atau halaman buku fisik terkait.
                    </p>
                </div>
<div class="card border shadow-sm p-6 relative transition-all duration-300 hero-card">
                    <div class="w-10 h-10 bg-[#b8860b]/10 border border-[#b8860b]/25 text-[#7a5a00] rounded flex items-center justify-center font-extrabold text-base mb-6">
                        3
                    </div>
                    <h3 class="text-base font-bold text-black m-0 mb-3">Pemindaian & Pemutaran</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Siswa tunanetra cukup memindai kode QR menggunakan kamera HP untuk langsung mendengar pembacaan buku per kalimat.
                    </p>
                </div>
            </div>
        </section>

        <!-- Features Grid Section -->
        <section class="py-6">
            <div class="text-center mb-14">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-black tracking-tight m-0 mb-3">Fitur Utama Pengalaman</h2>
                <p class="text-slate-600 text-base m-0 max-w-[600px] mx-auto leading-relaxed">
                    Didesain secara khusus agar sangat ramah bagi tunanetra dan penyandang keterbatasan penglihatan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<div class="card border shadow-sm p-6 flex flex-col gap-4 transition-all duration-300 hero-card">
                    <div class="text-[#b8860b]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="16" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h2"/><circle cx="9" cy="7" r="4"/><path d="M1 21v-2a4 4 0 0 1 4-4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-black m-0">Aksesibilitas Tinggi</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Kontras tinggi, pembesar teks, serta navigasi keyboard lengkap dengan tombol pintasan seperti <kbd class="kbd kbd-sm text-[10px] text-black">Spasi</kbd> untuk jeda audio.
                    </p>
                </div>
<div class="card border shadow-sm p-6 flex flex-col gap-4 transition-all duration-300 hero-card">
                    <div class="text-[#b8860b]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-black m-0">Penyimpanan Progres Otomatis</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Progres kalimat terakhir otomatis tersimpan di peranti Anda. Memindai ulang kode QR akan langsung melanjutkan ke kalimat sebelumnya.
                    </p>
                </div>
<div class="card border shadow-sm p-6 flex flex-col gap-4 transition-all duration-300 hero-card">
                    <div class="text-[#b8860b]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-black m-0">Inklusif & Tanpa Hambatan</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Pengguna tidak perlu menginstal aplikasi tambahan di HP. Pemutaran audio langsung berjalan dari browser bawaan HP secara responsif.
                    </p>
                </div>
            </div>
        </section>
    </div>
@endsection

