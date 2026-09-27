<?php

namespace Tests\Feature;

use App\Jobs\FinalizeBookAudio;
use App\Jobs\GenerateAudioChunk;
use App\Jobs\GenerateBookAudio;
use App\Models\AudioBuku;
use App\Services\AudioChunkPlan;
use App\Services\TTSEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Support\FakeTtsEngine;
use Tests\TestCase;

class ChunkedAudioGenerationTest extends TestCase
{
    use RefreshDatabase;

    private FakeTtsEngine $tts;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->tts = new FakeTtsEngine;
        $this->app->instance(TTSEngine::class, $this->tts);
    }

    public function test_resume_melewati_kalimat_yang_sudah_terhasilkan_sebelumnya()
    {
        $book = AudioBuku::factory()->create();
        $plan = $this->bindPlan(new AudioChunkPlan(5));

        $plan->save($book->id, $this->sentences(10));

        $directory = $this->sentenceDirectory($plan, $book->id);
        File::ensureDirectoryExists($directory);

        foreach ([1, 2, 3] as $index) {
            file_put_contents(
                $directory.DIRECTORY_SEPARATOR.$plan->sentenceFileName($index),
                'SUDAH-ADA-'.$index
            );
        }

        (new GenerateAudioChunk($book, 1))->handle($this->tts, $plan);

        $this->assertSame([4, 5], $this->tts->generated, 'Hanya kalimat yang belum ada yang disintesis ulang.');

        foreach ([1, 2, 3] as $index) {
            $path = $directory.DIRECTORY_SEPARATOR.$plan->sentenceFileName($index);
            $this->assertFileExists($path, "MP3 yang sudah jadi tidak boleh dihapus (kalimat {$index}).");
            $this->assertSame("SUDAH-ADA-{$index}", file_get_contents($path));
        }

        foreach ([4, 5] as $index) {
            $this->assertFileExists($directory.DIRECTORY_SEPARATOR.$plan->sentenceFileName($index));
        }
    }

    public function test_progress_bersamaan_dengan_jumlah_kalimat_yang_tersimpan()
    {
        $book = AudioBuku::factory()->create();
        $plan = $this->bindPlan(new AudioChunkPlan(5));

        $plan->save($book->id, $this->sentences(10));

        $directory = $this->sentenceDirectory($plan, $book->id);
        File::ensureDirectoryExists($directory);
        file_put_contents($directory.DIRECTORY_SEPARATOR.$plan->sentenceFileName(1), 'SUDAH-ADA-1');

        (new GenerateAudioChunk($book, 1))->handle($this->tts, $plan);

        $this->assertSame(50, (int) $book->fresh()->audio_progress, 'Lima dari sepuluh kalimat sudah tersedia.');
    }

    public function test_buku_lebih_dari_6000_kalimat_dibagi_ke_banyak_chunk()
    {
        Queue::fake();

        $book = AudioBuku::factory()->create();
        $plan = $this->bindPlan(new AudioChunkPlan(200));

        $book->update([
            'file_buku' => null,
            'deskripsi' => implode("\n\n", array_map(
                fn (int $i) => "Ini adalah kalimat nomor {$i} dalam buku yang sangat panjang.",
                range(1, 6609)
            )),
        ]);

        (new GenerateBookAudio($book))->handle($plan);

        $fresh = $book->fresh();

        $this->assertGreaterThan(6000, (int) $fresh->total_sentences, 'Buku besar harus tetap terbagi menjadi banyak kalimat.');
        $this->assertSame(
            (int) ceil($fresh->total_sentences / 200),
            (int) $fresh->total_chunks,
            'total_chunks harus mengikuti jumlah kalimat dan ukuran chunk.'
        );
        $this->assertGreaterThanOrEqual(33, (int) $fresh->total_chunks);
        $this->assertSame('processing', $fresh->audio_status);

        Queue::assertPushed(GenerateAudioChunk::class, (int) $fresh->total_chunks);
        Queue::assertPushed(
            FinalizeBookAudio::class,
            0,
            'Finalisasi hanya boleh jalan setelah semua chunk selesai.'
        );

        Storage::disk('public')->assertExists($plan->planPath($book->id));
    }

    public function test_chunk_terakhir_menghasilkan_file_audio_final()
    {
        $book = AudioBuku::factory()->create();
        $plan = $this->bindPlan(new AudioChunkPlan(5));

        $plan->save($book->id, $this->sentences(10));

        (new GenerateAudioChunk($book, 1))->handle($this->tts, $plan);
        (new GenerateAudioChunk($book, 2))->handle($this->tts, $plan);

        $fresh = $book->fresh();

        $this->assertSame('completed', $fresh->audio_status);
        $this->assertSame(100, (int) $fresh->audio_progress);
        $this->assertSame(2, (int) $fresh->current_chunk);
        $this->assertSame("audio/{$book->id}/full.mp3", $fresh->file_audio);

        $fullPath = Storage::disk('public')->path("audio/{$book->id}/full.mp3");
        $this->assertFileExists($fullPath);

        $combined = (string) file_get_contents($fullPath);
        for ($index = 1; $index <= 10; $index++) {
            $this->assertStringContainsString("FAKE-MP3-{$index}-", $combined, "Kalimat {$index} harus ada di file final.");
        }
        $this->assertLessThan(
            strpos($combined, 'FAKE-MP3-2-'),
            strpos($combined, 'FAKE-MP3-1-'),
            'Urutan kalimat harus dipertahankan saat digabungkan.'
        );
    }

    public function test_ulang_generate_tidak_mengulang_kalimat_yang_sudah_selesai()
    {
        $book = AudioBuku::factory()->create();
        $plan = $this->bindPlan(new AudioChunkPlan(5));

        $plan->save($book->id, $this->sentences(10));

        (new GenerateAudioChunk($book, 1))->handle($this->tts, $plan);
        $this->assertSame([1, 2, 3, 4, 5], $this->tts->generated);

        $this->tts->generated = [];

        (new GenerateAudioChunk($book, 1))->handle($this->tts, $plan);

        $this->assertSame([], $this->tts->generated, 'Chunk yang sudah lengkap tidak boleh disintesis ulang.');
    }

    public function test_finalisasi_melewati_kalimat_yang_kosong_tanpa_menggeser_urutan()
    {
        $book = AudioBuku::factory()->create();
        $plan = $this->bindPlan(new AudioChunkPlan(5));

        $plan->save($book->id, $this->sentences(10));

        $directory = $this->sentenceDirectory($plan, $book->id);
        File::ensureDirectoryExists($directory);

        foreach (range(1, 10) as $index) {
            file_put_contents(
                $directory.DIRECTORY_SEPARATOR.$plan->sentenceFileName($index),
                $index === 2 ? '' : "FAKE-MP3-{$index}-"
            );
        }

        (new FinalizeBookAudio($book))->handle($this->tts, $plan);

        $fresh = $book->fresh();

        $this->assertSame('completed', $fresh->audio_status);
        $this->assertSame(100, (int) $fresh->audio_progress);

        $combined = (string) file_get_contents(Storage::disk('public')->path("audio/{$book->id}/full.mp3"));

        $this->assertSame(9, substr_count($combined, 'FAKE-MP3-'), 'Potongan kosong harus dilewati.');
        $this->assertStringContainsString('FAKE-MP3-1-', $combined);
        $this->assertStringContainsString('FAKE-MP3-3-', $combined);
        $this->assertLessThan(
            strpos($combined, 'FAKE-MP3-3-'),
            strpos($combined, 'FAKE-MP3-1-'),
            'Kalimat setelah yang kosong tidak boleh bergeser.'
        );
    }

    private function bindPlan(AudioChunkPlan $plan): AudioChunkPlan
    {
        $this->app->instance(AudioChunkPlan::class, $plan);

        return $plan;
    }

    private function sentenceDirectory(AudioChunkPlan $plan, int $bookId): string
    {
        return Storage::disk('public')->path($plan->audioDir($bookId));
    }

    /**
     * @return array<int, string>
     */
    private function sentences(int $count): array
    {
        return array_map(fn (int $i) => "Kalimat nomor {$i}.", range(1, $count));
    }
}
