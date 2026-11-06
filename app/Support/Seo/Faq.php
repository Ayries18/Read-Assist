<?php

namespace App\Support\Seo;

/**
 * FAQ landing page.
 *
 * Disimpan di sini, bukan di dalam view, karena isinya dipakai dua kali:
 * untuk JSON-LD FAQPage (supaya halaman eligible rich result) dan untuk
 * markup di dalam body. Kalau berasal dari sumber yang sama, tidak mungkin
 * keduanya berbeda dan membuat Google menandai structured data tidak cocok.
 */
class Faq
{
    /**
     * @return array<int, array{question: string, answer: string}>
     */
    public static function landing(): array
    {
        return [
            [
                'question' => 'Apa itu Read-Assist?',
                'answer' => 'Read-Assist adalah platform aksesibilitas yang mengubah buku cetak dan berkas digital menjadi pembacaan audio. Setiap buku diberi kode QR unik di sampulnya, dan siapa pun yang memindai kode itu langsung mendengar naskah buku dibacakan per kalimat.',
            ],
            [
                'question' => 'Siapa saja yang dapat memakai Read-Assist?',
                'answer' => 'Read-Assist dirancang untuk penyandang tunanetra, tuna netra, dan siapa pun yang mengalami kesulitan membaca teks cetak. Antarmuka mendukung kontras tinggi, navigasi keyboard penuh, serta kompatibel dengan pembaca layar seperti TalkBack dan NVDA.',
            ],
            [
                'question' => 'Format berkas apa saja yang didukung?',
                'answer' => 'Dua format yang paling umum didukung, yaitu PDF dan EPUB. Sistem mengekstraksi teks secara otomatis, memecahnya menjadi potongan kalimat, lalu menyintesis audio untuk setiap bagian. Buku berskala besar diproses bertahap melalui antrean agar tidak membebani server.',
            ],
            [
                'question' => 'Apakah saya perlu memasang aplikasi tambahan?',
                'answer' => 'Tidak. Read-Assist berjalan sepenuhnya di browser bawaan. Memindai kode QR cukup memakai aplikasi kamera smartphone, lalu halaman pemutar langsung terbuka. Tidak ada aplikasi yang harus diunduh dan tidak ada biaya langganan.',
            ],
            [
                'question' => 'Apakah progres membaca saya tersimpan?',
                'answer' => 'Ya. Kalimat terakhir yang sedang didengarkan tersimpan otomatis di perangkat Anda. Ketika kode QR dipindai ulang, pemutar melanjutkan tepat dari kalimat yang sama sehingga Anda tidak perlu mencari posisi terakhir secara manual.',
            ],
            [
                'question' => 'Berapa lama buku saya siap didengarkan?',
                'answer' => 'Buku pendek biasanya selesai beberapa menit setelah diunggah. Buku panjang diproses di latar belakang per bagian, dan status pemrosesannya dapat dipantau melalui dasbor, termasuk menjalankan ulang bagian tertentu bila sintesis audio gagal.',
            ],
            [
                'question' => 'Bagaimana cara mendaftarkan buku saya?',
                'answer' => 'Akun pengelola buku masuk ke halaman katalog, unggah berkas PDF atau EPUB, lalu sistem membuat kode QR unik untuk buku tersebut. Kode itu bisa diunduh, dicetak, lalu ditempelkan pada sampul buku fisik agar siapa pun dapat memindainya.',
            ],
        ];
    }
}
