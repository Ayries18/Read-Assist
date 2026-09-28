{{--
    Kartu fitur landing page.

    @param string $icon    Path SVG Lucide (tanpa <svg>, tanpa atribut dekoratif)
    @param string $title   Judul kartu (dirender sebagai <h3>)
    @param string $text    Satu kalimat penjelasan
    @param int    $delay   Delay transisi dalam milidetik (stagger)

    Ikon memakai gaya stroke Lucide asli (24×24, stroke-width 1.75,
    linecap/linejoin round) supaya konsisten dengan ikon lain di layout.
--}}
@props(['icon', 'title', 'text', 'delay' => 0])

<article class="ra-card ra-card--lift" data-ra-reveal style="transition-delay: {{ $delay }}ms">
    <span class="ra-card__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
            stroke-linecap="round" stroke-linejoin="round" focusable="false">
            {!! $icon !!}
        </svg>
    </span>
    <h3 class="ra-h3">{{ $title }}</h3>
    <p class="ra-card__text">{{ $text }}</p>
</article>
