<?php

namespace Tests\Unit;

use App\Services\TTSEngine;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
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

        config()->set('tts.provider', 'google');
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

    public function test_provider_cloud_meminta_voice_perempuan_dan_menyimpan_audio_hasil_decode(): void
    {
        $history = [];
        $mock = new MockHandler([
            new Response(200, [], json_encode(['audioContent' => base64_encode('MP3-PEREMPUAN')])),
        ]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        config()->set('tts.provider', 'google_cloud');
        config()->set('tts.google_cloud.key', 'kunci-test');

        $tts = new TTSEngine(new Client(['handler' => $stack, 'timeout' => 30]));
        $output = $this->tmpDir.'/cloud_0001.mp3';

        $this->assertTrue($tts->generateSentence('Halo dunia', $output, 1));
        $this->assertSame('MP3-PEREMPUAN', (string) file_get_contents($output));

        $this->assertCount(1, $history);
        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('kunci-test', $request->getHeaderLine('x-goog-api-key'));

        $payload = json_decode((string) $request->getBody(), true);
        $this->assertSame('id-ID', $payload['voice']['languageCode']);
        $this->assertSame('id-ID-Wavenet-A', $payload['voice']['name']);
        $this->assertSame('FEMALE', $payload['voice']['ssmlGender']);
        $this->assertSame('MP3', $payload['audioConfig']['audioEncoding']);
    }

    public function test_provider_cloud_tanpa_kunci_jatuh_ke_provider_google(): void
    {
        config()->set('tts.provider', 'google_cloud');
        config()->set('tts.google_cloud.key', null);

        $mock = new MockHandler([new Response(200, [], 'MP3-LAKI-LAKI')]);
        $tts = new TTSEngine(new Client(['handler' => HandlerStack::create($mock), 'timeout' => 30]));
        $output = $this->tmpDir.'/fallback_0001.mp3';

        $this->assertTrue($tts->generateSentence('Halo dunia', $output, 1));
        $this->assertSame('MP3-LAKI-LAKI', (string) file_get_contents($output));
    }

    public function test_provider_cloud_mengembalikan_false_jika_audio_content_tidak_valid(): void
    {
        config()->set('tts.provider', 'google_cloud');
        config()->set('tts.google_cloud.key', 'kunci-test');

        $mock = new MockHandler([
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        ]);
        $tts = new TTSEngine(new Client(['handler' => HandlerStack::create($mock), 'timeout' => 30]));
        $output = $this->tmpDir.'/cloud_gagal.mp3';

        $this->assertFalse($tts->generateSentence('Halo dunia', $output, 1));
        $this->assertFileDoesNotExist($output);
    }

    public function test_provider_cloud_memotong_kalimat_panjang_seperti_provider_google(): void
    {
        config()->set('tts.provider', 'google_cloud');
        config()->set('tts.google_cloud.key', 'kunci-test');
        config()->set('tts.google_cloud.max_chars', 40);

        $mock = new MockHandler([
            new Response(200, [], json_encode(['audioContent' => base64_encode('CLOUD-A')])),
            new Response(200, [], json_encode(['audioContent' => base64_encode('CLOUD-B')])),
            new Response(200, [], json_encode(['audioContent' => base64_encode('CLOUD-C')])),
        ]);
        $tts = new TTSEngine(new Client(['handler' => HandlerStack::create($mock), 'timeout' => 30]));
        $output = $this->tmpDir.'/cloud_panjang.mp3';

        $this->assertTrue($tts->generateSentence(str_repeat('kalimat yang cukup panjang untuk dipecah ', 3), $output, 2));
        $this->assertSame('CLOUD-ACLOUD-BCLOUD-C', (string) file_get_contents($output));
        $this->assertSame([], glob($this->tmpDir.'/tmp_*'));
    }
}
