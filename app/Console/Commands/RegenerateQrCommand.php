<?php

namespace App\Console\Commands;

use App\Http\Controllers\AudioBukuController;
use App\Models\AudioBuku;
use Illuminate\Console\Command;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RegenerateQrCommand extends Command
{
    protected $signature = 'qr:regenerate {--id= : Regenerate QR for specific book ID}';
    protected $description = 'Regenerate QR code files for all (or specific) books';

    public function handle(): int
    {
        $query = AudioBuku::query();

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        }

        $books = $query->get();

        if ($books->isEmpty()) {
            $this->warn('Tidak ada buku ditemukan.');
            return 0;
        }

        $baseUrl = rtrim(config('app.url'), '/');
        $this->info("Menggunakan APP_URL: {$baseUrl}");

        $qrDir = storage_path('app/public/qr');
        if (! is_dir($qrDir)) {
            mkdir($qrDir, 0755, true);
        }

        $count = 0;
        foreach ($books as $book) {
            $qrUrl = "{$baseUrl}/katalog-audio/{$book->id}";

            $svg = QrCode::size(300)
                ->margin(2)
                ->errorCorrection('M')
                ->generate($qrUrl);

            file_put_contents($qrDir . '/qr-book-' . $book->id . '.svg', $svg);

            $this->line("  [{$book->id}] {$book->judul} -> {$qrUrl}");
            $count++;
        }

        $this->info("Berhasil regenerate {$count} QR code.");
        return 0;
    }
}
