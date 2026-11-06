{{--
    Ilustrasi "Tentang Read-Assist" — inline SVG, tanpa request jaringan.

    Motif: buku digital (halaman terbuka) + headphone (menyimak) + kartu QR
    (QR-Audio) + pil braille (aksesibilitas) + bar pemutar (progres/TTS).

    Seluruh warna memakai `currentColor` / var(--ra-gold) supaya otomatis
    mengikuti tema terang, gelap, dan mode kontras tinggi (lihat
    resources/css/landing.css). `currentColor` diwarisi dari .ra-visual__frame.

    SVG dekoratif → aria-hidden; makna-nya sudah disampaikan oleh teks di
    sebelahnya, jadi pembaca layar tidak perlu membacanya dua kali.
--}}
<div class="ra-visual" role="img" aria-label="Ilustrasi buku digital yang sedang disimak lewat headphone, dengan kode QR pada sampul buku fisik dan bar pemutar audio di bawahnya.">
    <div class="ra-visual__frame">
        <svg class="ra-visual__art" viewBox="0 0 520 440" fill="none" aria-hidden="true" focusable="false">
            {{-- Bingkai --}}
            <rect x="32" y="32" width="456" height="376" rx="32"
                fill="var(--ra-visual-fill)" stroke="currentColor" stroke-opacity=".12" stroke-width="1.5" />

            {{-- Pil braille: metafora aksesibilitas --}}
            <g>
                <rect x="52" y="62" width="120" height="38" rx="19" fill="var(--ra-chip-bg)"
                    stroke="currentColor" stroke-opacity=".18" />
                <circle cx="92" cy="81" r="4.5" fill="var(--ra-gold)" />
                <circle cx="112" cy="81" r="4.5" fill="var(--ra-gold)" />
                <circle cx="132" cy="81" r="4.5" fill="none" stroke="currentColor" stroke-opacity=".35" stroke-width="2" />
                <circle cx="92" cy="97" r="4.5" fill="none" stroke="currentColor" stroke-opacity=".35" stroke-width="2" />
                <circle cx="112" cy="97" r="4.5" fill="var(--ra-gold)" />
                <circle cx="132" cy="97" r="4.5" fill="var(--ra-gold)" />
            </g>

            {{-- Kartu kode QR --}}
            <g>
                <rect x="372" y="56" width="96" height="96" rx="18" fill="var(--ra-surface)"
                    stroke="currentColor" stroke-opacity=".22" stroke-width="1.5" />
                <rect x="386" y="70" width="24" height="24" rx="6" fill="none" stroke="var(--ra-gold)" stroke-width="2.5" />
                <rect x="395" y="79" width="6" height="6" rx="2" fill="var(--ra-gold)" />
                <rect x="430" y="70" width="24" height="24" rx="6" fill="none" stroke="var(--ra-gold)" stroke-width="2.5" />
                <rect x="439" y="79" width="6" height="6" rx="2" fill="var(--ra-gold)" />
                <rect x="386" y="110" width="24" height="24" rx="6" fill="none" stroke="var(--ra-gold)" stroke-width="2.5" />
                <rect x="395" y="119" width="6" height="6" rx="2" fill="var(--ra-gold)" />
                <rect x="420" y="110" width="6" height="6" rx="1.5" fill="currentColor" fill-opacity=".45" />
                <rect x="436" y="110" width="6" height="6" rx="1.5" fill="currentColor" fill-opacity=".45" />
                <rect x="420" y="126" width="6" height="6" rx="1.5" fill="currentColor" fill-opacity=".45" />
                <rect x="442" y="126" width="12" height="6" rx="1.5" fill="currentColor" fill-opacity=".45" />
                <rect x="420" y="142" width="6" height="6" rx="1.5" fill="var(--ra-gold)" />
            </g>

            {{-- Headphone --}}
            <g>
                <path d="M126 240a134 134 0 0 1 268 0" fill="none" stroke="currentColor"
                    stroke-opacity=".5" stroke-width="8" stroke-linecap="round" />
                <rect x="111" y="214" width="30" height="52" rx="15" fill="var(--ra-surface)"
                    stroke="currentColor" stroke-opacity=".5" stroke-width="8" />
                <rect x="379" y="214" width="30" height="52" rx="15" fill="var(--ra-surface)"
                    stroke="currentColor" stroke-opacity=".5" stroke-width="8" />
            </g>

            {{-- Buku digital terbuka --}}
            <g>
                <path d="M260 224c-24-18-60-24-84-20v100c24-4 60 2 84 20z" fill="var(--ra-surface)"
                    stroke="currentColor" stroke-width="3" stroke-linejoin="round" />
                <path d="M260 224c24-18 60-24 84-20v100c-24-4-60 2-84 20z" fill="var(--ra-surface)"
                    stroke="currentColor" stroke-width="3" stroke-linejoin="round" />
                <path d="M260 224v100" stroke="currentColor" stroke-opacity=".4" stroke-width="3" stroke-linecap="round" />
                {{-- Baris teks; satu baris emas = kalimat yang sedang dibacakan --}}
                <path d="M192 252h56" stroke="currentColor" stroke-opacity=".28" stroke-width="6" stroke-linecap="round" />
                <path d="M192 274h40" stroke="var(--ra-gold)" stroke-width="7" stroke-linecap="round" />
                <path d="M192 296h56" stroke="currentColor" stroke-opacity=".28" stroke-width="6" stroke-linecap="round" />
                <path d="M270 252h58" stroke="currentColor" stroke-opacity=".28" stroke-width="6" stroke-linecap="round" />
                <path d="M270 274h34" stroke="currentColor" stroke-opacity=".28" stroke-width="6" stroke-linecap="round" />
                <path d="M270 296h58" stroke="currentColor" stroke-opacity=".28" stroke-width="6" stroke-linecap="round" />
            </g>

            {{-- Bar pemutar: progres bacaan --}}
            <g>
                <rect x="136" y="342" width="248" height="42" rx="15" fill="var(--ra-surface)"
                    stroke="currentColor" stroke-opacity=".18" stroke-width="1.5" />
                <circle cx="162" cy="363" r="14" fill="var(--ra-gold)" />
                <path d="M158 356.5 168 363l-10 6.5z" fill="var(--ra-on-gold)" />
                <path d="M188 363h140" stroke="currentColor" stroke-opacity=".18" stroke-width="6" stroke-linecap="round" />
                <path d="M188 363h74" stroke="var(--ra-gold)" stroke-width="6" stroke-linecap="round" />
                <circle cx="352" cy="363" r="3.5" fill="currentColor" fill-opacity=".35" />
                <circle cx="366" cy="363" r="3.5" fill="currentColor" fill-opacity=".35" />
            </g>
        </svg>
    </div>
</div>
