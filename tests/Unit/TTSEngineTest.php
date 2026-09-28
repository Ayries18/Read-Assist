<?php

namespace Tests\Unit;

use App\Services\TTSEngine;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TTSEngineTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tmpDir = sys_get_temp_dir().'/tts-test-'.uniqid();
        File::ensureDirectoryExists($this->tmpDir);

        config()->set('tts.google.max_chars', 40);
        config()->set('tts.timeout', 30);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tmpDir);

        parent::tearDown();
    }

    public function test_kalimat_panjang_yang_semua_part_berhasil_digabung_dan_part_dibersihkan(): void
    {
        $mock = new MockHandler([
            new Response(200, [], 'PART-A'),
            new Response(200, [], 'PART-B'),
            new Response(200, [], 'PART-C'),
        ]);

        $tts = new TTSEngine(new Client(['handler' => HandlerStack::create($mock), 'timeout' => 30]));

        $output = $this->tmpDir.'/sentence_0001.mp3';
        $longText = str_repeat('kalimat yang cukup panjang untuk dipecah ', 3);

        $this->assertTrue($tts->generateSentence($longText, $output, 1));
        $this->assertSame('PART-APART-BPART-C', (string) file_get_contents($output));
        $this->assertSame([], glob($this->tmpDir.'/tmp_*'), 'File part sementara harus dibersihkan setelah sukses.');
        $this->assertSame([], glob($this->tmpDir.'/*.part*.mp3'), 'Sisa part lama tidak boleh tertinggal.');
    }

    public function test_kalimat_panjang_dengan_part_gagal_dikembalikan_false_tanpa_menyisakan_file(): void
    {
        $mock = new MockHandler([
            new Response(200, [], 'PART-A'),
            new ConnectException('Koneksi TTS terputus', new Request('GET', 'test')),
        ]);

        $tts = new TTSEngine(new Client(['handler' => HandlerStack::create($mock), 'timeout' => 30]));

        $output = $this->tmpDir.'/sentence_0002.mp3';
        $longText = str_repeat('kalimat yang cukup panjang untuk dipecah ', 3);

        $this->assertFalse($tts->generateSentence($longText, $output, 2));
        $this->assertFileDoesNotExist($output, 'Output parsial tidak boleh dibuat.');
        $this->assertSame([], glob($this->tmpDir.'/tmp_*'), 'File part parsial harus dibersihkan saat gagal.');
    }
}
