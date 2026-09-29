@php
    /**
     * Partial SEO generik.
     *
     * Semua halaman memakai partial ini, sehingga setiap view hanya perlu
     * mengirim array $seo berisi kunci yang ingin dioverride. Kunci yang tidak
     * diisi otomatis memakai nilai default di bawah.
     *
     * Kunci yang didukung:
     *   title, description, keywords, canonical, image, type, robots,
     *   locale, siteName, schema (array|JsonSerializable|null)
     *
     * Semua URL dibangun lewat helper Laravel (url(), asset(), route()) —
     * tidak ada URL yang di-hardcode.
     */
    $seoSiteName = 'Read-Assist';
    $seoType = $seo['type'] ?? 'website';
    $seoLocale = $seo['locale'] ?? 'id_ID';
    $seoUrl = $seo['canonical'] ?? url()->current();

    $seoTitle = $seo['title']
        ?? ($title ?? 'Read-Assist — Buku Audio untuk Penyandang Tunanetra');
    $seoTitle = rtrim(trim((string) $seoTitle), " \t\n\r\0\x0B-—|·");

    $seoDescription = $seo['description'] ?? 'Read-Assist adalah platform aksesibilitas buku audio untuk penyandang tunanetra. Pindai kode QR pada buku cetak, lalu dengarkan pembacaan teks otomatis dari smartphone. Mendukung PDF dan EPUB.';
    $seoDescription = trim(preg_replace('/\s+/u', ' ', (string) $seoDescription));

    $seoKeywords = $seo['keywords'] ?? 'buku audio, tunanetra, aksesibilitas, read-assist, qr code, text to speech, audio book, buku braille, ebook, EPUB, PDF';
    $seoRobots = $seo['robots'] ?? 'index, follow, max-image-preview:large';
    $seoImage = $seo['image'] ?? asset('logo-horizontal.png');
    $seoImageWidth = (int) ($seo['image_width'] ?? 1024);
    $seoImageHeight = (int) ($seo['image_height'] ?? 512);
    $seoSchema = $seo['schema'] ?? null;
@endphp

<title>{{ config('app.name', 'Read-Assist') }}</title>
<meta name="description" content="{{ $seoDescription }}">
<meta name="keywords" content="{{ $seoKeywords }}">
<meta name="author" content="{{ $seoSiteName }}">
<meta name="robots" content="{{ $seoRobots }}">

<link rel="canonical" href="{{ $seoUrl }}">

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $seoSiteName }}">
<meta property="og:locale" content="{{ $seoLocale }}">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:width" content="{{ $seoImageWidth }}">
<meta property="og:image:height" content="{{ $seoImageHeight }}">
<meta property="og:image:alt" content="{{ $seoImageAlt ?? $seoTitle }}">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">
<meta name="twitter:image:alt" content="{{ $seoImageAlt ?? $seoTitle }}">

@if ($seoSchema)
    <script type="application/ld+json">{!! json_encode($seoSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
