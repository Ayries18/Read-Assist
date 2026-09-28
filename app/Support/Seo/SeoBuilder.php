<?php

namespace App\Support\Seo;

use App\Models\AudioBuku;
use Illuminate\Support\Str;

/**
 * Sumber tunggal untuk data SEO tiap halaman.
 *
 * Dipisah dari controller supaya view tidak perlu merakit array panjang,
 * dan supaya setiap halaman punya satu tempat yang jelas untuk title,
 * description, canonical, dan JSON-LD.
 */
class SeoBuilder
{
    /** Batas panjtan judul di <title> (Google memotong ±60 karakter). */
    private const TITLE_LIMIT = 65;

    /** Batas panjang description (70-160 karakter). */
    private const DESCRIPTION_LIMIT = 155;

    /** Default semua URL absolut dibangun lewat helper Laravel. */
    public static function landing(string $canonical, array $faq): array
    {
        return [
            // Panjang dijaga di bawah 60 karakter karena Google memotong title
            // di angka itu pada hasil pencarian desktop. Kata kunci "buku audio"
            // dan "tunanetra" harus tetap ada tanpa sufiks "| QR Code & TTS".
            'title' => 'Read-Assist — Buku Audio untuk Penyandang Tunanetra',
            // Panjang description dijaga 70-160 karakter: di bawah 70 wasting
            // ruang di hasil pencarian, di atas 160 akan dipotong Google.
            'description' => 'Read-Assist mengubah buku cetak dan EPUB menjadi buku audio untuk penyandang tunanetra. Pindai kode QR, dengarkan, lalu lanjutkan dari kalimat terakhir.',
            'keywords' => 'buku audio, tunanetra, aksesibilitas, read-assist, qr code, text to speech, audiobook, buku braille, buku cetak, EPUB, PDF',
            'canonical' => $canonical,
            'image' => asset('logo-horizontal.png'),
            'image_width' => 1024,
            'image_height' => 512,
            'schema' => [
                self::webApplication($canonical),
                self::faqPage($faq),
            ],
        ];
    }

    /**
     * Data SEO halaman katalog.
     *
     * @return array<string, mixed>
     */
    public static function catalog(string $canonical): array
    {
        return [
            'title' => 'Katalog Buku Audio',
            'description' => 'Jelajahi dan dengarkan katalog buku audio Read-Assist untuk penyandang tunanetra. Cari judul, penulis, atau kategori, lalu putar langsung dari smartphone.',
            'keywords' => 'katalog buku audio, buku audio, audio book indonesia, tunanetra, read-assist, ebook audio',
            'canonical' => $canonical,
            'image' => asset('logo-horizontal.png'),
            'image_width' => 1024,
            'image_height' => 512,
        ];
    }

    /**
     * Data SEO tambahan untuk katalog yang sedang difilter (noindex, supaya
     * kombinasi search/kategori/sort yang berterbangan tidak mengotori index).
     *
     * @return array<string, mixed>
     */
    public static function filteredCatalog(string $canonical): array
    {
        return self::catalog($canonical) + ['robots' => 'noindex, follow'];
    }

    /**
     * Data SEO halaman detail/play buku, termasuk Schema.org AudioObject
     * supaya Google bisa mengenali dan memutar audionya.
     *
     * @return array<string, mixed>
     */
    public static function book(AudioBuku $book): array
    {
        $description = self::cleanText($book->deskripsi, self::DESCRIPTION_LIMIT);
        if ($description === '') {
            $description = sprintf('Buku audio %s siap didengarkan di Read-Assist.', $book->judul);
        }

        $keywords = array_values(array_filter([
            'buku audio',
            'audio book',
            $book->judul,
            $book->penulis,
            $book->kategori,
            'tunanetra',
            'read-assist',
        ]));

        return [
            'title' => Str::limit($book->judul, self::TITLE_LIMIT, ''),
            'description' => $description,
            'keywords' => implode(', ', $keywords),
            'canonical' => route('katalog.show', $book->id),
            'image' => $book->cover ? asset('storage/'.$book->cover) : asset('logo-horizontal.png'),
            'image_width' => 1024,
            'image_height' => 512,
            'schema' => [
                self::audioObject($book, $description),
            ],
        ];
    }

    /**
     * JSON-LD AudioObject untuk sebuah buku.
     *
     * @return array<string, mixed>
     */
    public static function audioObject(AudioBuku $book, string $description): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'AudioObject',
            'name' => $book->judul,
            'description' => $description,
            'url' => route('katalog.show', $book->id),
            'inLanguage' => 'id-ID',
        ];

        if ($book->cover) {
            $data['image'] = asset('storage/'.$book->cover);
        }

        if ($book->penulis) {
            $data['author'] = [
                '@type' => 'Person',
                'name' => $book->penulis,
            ];
        }

        if (in_array($book->audio_status, ['completed', 'partial'], true)) {
            $data['contentUrl'] = route('audio.stream', $book->id);
            $data['encodingFormat'] = 'audio/mpeg';
        }

        return $data;
    }

    /**
     * JSON-LD WebApplication.
     *
     * @return array<string, mixed>
     */
    public static function webApplication(string $canonical): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => 'Read-Assist',
            'url' => $canonical,
            'applicationCategory' => 'EducationalApplication',
            'applicationSubCategory' => 'Aksesibilitas Buku Audio',
            'operatingSystem' => 'Web',
            'inLanguage' => 'id-ID',
            'description' => 'Platform aksesibilitas yang mengubah buku cetak dan digital menjadi buku audio untuk penyandang tunanetra melalui pemindaian kode QR.',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'IDR',
            ],
            'featureList' => [
                'Pemindaian kode QR untuk akses cepat',
                'Text-to-Speech otomatis dari PDF dan EPUB',
                'Pemutaran per kalimat dengan navigasi keyboard',
                'Penyimpanan progres membaca otomatis',
                'Mode kontras tinggi dan antarmuka ramah pembaca layar',
            ],
        ];
    }

    /**
     * JSON-LD FAQPage. Hanya sah bila markup FAQ di body juga ada,
     * kalau tidak Google mengabaikan structured data ini.
     *
     * @param  array<int, array{question: string, answer: string}>  $faq
     * @return array<string, mixed>
     */
    public static function faqPage(array $faq): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(
                fn (array $item): array => [
                    '@type' => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'],
                    ],
                ],
                $faq
            ),
        ];
    }

    /**
     * Bersihkan teks bebas (HTML/kutipan) lalu potong ke panjang ideal meta.
     */
    private static function cleanText(?string $text, int $limit): string
    {
        $clean = trim(strip_tags(html_entity_decode((string) $text, ENT_QUOTES, 'UTF-8')));
        $clean = trim((string) preg_replace('/\s+/u', ' ', $clean));

        return Str::limit($clean, $limit, '…');
    }
}
