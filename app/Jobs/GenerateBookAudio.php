<?php

namespace App\Jobs;

use App\Models\AudioBuku;
use App\Services\TTSEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateBookAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public AudioBuku $audioBook;
    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(AudioBuku $audioBook)
    {
        $this->audioBook = $audioBook;
    }

    public function handle(TTSEngine $tts): void
    {
        $this->audioBook->update(['audio_status' => 'processing']);

        $bookId = $this->audioBook->id;
        $text = $this->getFullBookText();

        if (empty(trim($text))) {
            $this->audioBook->update(['audio_status' => 'failed']);
            Log::warning("GenerateBookAudio #{$bookId}: teks buku kosong.");
            return;
        }

        $audioDir = 'audio/' . $bookId;
        $storagePath = Storage::disk('public')->path($audioDir);

        $sentences = TTSEngine::splitSentences($text, $this->audioBook->judul);
        if (empty($sentences)) {
            $this->audioBook->update(['audio_status' => 'failed']);
            Log::warning("GenerateBookAudio #{$bookId}: tidak ada kalimat.");
            return;
        }

        $sentenceFiles = [];
        $total = count($sentences);

        foreach ($sentences as $i => $sentence) {
            $index = $i + 1;
            $filename = 'sentence_' . str_pad((string) $index, 4, '0', STR_PAD_LEFT) . '.mp3';
            $outputPath = $storagePath . DIRECTORY_SEPARATOR . $filename;

            $success = $tts->generateSentence($sentence, $outputPath, $index);

            if ($success) {
                $sentenceFiles[] = $outputPath;
                Log::info("GenerateBookAudio #{$bookId}: kalimat {$index}/{$total} berhasil.");
            } else {
                Log::error("GenerateBookAudio #{$bookId}: kalimat {$index}/{$total} gagal.");
            }
        }

        if (empty($sentenceFiles)) {
            $this->audioBook->update(['audio_status' => 'failed']);
            Log::error("GenerateBookAudio #{$bookId}: semua kalimat gagal.");
            return;
        }

        $fullAudioPath = $storagePath . DIRECTORY_SEPARATOR . 'full.mp3';
        $concatSuccess = $tts->concatAudio($sentenceFiles, $fullAudioPath);

        if ($concatSuccess) {
            $this->audioBook->update([
                'file_audio' => $audioDir . '/full.mp3',
                'audio_status' => 'completed',
            ]);
            Log::info("GenerateBookAudio #{$bookId}: selesai. File: {$audioDir}/full.mp3");
        } else {
            $this->audioBook->update(['audio_status' => 'failed']);
            Log::error("GenerateBookAudio #{$bookId}: concat gagal.");
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
                if (!empty(trim($extracted))) {
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
        $tempDirectory = Storage::disk('public')->path('temp');
        if (!is_dir($tempDirectory)) {
            mkdir($tempDirectory, 0777, true);
        }

        $outputPath = $tempDirectory . DIRECTORY_SEPARATOR . Str::uuid() . '.txt';
        $command = 'pdftotext -enc UTF-8 -layout ' . escapeshellarg($path) . ' ' . escapeshellarg($outputPath);
        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($outputPath)) {
            return '';
        }

        $text = file_get_contents($outputPath) ?: '';
        @unlink($outputPath);

        return $this->cleanExtractedText($text);
    }

    protected function extractEpubText(string $path): string
    {
        if (!class_exists(\ZipArchive::class)) {
            return '';
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $text = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $lowerName = strtolower($name);
            if (!str_ends_with($lowerName, '.html') && !str_ends_with($lowerName, '.xhtml')) {
                continue;
            }
            $content = $zip->getFromIndex($i);
            if ($content === false) continue;
            $text .= ' ' . strip_tags($content);
        }

        $zip->close();
        return $this->cleanExtractedText($text);
    }

    protected function cleanExtractedText(string $text): string
    {
        if (!mb_check_encoding($text, 'UTF-8')) {
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
        $this->audioBook->update(['audio_status' => 'failed']);
        Log::error("GenerateBookAudio #{$this->audioBook->id} job failed: " . $exception->getMessage());
    }
}
