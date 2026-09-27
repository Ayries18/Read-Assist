<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Menyimpan rencana pemecahan teks buku menjadi beberapa bagian (chunk).
 *
 * Rencana ditulis sebagai JSON di disk publik supaya setiap job chunk dapat
 * mengambil kalimatnya sendiri tanpa harus mengekstrak ulang PDF/EPUB besar.
 * File MP3 yang sudah dihasilkan tidak pernah dihapus sehingga job dapat
 * dilanjutkan dari titik STOP terakhir (resume).
 */
class AudioChunkPlan
{
    public const DEFAULT_CHUNK_SIZE = 200;

    public function __construct(private readonly int $chunkSize = self::DEFAULT_CHUNK_SIZE) {}

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    public function audioDir(int $bookId): string
    {
        return 'audio/'.$bookId;
    }

    public function planPath(int $bookId): string
    {
        return $this->audioDir($bookId).'/plan.json';
    }

    public function sentenceFileName(int $index): string
    {
        return 'sentence_'.str_pad((string) $index, 4, '0', STR_PAD_LEFT).'.mp3';
    }

    public function totalChunks(int $totalSentences): int
    {
        if ($totalSentences <= 0) {
            return 0;
        }

        return (int) ceil($totalSentences / $this->chunkSize);
    }

    /**
     * @param  array<int, string>  $sentences
     * @return array{chunk_size:int,total_sentences:int,total_chunks:int,sentences:array<int,string>}
     */
    public function save(int $bookId, array $sentences): array
    {
        $sentences = array_values($sentences);

        $plan = [
            'chunk_size' => $this->chunkSize,
            'total_sentences' => count($sentences),
            'total_chunks' => $this->totalChunks(count($sentences)),
            'sentences' => $sentences,
        ];

        $encoded = json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        Storage::disk('public')->put($this->planPath($bookId), $encoded === false ? '{}' : $encoded);

        return $plan;
    }

    /**
     * @return array{chunk_size?:int,total_sentences?:int,total_chunks?:int,sentences?:array<int,string>}|null
     */
    public function load(int $bookId): ?array
    {
        $path = $this->planPath($bookId);

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        $decoded = json_decode((string) Storage::disk('public')->get($path), true);

        if (! is_array($decoded) || ! isset($decoded['sentences']) || ! is_array($decoded['sentences'])) {
            return null;
        }

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $plan
     * @return array<int, string>
     */
    public function sentencesForChunk(array $plan, int $chunkIndex): array
    {
        $sentences = array_values($plan['sentences'] ?? []);
        $offset = ($chunkIndex - 1) * $this->chunkSizeOf($plan);

        return array_slice($sentences, $offset, $this->chunkSizeOf($plan));
    }

    /**
     * Indeks (1-based) kalimat pertama milik sebuah chunk.
     *
     * @param  array<string, mixed>  $plan
     */
    public function firstSentenceIndex(array $plan, int $chunkIndex): int
    {
        return ($chunkIndex - 1) * $this->chunkSizeOf($plan) + 1;
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    public function totalSentencesOf(array $plan): int
    {
        return (int) ($plan['total_sentences'] ?? count($plan['sentences'] ?? []));
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    public function totalChunksOf(array $plan): int
    {
        return (int) ($plan['total_chunks'] ?? $this->totalChunks($this->totalSentencesOf($plan)));
    }

    public function forget(int $bookId): void
    {
        Storage::disk('public')->delete($this->planPath($bookId));
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    private function chunkSizeOf(array $plan): int
    {
        $size = (int) ($plan['chunk_size'] ?? self::DEFAULT_CHUNK_SIZE);

        return $size > 0 ? $size : self::DEFAULT_CHUNK_SIZE;
    }
}
