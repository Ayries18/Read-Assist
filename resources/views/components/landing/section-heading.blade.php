{{--
    Kepala section: eyebrow + H2 + paragraf pengantar.

    @param string $id     Id untuk elemen H2 (dipakai aria-labelledby)
    @param string $eyebrow Label kecil di atas judul
    @param string $title  Teks H2
    @param string $lead   Paragraf pengantar (opsional)
    @param string $align  "center" (default) atau "left"

    Spacing vertikal sengaja dipegang komponen ini supaya ritme antar
    section seragam dan mudah disetel dari satu tempat.
--}}
@props(['id', 'eyebrow' => null, 'title', 'lead' => null, 'align' => 'center'])

@php
    $alignment = $align === 'left' ? 'text-left items-start' : 'text-center items-center';
@endphp

<div class="flex flex-col gap-3 {{ $alignment }} mb-10 sm:mb-14" data-ra-reveal>
    @if ($eyebrow)
        <p class="ra-eyebrow">{{ $eyebrow }}</p>
    @endif

    <h2 id="{{ $id }}" class="ra-h2">{{ $title }}</h2>

    @if ($lead)
        <p class="ra-lead m-0 {{ $align === 'left' ? 'max-w-2xl' : 'mx-auto max-w-2xl' }} text-base leading-relaxed">
            {{ $lead }}
        </p>
    @endif
</div>
