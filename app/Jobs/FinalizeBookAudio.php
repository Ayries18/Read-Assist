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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menggabungkan seluruh potongan MP3 menjadi satu file audio final.
 *
 * Dipanggil otomatis setelah GenerateAudioChunk untuk chunk terakhir selesai.
 * Nama file selalu dibangun dari daftar lengkap kalimat pada plan, bukan dari
 * hasil glob, sehingga urutannya benar dan gap yang gagal disintesis hanya
 * dilewati tanpa menggeser kalimat berikutnya.
 */
class FinalizeBookAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public AudioBuku $audioBook;

    public int $timeout = 600;

    public int $tries = 1;

    public bool $deleteWhenMissingModels = true;

    public function __construct(AudioBuku $audioBook)
    {
        $this->audioBook = $audioBook;
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
            Log::error("FinalizeBookAudio #{$bookId}: plan.json tidak ditemukan.");

            return;
        }

        $audioDir = $plan->audioDir($bookId);
        $totalSentences = $plan->totalSentencesOf($saved);
        $storagePath = Storage::disk('public')->path($audioDir);

        $sentenceFiles = [];
        $available = 0;

        for ($index = 1; $index <= $totalSentences; $index++) {
            $path = $storagePath.DIRECTORY_SEPARATOR.$plan->sentenceFileName($index);
            $sentenceFiles[] = $path;

            if (is_file($path) && filesize($path) > 0) {
                $available++;
            }
        }

        if ($available === 0) {
            $this->audioBook->update([
                'audio_status' => 'failed',
                'audio_message' => 'Tidak ada potongan audio yang berhasil disintesis.',
            ]);
            Log::error("FinalizeBookAudio #{$bookId}: tidak ada potongan audio.");

            return;
        }

        if ($available < $totalSentences) {
            $this->audioBook->update([
                'audio_message' => "Menggabungkan audio ({$available} dari {$totalSentences} kalimat tersedia)...",
            ]);
        } else {
            $this->audioBook->update([
                'audio_message' => 'Menggabungkan potongan audio...',
            ]);
        }

        $fullAudioPath = $storagePath.DIRECTORY_SEPARATOR.'full.mp3';
        $concatSuccess = $tts->concatAudio($sentenceFiles, $fullAudioPath);

        if (! $concatSuccess) {
            $this->audioBook->update([
                'audio_status' => 'failed',
                'audio_message' => 'Gagal menggabungkan audio.',
            ]);
            Log::error("FinalizeBookAudio #{$bookId}: concat gagal.");

            return;
        }

        $this->audioBook->update([
            'file_audio' => $audioDir.'/full.mp3',
            'audio_status' => 'completed',
            'audio_progress' => 100,
            'current_chunk' => $plan->totalChunksOf($saved),
            'audio_message' => 'Selesai.',
        ]);

        Log::info("FinalizeBookAudio #{$bookId}: selesai. File: {$audioDir}/full.mp3");
    }

    public function failed(\Throwable $exception): void
    {
        $this->audioBook->update([
            'audio_status' => 'failed',
            'audio_message' => 'Gagal menggabungkan audio: '.Str::limit($exception->getMessage(), 120),
        ]);
        Log::error("FinalizeBookAudio #{$this->audioBook->id} failed: ".$exception->getMessage());
    }
}
