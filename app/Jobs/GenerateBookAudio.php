<?php

namespace App\Jobs;

use App\Exceptions\BookTextExtractionException;
use App\Models\AudioBuku;
use App\Services\AudioChunkPlan;
use App\Services\TTSEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;

class GenerateBookAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public AudioBuku $audioBook;

    public int $timeout = 600;

    public int $tries = 1;

    public bool $deleteWhenMissingModels = true;

    /**
     * Batas ukuran PDF untuk ekstraksi teks otomatis. Parser PHP murni
     * menahan seluruh dokumen di memori sehingga PDF besar dapat
     * menghabiskan memory_limit dan membuat job gagal diam-diam.
     */
    public const MAX_PDF_EXTRACTION_MEGABYTES = 25;

    public function __construct(AudioBuku $audioBook)
    {
        $this->audioBook = $audioBook;
    }

    /**
     * Job ini hanya berfungsi sebagai orchestrator: mengekstrak teks, memecahnya
     * menjadi beberapa bagian, lalu menaruh satu job GenerateAudioChunk per
     * bagian. Sintesis TTS sebelumnya berjalan di dalam satu job sehingga PDF
     * besar (>6000 kalimat) selalu dibunuh oleh timeout 600 detik. Memecahnya
     * membuat pekerjaan bisa dilanjutkan dari chunk terakhir yang berhasil.
     */
    public function handle(AudioChunkPlan $plan): void
    {
        $this->audioBook->update([
            'audio_status' => 'processing',
            'audio_progress' => 0,
            'current_chunk' => 0,
            'audio_message' => 'Mengekstrak teks buku...',
        ]);

        $bookId = $this->audioBook->id;
        $text = $this->getFullBookText();

        if (empty(trim($text))) {
            $this->audioBook->update([
                'audio_status' => 'failed',
                'audio_message' => 'Teks buku kosong.',
            ]);
            Log::warning("GenerateBookAudio #{$bookId}: teks buku kosong.");

            return;
        }

        $sentences = TTSEngine::splitSentences($text, $this->audioBook->judul);
        if (empty($sentences)) {
            $this->audioBook->update([
                'audio_status' => 'failed',
                'audio_message' => 'Tidak ada kalimat untuk dibacakan.',
            ]);
            Log::warning("GenerateBookAudio #{$bookId}: tidak ada kalimat.");

            return;
        }

        $saved = $plan->save($bookId, $sentences);
        $totalChunks = $saved['total_chunks'];
        $totalSentences = $saved['total_sentences'];

        $this->audioBook->update([
            'total_sentences' => $totalSentences,
            'total_chunks' => $totalChunks,
            'audio_message' => "Menyintesis {$totalSentences} kalimat dalam {$totalChunks} bagian...",
        ]);

        Log::info("GenerateBookAudio #{$bookId}: {$totalSentences} kalimat dibagi menjadi {$totalChunks} chunk.");

        for ($chunkIndex = 1; $chunkIndex <= $totalChunks; $chunkIndex++) {
            GenerateAudioChunk::dispatch($this->audioBook, $chunkIndex);
        }
    }

    protected function getFullBookText(): string
    {
        $bookId = $this->audioBook->id;

        if ($this->audioBook->file_buku) {
            $filePath = Storage::disk('public')->path($this->audioBook->file_buku);
            if (file_exists($filePath)) {
                $extension = strtolower(pathinfo($this->audioBook->file_buku, PATHINFO_EXTENSION));
                $extracted = $this->extractBookText($filePath, $extension);
                if (! empty(trim($extracted))) {
                    Log::info("GenerateBookAudio #{$bookId}: teks sukses diekstrak dari file {$extension}.");

                    return $extracted;
                }
                Log::warning("GenerateBookAudio #{$bookId}: file buku ada tapi gagal diekstrak, fallback ke deskripsi.");
            }
        }

        return $this->audioBook->deskripsi ?? '';
    }

    protected function extractBookText(string $path, string $extension): string
    {
        return match ($extension) {
            'pdf' => $this->extractPdfText($path),
            'epub' => $this->extractEpubText($path),
            default => '',
        };
    }

    protected function extractPdfText(string $path): string
    {
        $this->guardPdfExtractionSize($path);

        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();

            return $this->cleanExtractedText($text);
        } catch (\Throwable $e) {
            Log::error('PDF text extraction failed: '.$e->getMessage(), [
                'path' => $path,
            ]);

            return '';
        }
    }

    /**
     * Tolak PDF yang terlalu besar sebelum masuk parser, karena parser PHP murni
     * menahan seluruh objek dokumen di memori dan dapat menguras memory_limit
     * pada shared hosting.
     *
     * @throws BookTextExtractionException
     */
    protected function guardPdfExtractionSize(string $path): void
    {
        if (! is_file($path)) {
            return;
        }

        $sizeMb = round(filesize($path) / 1048576, 1);

        if ($sizeMb <= self::MAX_PDF_EXTRACTION_MEGABYTES) {
            return;
        }

        Log::error('PDF rejected: exceeds extraction size limit.', [
            'path' => $path,
            'size_mb' => $sizeMb,
            'limit_mb' => self::MAX_PDF_EXTRACTION_MEGABYTES,
        ]);

        throw new BookTextExtractionException(
            "Ukuran PDF {$sizeMb} MB melebihi batas ".self::MAX_PDF_EXTRACTION_MEGABYTES
            .' MB untuk ekstraksi teks otomatis. Kompres atau pecah file menjadi bagian yang lebih kecil.'
        );
    }

    protected function extractEpubText(string $path): string
    {
        if (! class_exists(\ZipArchive::class)) {
            return '';
        }

        $zip = new \ZipArchive;
        if ($zip->open($path) !== true) {
            return '';
        }

        $text = '';
        $maxChars = 5000000; // 5MB character ceiling safeguard
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $lowerName = strtolower($name);
            if (! str_ends_with($lowerName, '.html') && ! str_ends_with($lowerName, '.xhtml')) {
                continue;
            }
            $content = $zip->getFromIndex($i);
            if ($content === false) {
                continue;
            }
            $text .= ' '.strip_tags($content);

            if (strlen($text) > $maxChars) {
                break;
            }
        }

        $zip->close();

        return $this->cleanExtractedText($text);
    }

    protected function cleanExtractedText(string $text): string
    {
        if (! mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
        }
        $text = iconv('UTF-8', 'UTF-8//IGNORE', $text) ?: $text;
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\R{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }

    public function failed(\Throwable $exception): void
    {
        $this->audioBook->update([
            'audio_status' => 'failed',
            'audio_message' => 'Gagal memproses audio: '.Str::limit($exception->getMessage(), 120),
        ]);
        Log::error("GenerateBookAudio #{$this->audioBook->id} job failed: ".$exception->getMessage());
    }
}
