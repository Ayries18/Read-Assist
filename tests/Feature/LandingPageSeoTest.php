<?php

namespace Tests\Feature;

use App\Support\Seo\Faq;
use Tests\TestCase;

/**
 * Regression test untuk meta tag landing page.
 *
 * Audit awal menemukan halaman / sama sekali tidak punya canonical, og:image,
 * twitter card, maupun JSON-LD, sehingga Google tidak punya sinyal apa pun
 * tentang isi halaman. Test ini mengunci keberadaan tag-tag tersebut supaya
 * tidak hilang diam-diam saat layout diedit.
 */
class LandingPageSeoTest extends TestCase
{
    public function test_landing_page_exposes_a_single_h1(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    public function test_landing_page_has_unique_title_and_meta_description(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $this->assertSame(
            1,
            substr_count($html, '<title>'),
            'Harus ada tepat satu elemen <title>.'
        );

        preg_match('/<title>(.+?)<\/title>/s', $html, $title);
        $this->assertNotEmpty($title);
        $this->assertLessThanOrEqual(
            60,
            mb_strlen(html_entity_decode($title[1], ENT_QUOTES)),
            'Title akan terpotong di hasil pencarian.'
        );

        preg_match('/<meta name="description" content="([^"]*)"/', $html, $description);
        $this->assertNotEmpty($description, 'Meta description wajib ada.');

        $length = mb_strlen(html_entity_decode($description[1], ENT_QUOTES));
        $this->assertGreaterThanOrEqual(70, $length, 'Meta description terlalu pendek untuk hasil pencarian.');
        $this->assertLessThanOrEqual(160, $length, 'Meta description akan terpotong di hasil pencarian.');
    }

    public function test_landing_page_declares_canonical_open_graph_and_twitter_cards(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="', $html);
        $this->assertStringContainsString('property="og:title"', $html);
        $this->assertStringContainsString('property="og:description"', $html);
        $this->assertStringContainsString('property="og:image"', $html);
        $this->assertStringContainsString('property="og:url"', $html);
        $this->assertStringContainsString('name="twitter:card"', $html);
        $this->assertStringContainsString('name="twitter:image"', $html);

        // og:image harus URL absolut, karena crawler tidak menyelesaikan
        // path relatif untuk pratinjau kartu sosial.
        preg_match('/property="og:image" content="([^"]*)"/', $html, $image);
        $this->assertNotEmpty($image);
        $this->assertStringStartsWith('http', $image[1]);
    }

    public function test_canonical_comes_from_the_home_route(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        preg_match('/<link rel="canonical" href="([^"]*)"/', $response->getContent(), $canonical);
        $this->assertNotEmpty($canonical);
        $this->assertSame(
            route('home'),
            html_entity_decode($canonical[1], ENT_QUOTES),
            'Canonical harus dibangun dari route("home"), bukan URL yang diketik manual.'
        );
    }

    public function test_landing_page_embeds_valid_json_ld(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('application/ld+json', false);

        preg_match_all(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            $response->getContent(),
            $blocks
        );

        $this->assertNotEmpty($blocks[1], 'JSON-LD tidak ditemukan di halaman.');

        $types = [];
        foreach ($blocks[1] as $block) {
            $decoded = json_decode(html_entity_decode($block, ENT_QUOTES | ENT_HTML5), true);

            $this->assertIsArray(
                $decoded,
                'JSON-LD tidak bisa di-decode: '.json_last_error_msg()
            );

            foreach ((array) $decoded as $node) {
                $types[] = $node['@type'] ?? null;
            }
        }

        $this->assertContains('WebApplication', $types);
        $this->assertContains('FAQPage', $types);
    }

    public function test_faq_markup_matches_the_faq_data_source(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $faq = Faq::landing();
        $this->assertCount(7, $faq, 'Jumlah FAQ berubah; perbarui test dan structured data bila itu disengaja.');

        foreach ($faq as $item) {
            $this->assertStringContainsString(
                htmlspecialchars($item['question'], ENT_QUOTES),
                $html,
                'Pertanyaan FAQ tidak muncul di body: '.$item['question']
            );
        }
    }

    public function test_landing_page_renders_every_image_with_alt_text(): void
    {
        $response = $this->get('/');

        // markup di dalam <script> adalah string JavaScript, bukan elemen
        // gambar yang di-render peramban. Menghitungnya sebagai <img> akan
        // memunculkan false positive untuk gambar window cetak.
        $html = preg_replace('#<script\b[^>]*>.*?</script>#si', '', $response->getContent());

        preg_match_all('/<img\b[^>]*>/i', $html, $images);

        $this->assertNotEmpty($images[0], 'Landing page harus punya minimal satu gambar.');

        foreach ($images[0] as $image) {
            $this->assertMatchesRegularExpression(
                '/\salt="[^"]*"/i',
                $image,
                'Gambar tanpa alt teks: '.$image
            );
            $this->assertMatchesRegularExpression(
                '/\swidth="\d+"/i',
                $image,
                'Gambar tanpa width (menyebabkan layout shift): '.$image
            );
            $this->assertMatchesRegularExpression(
                '/\sheight="\d+"/i',
                $image,
                'Gambar tanpa height (menyebabkan layout shift): '.$image
            );
            $this->assertMatchesRegularExpression(
                '/\sloading="(lazy|eager)"/i',
                $image,
                'Gambar tanpa atribut loading: '.$image
            );
        }
    }
}
