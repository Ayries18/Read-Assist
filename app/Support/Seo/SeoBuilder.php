<?php

namespace App\Support\Seo;

/**
 * Sumber tunggal untuk data SEO tiap halaman.
 *
 * Dipisah dari controller supaya view tidak perlu merakit array panjang,
 * dan supaya setiap halaman punya satu tempat yang jelas untuk title,
 * description, canonical, dan JSON-LD.
 */
class SeoBuilder
{
    /**
     * Data SEO untuk landing page.
     *
     * @param  array<int, array{question: string, answer: string}>  $faq
     * @return array<string, mixed>
     */
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
            'keywords' => 'buku audio, tunanetra, tunanetrab, aksesibilitas, read-assist, qr code, text to speech, audiobook, buku braille, buku cetak, EPUB, PDF',
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
}
