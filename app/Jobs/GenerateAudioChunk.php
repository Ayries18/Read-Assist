<?php

namespace App\Jobs;

use App\Models\AudioBuku;
use App\Services\AudioChunkPlan;
use App\Services\TTSEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menyintesis satu bagian (chunk) dari sebuah buku.
 *
 * Setiap kalimat menghasilkan file MP3 sendiri di disk. Kalau file tersebut
 * sudah ada dan tidak kosong, job langsung melewatinya sehingga pekerjaan
 * yang sudah selesai tidak terulang dan tidak hilang saat worker mati di
 * tengah jalan. Setelah chunk terakhir selesai, job FinalizeBookAudio
 * menggabungkan seluruh potongan menjadi satu file audio.
 */
class GenerateAudioChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public AudioBuku $audioBook;

    public int $chunkIndex;

    public int $timeout = 600;

    public int $tries = 1;

    public bool $deleteWhenMissingModels = true;

    public function __construct(AudioBuku $audioBook, int $chunkIndex)
    {
        $this->audioBook = $audioBook;
        $this->chunkIndex = max(1, $chunkIndex);
    }

    public function handle(TTSEngine $tts, AudioChunkPlan $plan): void
    {
        $bookId = $this->audioBook->id;
        $saved = $plan->load($bookId);

        if ($saved === null) {
            $this->audioBook->update([
                'audio_status' => 'failed',
                'audio_message' => 'Rencana chunk hilang, jalankan ulang generate audio.',
            ]);
            Log::error("GenerateAudioChunk #{$bookId}: plan.json tidak ditemukan.");

            return;
        }

        $totalChunks = $plan->totalChunksOf($saved);
        $totalSentences = $plan->totalSentencesOf($saved);
        $sentences = $plan->sentencesForChunk($saved, $this->chunkIndex);
        $startIndex = $plan->firstSentenceIndex($saved, $this->chunkIndex);

        $this->audioBook->update([
            'current_chunk' => $this->chunkIndex,
            'audio_status' => 'processing',
            'audio_message' => "Menyintesis bagian {$this->chunkIndex} dari {$totalChunks}...",
        ]);

        $directory = Storage::disk('public')->path($plan->audioDir($bookId));
        File::ensureDirectoryExists($directory);

        $done = $this->countExistingSentences($directory);
        $resumed = 0;
        $last = count($sentences);

        // $done sudah menghitung seluruh MP3 yang ada (dari chunk manapun),
        // jadi kalimat yang dilewati di bawah tidak boleh dihitung dua kali.

        foreach ($sentences as $offset => $sentence) {
            $index = $startIndex + $offset;
            $path = $directory.DIRECTORY_SEPARATOR.$plan->sentenceFileName($index);

            if ($this->hasAudio($path)) {
                $resumed++;
            } else {
                $tts->generateSentence($sentence, $path, $index);

                if ($this->hasAudio($path)) {
                    $done++;
                } else {
                    Log::error("GenerateAudioChunk #{$bookId} chunk {$this->chunkIndex}: kalimat {$index} gagal.");
                }
            }

            $this->audioBook->update([
                'audio_progress' => (int) round(($done / max(1, $totalSentences)) * 100),
                'audio_message' => "Bagian {$this->chunkIndex}/{$totalChunks} - kalimat {$index} dari {$totalSentences}...",
            ]);

            if ($offset < $last - 1) {
                usleep(150000);
            }
        }

        Log::info("GenerateAudioChunk #{$bookId} chunk {$this->chunkIndex}/{$totalChunks} selesai, {$resumed} dilewati karena sudah ada.");

        if ($this->chunkIndex >= $totalChunks) {
            FinalizeBookAudio::dispatch($this->audioBook);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->audioBook->update([
            'audio_status' => 'failed',
            'audio_message' => 'Gagal memproses audio: '.Str::limit($exception->getMessage(), 120),
        ]);
        Log::error(
            "GenerateAudioChunk #{$this->audioBook->id} chunk {$this->chunkIndex} failed: ".$exception->getMessage()
        );
    }

    private function hasAudio(string $path): bool
    {
        return is_file($path) && filesize($path) > 0;
    }

    private function countExistingSentences(string $directory): int
    {
        $files = glob($directory.DIRECTORY_SEPARATOR.'sentence_*.mp3');

        return is_array($files) ? count($files) : 0;
    }
}
