@extends('layouts.app')

@section('content')
    <div class="flex flex-col">

        {{-- ══ HERO ════════════════════════════════════════════════════
             Di luar sistem section `.ra-*` dan tanpa `data-ra-reveal`:
             elemen di sini adalah LCP, jadi tidak boleh pernah disamarkan
             oleh animasi masuk. --}}
        <section class="pb-2 lg:pb-6" aria-labelledby="h1-hero">
            <div class="grid grid-cols-1 lg:grid-cols-[1.15fr_0.85fr] gap-10 lg:gap-14 items-center">
                <div class="flex flex-col gap-5">
                    <p class="ra-eyebrow">Platform Aksesibilitas Buku</p>

                    <h1 id="h1-hero" class="m-0 max-w-2xl text-3xl sm:text-5xl font-extrabold leading-[1.12] tracking-tight text-black">
                        Jembatan Audio untuk <br class="hidden sm:block"><span class="text-gradient">Membaca Buku Fisik</span>
                    </h1>

                    <p class="ra-lead m-0 max-w-xl text-base leading-[1.7]">
                        Read-Assist mendampingi penyandang tunanetra untuk membaca buku cetak secara mandiri.
                        Cukup pindai label QR unik yang ditempel pada buku fisik untuk mendengarkan pembacaan
                        teks otomatis langsung dari smartphone Anda.
                    </p>

                    <div class="mt-1 flex flex-wrap items-center gap-3 sm:gap-4">
                        <a href="{{ route('audio-books.index') }}" class="ra-btn ra-btn--primary">
                            Mulai Mendengarkan
                            <svg class="ra-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                                focusable="false">
                                <path d="M5 12h14" />
                                <path d="m12 5 7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ route('audio-books.index') }}" class="ra-btn ra-btn--ghost">Jelajahi Katalog</a>
                        @if (!session()->has('auth_role'))
                            <a href="{{ route('login') }}" class="ra-btn ra-btn--ghost">Masuk Ke Akun</a>
                        @endif
                    </div>

                    <dl class="mt-7 grid grid-cols-1 gap-y-5 border-t pt-6 sm:grid-cols-3 sm:gap-y-0 stat-row">
                        <div class="flex flex-col gap-1 sm:pr-4">
                            <dt class="m-0 text-[0.7rem] font-medium uppercase tracking-wider text-gray-600 order-2">Buku Terdaftar</dt>
                            <dd class="m-0 order-1 text-4xl font-extrabold leading-none tracking-tight text-black">{{ $bookCount }}</dd>
                        </div>
                        <div class="stat-cell flex flex-col gap-1 border-t pt-5 sm:border-l sm:border-t-0 sm:px-4 sm:pt-0">
                            <dt class="m-0 text-[0.7rem] font-medium uppercase tracking-wider text-gray-600 order-2">Total Karakter</dt>
                            <dd class="m-0 order-1 text-4xl font-extrabold leading-none tracking-tight text-black">{{ $charCount }}</dd>
                        </div>
                        <div class="stat-cell flex flex-col gap-1 border-t pt-5 sm:border-l sm:border-t-0 sm:pl-4 sm:pt-0">
                            <dt class="m-0 text-[0.7rem] font-medium uppercase tracking-wider text-gray-600 order-2">Estimasi Bacaan</dt>
                            <dd class="m-0 order-1 text-4xl font-extrabold leading-none tracking-tight text-black">{{ $readDuration }}</dd>
                        </div>
                    </dl>
                </div>

                <figure class="hero-visual relative overflow-hidden">
                    <picture>
                        <source
                            type="image/webp"
                            srcset="{{ $hero480 }} 480w, {{ $hero720 }} 720w, {{ $hero1200 }} 1200w, {{ $hero1800 }} 1800w"
                            sizes="(max-width: 1024px) 92vw, 38vw"
                        >
                        <img
                            src="{{ $heroJpeg }}"
                            srcset="{{ $hero480 }} 480w, {{ $hero720 }} 720w, {{ $hero1200 }} 1200w, {{ $hero1800 }} 1800w"
                            sizes="(max-width: 1024px) 92vw, 38vw"
                            alt="Seorang perempuan penyandang tunanetra membaca buku braille dengan menempelkan jarinya di halaman"
                            class="block h-full w-full object-cover"
                            width="1200"
                            height="900"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                        >
                    </picture>
                    <figcaption class="hero-caption absolute inset-x-0 bottom-0 flex items-center gap-3 p-5 sm:p-6">
                        <div class="hero-badge flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[#b8860b]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </div>
                        <div>
                            <span class="hero-tagline mb-0.5 block text-[0.68rem] font-bold uppercase tracking-wider">Disabilitas Netra</span>
                            <p class="m-0 text-xs leading-snug text-slate-200">Membaca buku braille secara mandiri - kini didukung audio Read-Assist.</p>
                        </div>
                    </figcaption>
                </figure>
            </div>
        </section>

        {{-- ══ TENTANG READ-ASSIST ══════════════════════════════════════
             Dua kolom: ilustrasi + ringkasan & CTA. Copy 127 kata agar
             produk dipahami dalam sekali lintasan mata; penjelasan
             panjang tetap tersedia di section Keunggulan. --}}
        <section class="ra-section ra-divider" aria-labelledby="h2-tentang">
            <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-16">
                <x-landing.about-visual />

                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-3" data-ra-reveal>
                        <p class="ra-eyebrow m-0">Tentang Read-Assist</p>
                        <h2 id="h2-tentang" class="ra-h2">
                            Buku cetak yang bisa didengarkan sendiri
                        </h2>
                    </div>

                    <div class="flex flex-col gap-3.5" data-ra-reveal style="transition-delay: 80ms">
                        <p class="ra-body m-0 text-[0.98rem] leading-[1.75]">
                            Read-Assist mengubah buku cetak menjadi buku audio yang bisa didengarkan sendiri, kapan pun.
                            Pengajar atau relawan mengunggah naskah digital berformat PDF atau EPUB ke katalog, lalu
                            menandai judul yang siap diproses. Sistem mengekstraksi teksnya, memecahnya per kalimat,
                            lalu menyintesis setiap kalimat menjadi berkas audio yang digabungkan menjadi satu buku
                            audio utuh.
                        </p>
                        <p class="ra-body m-0 text-[0.98rem] leading-[1.75]">
                            Kode QR ditempel pada sampul buku fisik. Pembaca cukup memindainya lewat kamera ponsel, dan
                            pemutar langsung terbuka tanpa perlu memasang aplikasi apa pun. Tiap kalimat bisa diulang,
                            dilewati, atau dijeda sesuka pembaca, sementara posisi terakhir selalu diingat.
                        </p>
                        <p class="ra-body m-0 text-[0.98rem] leading-[1.75]">
                            Bagi penyandang tunanetra, ini berarti akses ke novel, buku pelajaran, dan laporan yang
                            biasanya hanya tersedia lewat pembaca lain. Koleksi braille yang terbatas tidak lagi menjadi
                            penghalang, dan buku yang tadinya mustahil dibaca kini bisa diakses hanya dengan telefoning.
                        </p>
                    </div>

                    <div class="mt-1 flex flex-wrap items-center gap-3" data-ra-reveal style="transition-delay: 160ms">
                        <a href="{{ route('audio-books.index') }}" class="ra-btn ra-btn--primary">
                            Mulai Mendengarkan
                            <svg class="ra-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                                focusable="false">
                                <path d="M5 12h14" />
                                <path d="m12 5 7 7-7 7" />
                            </svg>
                        </a>
                        <a href="#cara-kerja" class="ra-btn ra-btn--ghost">
                            Pelajari Selengkapnya
                            <svg class="ra-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                                focusable="false">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══ FITUR UTAMA ══════════════════════════════════════════════
             Empat card inti produk, tepat di bawah ringkasan "Tentang". --}}
        <section class="ra-section ra-divider ra-band" aria-labelledby="h2-fitur">
            <x-landing.section-heading
                id="h2-fitur"
                eyebrow="Fitur Utama"
                title="Empat hal yang membuat buku terbaca"
                lead="Setiap fitur dibuat agar penyandang tunanetra dan pengguna pembaca layar bisa memakai Read-Assist tanpa bantuan."
            />

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <x-landing.feature-card
                    icon='<rect width="5" height="5" x="3" y="3" rx="1" /><rect width="5" height="5" x="16" y="3" rx="1" /><rect width="5" height="5" x="3" y="16" rx="1" /><path d="M21 16h-3a2 2 0 0 0-2 2v3" /><path d="M21 21v.01" /><path d="M12 7v3a2 2 0 0 1-2 2H7" /><path d="M3 12h.01" /><path d="M12 3h.01" /><path d="M12 16v.01" /><path d="M16 12h1" /><path d="M21 12v.01" /><path d="M12 21v-1" />'
                    title="QR-Audio"
                    text="Satu kode QR untuk setiap buku, cukup dipindai kamera ponsel untuk membuka pemutar."
                    :delay="0"
                />
                <x-landing.feature-card
                    icon='<path d="M2 13.5v-3" /><path d="M7 16.5v-9" /><path d="M12 19.5v-15" /><path d="M17 16.5v-9" /><path d="M22 13.5v-3" />'
                    title="Text-to-Speech"
                    text="Naskah PDF atau EPUB dipecah per kalimat lalu disintesis menjadi audio yang jernih."
                    :delay="60"
                />
                <x-landing.feature-card
                    icon='<path d="m16 6 4 14" /><path d="M12 6v14" /><path d="M8 8v12" /><path d="M4 4v16" />'
                    title="Katalog Buku"
                    text="Koleksi digital terstruktur dalam satu katalog yang bebas dicari dan dijelajahi."
                    :delay="120"
                />
                <x-landing.feature-card
                    icon='<path d="M19 21V5a2 2 0 0 0-2-2H7A2 2 0 0 0 5 5v16l7-4 7 4Z" /><path d="m9 10 2 2 4-4" />'
                    title="Progress Otomatis"
                    text="Kalimat terakhir tersimpan, jadi bacaan panjang dilanjutkan dari titik yang sama."
                    :delay="180"
                />
            </div>
        </section>

        {{-- ══ CARA KERJA ═══════════════════════════════════════════════
             Timeline 4 langkah. Desktop: horizontal. Mobile: vertikal.
             Dipakai <ol> agar urutan tetap terekspos ke screen reader. --}}
        <section id="cara-kerja" class="ra-section ra-divider" aria-labelledby="h2-cara-kerja">
            <x-landing.section-heading
                id="h2-cara-kerja"
                eyebrow="Cara Kerja"
                title="Dari naskah digital ke suara, dalam empat langkah"
                lead="Tidak ada perangkat khusus. Cukup satu naskah digital dan satu kode QR pada buku fisik."
            />

            <ol class="ra-timeline">
                <x-landing.timeline-step
                    icon='<path d="M12 3v12" /><path d="m17 8-5-5-5 5" /><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />'
                    num="01"
                    title="Upload"
                    text="Naskah digital PDF atau EPUB diunggah ke katalog oleh pengajar, relawan, atau pengelola buku."
                    :delay="0"
                />
                <x-landing.timeline-step
                    icon='<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" /><path d="M14 2v4a2 2 0 0 0 2 2h4" /><path d="M10 9H8" /><path d="M16 13H8" /><path d="M16 17H8" />'
                    num="02"
                    title="Ekstraksi"
                    text="Teks diekstraksi otomatis lalu dipecah menjadi potongan kalimat berukuran wajar."
                    :delay="70"
                />
                <x-landing.timeline-step
                    icon='<path d="M2 13.5v-3" /><path d="M7 16.5v-9" /><path d="M12 19.5v-15" /><path d="M17 16.5v-9" /><path d="M22 13.5v-3" />'
                    num="03"
                    title="Audio"
                    text="Setiap potongan disintesis menjadi berkas MP3, diverifikasi, lalu digabungkan menjadi satu buku audio."
                    :delay="140"
                />
                <x-landing.timeline-step
                    icon='<path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3" />'
                    num="04"
                    title="Dengarkan"
                    text="Kode QR ditempel pada buku fisik. Pindai, lalu dengarkan per kalimat langsung dari ponsel."
                    :delay="210"
                />
            </ol>
        </section>

        {{-- ══ MANFAAT BAGI TUNANETRA ═══════════════════════════════════ --}}
        <section class="ra-section ra-divider ra-band" aria-labelledby="h2-manfaat">
            <x-landing.section-heading
                id="h2-manfaat"
                eyebrow="Manfaat"
                title="Kenapa platform ini penting"
                lead="Mengapa platform ini penting bagi pembaca dengan gangguan penglihatan."
            />

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <article class="ra-card" data-ra-reveal>
                    <h3 class="ra-h3">Memperluas Akses ke Buku Cetak</h3>
                    <p class="ra-card__text">
                        Tidak ada perbedaan isi antara buku yang dibaca dengan mata dan buku yang didengarkan.
                        Buku yang tadinya terhalang ketiadaan koleksi braille kini tetap bisa diakses.
                    </p>
                </article>

                <article class="ra-card" data-ra-reveal style="transition-delay: 60ms">
                    <h3 class="ra-h3">Kebebasan Membaca Mandiri</h3>
                    <p class="ra-card__text">
                        Bacaan tidak lagi bergantung pada ketersediaan orang lain. Penyandang tunanetra yang ingin
                        membaca pada larut malam tetap bisa melakukannya tanpa harus meminta tolong dibacakan.
                    </p>
                </article>

                <article class="ra-card" data-ra-reveal style="transition-delay: 120ms">
                    <h3 class="ra-h3">Navigasi yang Sudah Dikenal</h3>
                    <p class="ra-card__text">
                        Pemutar bekerja dengan pola yang lazim pada aplikasi pembaca layar: jeda dengan spasi, mundur
                        dan maju dengan tombol panah. Tidak perlu mempelajari kendali yang asing.
                    </p>
                </article>

                <article class="ra-card" data-ra-reveal style="transition-delay: 180ms">
                    <h3 class="ra-h3">Hemat Kuota dan Ringan</h3>
                    <p class="ra-card__text">
                        Audio dipecah per kalimat sehingga tidak perlu mengunduh satu berkas besar sekaligus.
                        Bagian yang sudah didengar tidak diunduh ulang ketika pemutaran dilanjutkan.
                    </p>
                </article>
            </div>
        </section>

        {{-- ══ KEUNGGULAN SISTEM ════════════════════════════════════════
             Konten teknis yang sebelumnya 4 paragraf penuh. Diubah menjadi
             grid kartu + <strong> agar bisa dipindai, bukan dibaca. --}}
        <section class="ra-section ra-divider" aria-labelledby="h2-keunggulan">
            <x-landing.section-heading
                id="h2-keunggulan"
                eyebrow="Di Balik Layar"
                title="Keunggulan Sistem"
                lead="Detail teknis yang membuat pemrosesan buku besar tetap andal."
            />

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <article class="ra-card ra-card--lift ra-card--feature" data-ra-reveal>
                    <span class="ra-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z" />
                            <path d="m6.08 9.5-3.5 1.6a1 1 0 0 0 0 1.81l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9a1 1 0 0 0 0-1.83l-3.5-1.59" />
                            <path d="m6.08 14.5-3.5 1.6a1 1 0 0 0 0 1.81l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9a1 1 0 0 0 0-1.83l-3.5-1.59" />
                        </svg>
                    </span>
                    <h3 class="ra-h3">Pemrosesan Bertahap dengan Antrean</h3>
                    <p class="ra-card__text">
                        Buku besar dipecah menjadi potongan kalimat lalu dikerjakan satu per satu lewat antrean, bukan
                        sekaligus dalam satu permintaan. Server tidak kelelahan saat beberapa buku masuk bersamaan.
                    </p>
                </article>

                <article class="ra-card ra-card--lift ra-card--feature" data-ra-reveal style="transition-delay: 60ms">
                    <span class="ra-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8" />
                            <path d="M21 3v5h-5" />
                            <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16" />
                            <path d="M8 16H3v5" />
                        </svg>
                    </span>
                    <h3 class="ra-h3">Dukungan untuk Melanjutkan Proses</h3>
                    <p class="ra-card__text">
                        Bila pemrosesan terhenti di tengah jalan, sistem menghitung berkas mana yang sudah utuh lalu
                        melanjutkan dari titik terakhir, bukan mengulang pekerjaan yang selesai.
                    </p>
                </article>

                <article class="ra-card ra-card--lift ra-card--feature" data-ra-reveal style="transition-delay: 120ms">
                    <span class="ra-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            <path d="m3 17 2 2 4-4" /><path d="m3 7 2 2 4-4" /><path d="M11 7h10" /><path d="M11 17h10" />
                        </svg>
                    </span>
                    <h3 class="ra-h3">Validasi Kelengkapan</h3>
                    <p class="ra-card__text">
                        Buku baru ditandai selesai setelah semua berkas audio diverifikasi ada dan berukuran wajar.
                        Bagian yang hilang dilaporkan lengkap dengan nomor bagiannya, bukan diputar terpotong.
                    </p>
                </article>

                <article class="ra-card ra-card--lift ra-card--feature" data-ra-reveal style="transition-delay: 180ms">
                    <span class="ra-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8" />
                            <path d="M21 3v5h-5" />
                            <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16" />
                            <path d="M8 16H3v5" />
                        </svg>
                    </span>
                    <h3 class="ra-h3">Pemrosesan Ulang Otomatis</h3>
                    <p class="ra-card__text">
                        Bagian yang gagal disintesis diberi kesempatan dicoba lagi secara otomatis sebelum ditandai gagal
                        permanen, sehingga gangguan sesaat tidak langsung membuat satu buku hilang.
                    </p>
                </article>
            </div>
        </section>

        {{-- ══ FAQ ══════════════════════════════════════════════════════
             Dipertahankan utuh: sumber data yang sama dipakai JSON-LD,
             dan LandingPageSeoTest mengunci 7 pertanyaan ini. --}}
        <section class="ra-section ra-divider ra-band" aria-labelledby="h2-faq">
            <x-landing.section-heading
                id="h2-faq"
                eyebrow="FAQ"
                title="Pertanyaan yang sering diajukan"
                lead="Pertanyaan yang sering diajukan pembaca dan pengelola buku."
            />

            <div class="mx-auto flex w-full max-w-3xl flex-col gap-3">
                @foreach ($faq as $item)
                    <details class="ra-card ra-card--lift" data-ra-reveal>
                        <summary class="m-0 flex cursor-pointer list-none items-start justify-between gap-4 text-sm sm:text-base">
                            <span class="ra-h3">{{ $item['question'] }}</span>
                            <span class="ra-faq__toggle" aria-hidden="true">
                                <svg class="ra-faq__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </span>
                        </summary>
                        <p class="ra-card__text mt-3">{{ $item['answer'] }}</p>
                    </details>
                @endforeach
            </div>
        </section>

        {{-- ══ CTA PENUTUP ══════════════════════════════════════════════ --}}
        <section class="ra-section ra-divider" aria-labelledby="h2-cta">
            <div class="mx-auto flex max-w-2xl flex-col items-center gap-5 text-center" data-ra-reveal>
                <h2 id="h2-cta" class="ra-h2">Siap mulai mendengarkan?</h2>
                <p class="ra-lead m-0 text-base leading-relaxed">
                    Pilih buku dari katalog, pindai kode QR-nya, dan nikmati membaca secara mandiri.
                    Gratis, tanpa pemasangan aplikasi.
                </p>
                <div class="mt-1 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('audio-books.index') }}" class="ra-btn ra-btn--primary">
                        Mulai Mendengarkan
                        <svg class="ra-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                            focusable="false">
                            <path d="M5 12h14" />
                            <path d="m12 5 7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('audio-books.index') }}" class="ra-btn ra-btn--ghost">Jelajahi Katalog</a>
                </div>
            </div>
        </section>
    </div>
@endsection
