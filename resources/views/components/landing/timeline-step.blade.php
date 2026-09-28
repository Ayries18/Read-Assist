{{--
    Satu langkah pada timeline "Cara Kerja".

    @param string $icon  Path SVG Lucide
    @param string $num   Nomor langkah ("01", "02", …)
    @param string $title Judul langkah (dirender sebagai <h3>)
    @param string $text  Penjelasan satu kalimat

    Dipakai di dalam <ol class="ra-timeline"> supaya urutan langkah tetap
    terekspos ke pembaca layar dan mesin telusur.
--}}
@props(['icon', 'num', 'title', 'text', 'delay' => 0])

<li class="ra-step" data-ra-reveal style="transition-delay: {{ $delay }}ms">
    <span class="ra-step__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
            stroke-linecap="round" stroke-linejoin="round" focusable="false">
            {!! $icon !!}
        </svg>
    </span>
    <div class="ra-step__body">
        <span class="ra-step__num">Langkah {{ $num }}</span>
        <h3 class="ra-h3">{{ $title }}</h3>
        <p class="ra-step__text">{{ $text }}</p>
    </div>
</li>
