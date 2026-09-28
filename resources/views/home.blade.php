@extends('layouts.app')

@section('content')
    <div class="mt-2 flex flex-col gap-18 mb-20">

        <!-- Hero -->
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
                    <a href="{{ route('audio-books.index') }}" class="btn btn-primary btn-hero px-7 py-3 text-sm" aria-label="Mulai mendengarkan buku dari katalog Read-Assist">
                        Mulai Mendengarkan
                    </a>
                    <a href="{{ route('audio-books.index') }}" class="btn btn-ghost btn-hero--ghost px-6 py-3 text-sm font-semibold" aria-label="Jelajahi seluruh katalog buku audio">
                        Jelajahi Katalog
                    </a>
                    @if (!session()->has('auth_role'))
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-hero--ghost px-6 py-3 text-sm font-semibold" aria-label="Masuk ke akun Read-Assist">
                            Masuk Ke Akun
                        </a>
                    @endif
                </div>

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

            <figure class="hero-visual relative overflow-hidden">
                <picture>
                    <source
                        type="image/webp"
                        srcset="{{ $hero720 }} 720w, {{ $hero1200 }} 1200w, {{ $hero1800 }} 1800w"
                        sizes="(max-width: 1024px) 100vw, 40vw"
                    >
                    <img
                        src="{{ $heroJpeg }}"
                        srcset="{{ $hero720 }} 720w, {{ $hero1200 }} 1200w, {{ $hero1800 }} 1800w"
                        sizes="(max-width: 1024px) 100vw, 40vw"
                        alt="Seorang perempuan penyandang tunanetra membaca buku braille dengan menempelkan jarinya di halaman"
                        class="w-full h-full object-cover block"
                        width="1200"
                        height="900"
                        loading="eager"
                        decoding="async"
                        fetchpriority="high"
                    >
                </picture>
                <figcaption class="hero-caption absolute inset-x-0 bottom-0 p-5 sm:p-6 flex items-center gap-3">
                    <div class="hero-badge w-10 h-10 rounded-full flex items-center justify-center text-[#b8860b] shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </div>
                    <div>
                        <span class="hero-tagline block text-[0.68rem] uppercase tracking-wider font-bold mb-0.5">Disabilitas Netra</span>
                        <p class="m-0 text-xs text-slate-200 leading-snug">Membaca buku braille secara mandiri - kini didukung audio Read-Assist.</p>
                    </div>
                </figcaption>
            </figure>
        </section>

        <!-- Tentang Read-Assist -->
        <section class="py-6" aria-labelledby="h2-tentang">
            <div class="text-center mb-10">
                <h2 id="h2-tentang" class="text-3xl sm:text-4xl font-extrabold text-black tracking-tight m-0 mb-3">Tentang Read-Assist</h2>
                <p class="text-slate-600 text-base m-0 max-w-[640px] mx-auto leading-relaxed">
                    Platform aksesibilitas yang mengubah buku cetak menjadi buku audio.
                </p>
            </div>

            <div class="max-w-[900px] mx-auto flex flex-col gap-5 text-slate-600 text-sm sm:text-base leading-[1.8]">
                <p class="m-0">
                    Read-Assist lahir dari satu masalah yang nyata, yaitu akses terhadap bacaan di Indonesia masih sangat bergantung pada bantuan orang lain. Penyandang tunanetra yang ingin membaca novel, buku pelajaran, atau laporan tahunan sering harus bersandar pada pembaca lain. Membaca bersama memang mungkin, tetapi koleksi yang dikelola orang lain tidak selalu tersedia, dan waktu membaca pun tidak bisa diatur sendiri. Read-Assist menjawab masalah itu dengan cara yang sederhana: letakkan naskah digital di balik kode QR, lalu biarkan suara membacakannya sendiri.
                </p>
                <p class="m-0">
                    Secara teknis, Read-Assist melakukan empat langkah berurutan. Pertama, naskah digital dalam format PDF atau EPUB diunggah ke katalog. Kedua, teks diekstraksi secara otomatis lalu dipecah menjadi potongan kalimat yang berukuran wajar. Ketiga, setiap potongan disintesis menjadi berkas MP3 melalui mesin text-to-speech. Keempat, seluruh berkas MP3 digabungkan menjadi satu buku audio utuh yang siap diputar per kalimat.
                </p>
                <p class="m-0">
                    Yang membedakan Read-Assist dari pemutar audio biasa adalah pemutarannya dipisah per kalimat. Pembaca dapat menyimak satu kalimat, menekan tombol untuk melewati kalimat berikutnya, atau mengulang kalimat yang tadi terlewat. Progres disimpan otomatis sehingga bacaan panjang cukup dilanjutkan, bukan diulang dari awal. Semua fitur ini berjalan langsung di dalam browser, tanpa aplikasi tambahan yang harus dipasang.
                </p>
            </div>
        </section>

        <!-- Cara Kerja -->
        <section class="py-6" aria-labelledby="h2-cara-kerja">
            <div class="text-center mb-14">
                <h2 id="h2-cara-kerja" class="text-3xl sm:text-4xl font-extrabold text-black tracking-tight m-0 mb-3">Cara Kerja</h2>
                <p class="text-slate-600 text-base m-0 max-w-[600px] mx-auto leading-relaxed">
                    Tiga langkah sederhana menghubungkan buku cetak fisik ke suara yang inklusif.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card border shadow-sm p-6 relative transition-all duration-300 hero-card">
                    <div class="w-10 h-10 bg-[#b8860b]/10 border border-[#b8860b]/25 text-[#7a5a00] rounded flex items-center justify-center font-extrabold text-base mb-6" aria-hidden="true">1</div>
                    <h3 class="text-base font-bold text-black m-0 mb-3">Pendaftaran Buku</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Pengajar atau relawan memasukkan buku ke katalog dengan mengunggah naskah digital berformat PDF atau EPUB. Setelah diunggah, naskah diekstraksi menjadi teks lalu dipecah menjadi potongan kalimat.
                    </p>
                </div>
                <div class="card border shadow-sm p-6 relative transition-all duration-300 hero-card">
                    <div class="w-10 h-10 bg-[#b8860b]/10 border border-[#b8860b]/25 text-[#7a5a00] rounded flex items-center justify-center font-extrabold text-base mb-6" aria-hidden="true">2</div>
                    <h3 class="text-base font-bold text-black m-0 mb-3">Pemasangan Kode QR</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Sistem menghasilkan kode QR unik untuk setiap buku. Kode dapat diunduh, dicetak, lalu ditempelkan pada sampul atau halaman buku fisik terkait agar mudah ditemukan pembaca.
                    </p>
                </div>
                <div class="card border shadow-sm p-6 relative transition-all duration-300 hero-card">
                    <div class="w-10 h-10 bg-[#b8860b]/10 border border-[#b8860b]/25 text-[#7a5a00] rounded flex items-center justify-center font-extrabold text-base mb-6" aria-hidden="true">3</div>
                    <h3 class="text-base font-bold text-black m-0 mb-3">Pemindaian dan Pemutaran</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Penyandang tunanetra cukup memindai kode QR memakai kamera ponsel. Halaman pemutar langsung terbuka dan pembacaan berjalan per kalimat, lengkap dengan navigasi keyboard dan pengingat suara.
                    </p>
                </div>
            </div>
        </section>

        <!-- Fitur Utama -->
        <section class="py-6" aria-labelledby="h2-fitur">
            <div class="text-center mb-14">
                <h2 id="h2-fitur" class="text-3xl sm:text-4xl font-extrabold text-black tracking-tight m-0 mb-3">Fitur Utama</h2>
                <p class="text-slate-600 text-base m-0 max-w-[600px] mx-auto leading-relaxed">
                    Didesain khusus agar tetap ramah bagi penyandang tunanetra dan pengguna pembaca layar.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card border shadow-sm p-6 flex flex-col gap-4 transition-all duration-300 hero-card">
                    <div class="text-[#b8860b]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="16" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h2"/><circle cx="9" cy="7" r="4"/><path d="M1 21v-2a4 4 0 0 1 4-4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-black m-0">Aksesibilitas Tinggi</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Kontras tinggi, navigasi keyboard lengkap, dan pintasan seperti <kbd class="kbd kbd-sm text-[10px] text-black">Spasi</kbd> untuk jeda serta <kbd class="kbd kbd-sm text-[10px] text-black">Panah Kiri</kbd> dan <kbd class="kbd kbd-sm text-[10px] text-black">Panah Kanan</kbd> untuk berpindah kalimat.
                    </p>
                </div>
                <div class="card border shadow-sm p-6 flex flex-col gap-4 transition-all duration-300 hero-card">
                    <div class="text-[#b8860b]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-black m-0">Penyimpanan Progres Otomatis</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Kalimat terakhir otomatis tersimpan di perangkat Anda. Memindai ulang kode QR akan langsung melanjutkan ke kalimat sebelumnya tanpa perlu mencari posisi bacaan secara manual.
                    </p>
                </div>
                <div class="card border shadow-sm p-6 flex flex-col gap-4 transition-all duration-300 hero-card">
                    <div class="text-[#b8860b]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 1.65 1.65 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.06a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-black m-0">Inklusif dan Tanpa Hambatan</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Pengguna tidak perlu memasang aplikasi tambahan. Pemutaran berjalan dari browser bawaan ponsel secara responsif, dengan ukuran aset yang disesuaikan jaringan seluler.
                    </p>
                </div>
            </div>
        </section>

        <!-- Manfaat bagi Tunanetra -->
        <section class="py-6" aria-labelledby="h2-manfaat">
            <div class="text-center mb-10">
                <h2 id="h2-manfaat" class="text-3xl sm:text-4xl font-extrabold text-black tracking-tight m-0 mb-3">Manfaat bagi Tunanetra</h2>
                <p class="text-slate-600 text-base m-0 max-w-[640px] mx-auto leading-relaxed">
                    Mengapa platform ini penting bagi pembaca dengan gangguan penglihatan.
                </p>
            </div>

            <div class="max-w-[900px] mx-auto grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="card border shadow-sm p-6 flex flex-col gap-2">
                    <h3 class="text-base font-bold text-black m-0">Memperluas Akses ke Buku Cetak</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Tidak ada perbedaan isi antara buku yang dibaca dengan mata dan buku yang didengarkan. Buku yang tadinya terhalang ketiadaan koleksi braille kini tetap bisa diakses.
                    </p>
                </div>
                <div class="card border shadow-sm p-6 flex flex-col gap-2">
                    <h3 class="text-base font-bold text-black m-0">Kebebasan Membaca Mandiri</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Bacaan tidak lagi bergantung pada ketersediaan orang lain. Penyandang tunanetra yang ingin membaca pada larut malam tetap bisa melakukannya tanpa harus meminta tolong dibacakan.
                    </p>
                </div>
                <div class="card border shadow-sm p-6 flex flex-col gap-2">
                    <h3 class="text-base font-bold text-black m-0">Navigasi yang Sudah Dikenal</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Pemutar bekerja dengan pola yang lazim pada aplikasi pembaca layar: jeda dengan spasi, mundur dan maju dengan tombol panah. Tidak perlu mempelajari kendali yang asing.
                    </p>
                </div>
                <div class="card border shadow-sm p-6 flex flex-col gap-2">
                    <h3 class="text-base font-bold text-black m-0">Hemat Kuota dan Ringan</h3>
                    <p class="text-slate-600 text-xs leading-relaxed m-0">
                        Audio dipecah per kalimat sehingga tidak perlu mengunduh satu berkas besar sekaligus. Bagian yang sudah didengar tidak diunduh ulang ketika pemutaran dilanjutkan.
                    </p>
                </div>
            </div>
        </section>

        <!-- Keunggulan Sistem -->
        <section class="py-6" aria-labelledby="h2-keunggulan">
            <div class="text-center mb-10">
                <h2 id="h2-keunggulan" class="text-3xl sm:text-4xl font-extrabold text-black tracking-tight m-0 mb-3">Keunggulan Sistem</h2>
                <p class="text-slate-600 text-base m-0 max-w-[640px] mx-auto leading-relaxed">
                    Detail teknis yang membuat pemrosesan buku besar tetap andal.
                </p>
            </div>

            <div class="max-w-[900px] mx-auto flex flex-col gap-5 text-slate-600 text-sm sm:text-base leading-[1.8]">
                <p class="m-0">
                    <strong class="text-black">Pemrosesan bertahap dengan antrean.</strong> Buku besar dipecah menjadi potongan kalimat lalu dikerjakan satu per satu lewat antrean, bukan sekaligus dalam satu permintaan. Ini mencegah server kelelahan saat beberapa buku masuk bersamaan.
                </p>
                <p class="m-0">
                    <strong class="text-black">Dukungan penuh untuk melanjutkan proses.</strong> Bila pemrosesan terhenti di tengah jalan, sistem menghitung berkas mana yang sudah utuh lalu melanjutkan dari titik terakhir, bukan mengulang pekerjaan yang selesai.
                </p>
                <p class="m-0">
                    <strong class="text-black">Validasi kelengkapan sebelum digabung.</strong> Buku baru ditandai selesai setelah semua berkas audio diverifikasi ada dan berukuran wajar. Bila ada bagian yang hilang, sistem melaporkannya lengkap dengan nomor bagian yang bermasalah, bukan memutar audio yang terpotong.
                </p>
                <p class="m-0">
                    <strong class="text-black">Pemrosesan ulang otomatis.</strong> Bagian yang gagal disintesis diberi kesempatan dicoba lagi secara otomatis sebelum ditandai gagal permanen, sehingga gangguan sesaat tidak langsung membuat satu buku hilang.
                </p>
            </div>
        </section>

        <!-- FAQ -->
        <section class="py-6" aria-labelledby="h2-faq">
            <div class="text-center mb-10">
                <h2 id="h2-faq" class="text-3xl sm:text-4xl font-extrabold text-black tracking-tight m-0 mb-3">FAQ</h2>
                <p class="text-slate-600 text-base m-0 max-w-[640px] mx-auto leading-relaxed">
                    Pertanyaan yang sering diajukan pembaca dan pengelola buku.
                </p>
            </div>

            <div class="max-w-[800px] mx-auto flex flex-col gap-3">
                @foreach ($faq as $item)
                    <details class="card border shadow-sm p-5">
                        <summary class="cursor-pointer list-none flex items-start justify-between gap-4 m-0 font-bold text-black text-sm sm:text-base">
                            <span>{{ $item['question'] }}</span>
                            <span class="shrink-0 text-[#b8860b] text-xl leading-none mt-0.5" aria-hidden="true">+</span>
                        </summary>
                        <p class="text-slate-600 text-sm leading-relaxed mt-3 mb-0">{{ $item['answer'] }}</p>
                    </details>
                @endforeach
            </div>
        </section>

        <!-- CTA -->
        <section class="py-8" aria-labelledby="h2-cta">
            <div class="max-w-[760px] mx-auto text-center flex flex-col gap-4">
                <h2 id="h2-cta" class="text-2xl sm:text-3xl font-extrabold text-black tracking-tight m-0">Siap mulai mendengarkan?</h2>
                <p class="text-slate-600 text-base m-0 leading-relaxed">
                    Pilih buku dari katalog, pindai kode QR-nya, dan nikmati membaca secara mandiri. Gratis, tanpa pemasangan aplikasi.
                </p>
                <div class="flex gap-4 flex-wrap justify-center mt-2">
                    <a href="{{ route('audio-books.index') }}" class="btn btn-primary btn-hero px-7 py-3 text-sm" aria-label="Mulai mendengarkan buku sekarang">
                        Mulai Mendengarkan
                    </a>
                    <a href="{{ route('audio-books.index') }}" class="btn btn-ghost btn-hero--ghost px-6 py-3 text-sm font-semibold" aria-label="Jelajahi katalog buku audio">
                        Jelajahi Katalog
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
