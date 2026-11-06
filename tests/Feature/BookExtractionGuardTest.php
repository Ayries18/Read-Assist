<?php

namespace Tests\Feature;

use App\Exceptions\BookTextExtractionException;
use App\Http\Controllers\AudioBukuController;
use App\Jobs\GenerateBookAudio;
use App\Models\AudioBuku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookExtractionGuardTest extends TestCase
{
    use RefreshDatabase;

    private const LIMIT_MB = 25;

    public function test_pdf_di_atas_batas_25mb_ditolak_di_job()
    {
        $path = $this->makeSizedFile(self::LIMIT_MB + 1);

        try {
            $this->invokeExtractPdfText(
                new GenerateBookAudio(AudioBuku::factory()->create()),
                $path
            );
            $this->fail('Harus melempar BookTextExtractionException untuk PDF '.self::LIMIT_MB.'+1 MB.');
        } catch (BookTextExtractionException $e) {
            $this->assertStringContainsString('25 MB', $e->getMessage());
        } finally {
            @unlink($path);
        }
    }

    public function test_pdf_di_atas_batas_25mb_ditolak_di_controller()
    {
        $path = $this->makeSizedFile(self::LIMIT_MB + 1);

        try {
            $this->invokeExtractPdfText(new AudioBukuController, $path);
            $this->fail('Harus melempar BookTextExtractionException untuk PDF '.self::LIMIT_MB.'+1 MB.');
        } catch (BookTextExtractionException $e) {
            $this->assertStringContainsString(self::LIMIT_MB.' MB', $e->getMessage());
        } finally {
            @unlink($path);
        }
    }

    public function test_pdf_di_bawah_batas_tidak_ditolak()
    {
        $path = $this->makeSizedFile(1);

        try {
            $result = $this->invokeExtractPdfText(
                new GenerateBookAudio(AudioBuku::factory()->create()),
                $path
            );

            $this->assertIsString($result);
        } finally {
            @unlink($path);
        }
    }

    public function test_batas_ekstraksi_konsisten_antara_job_dan_controller()
    {
        $this->assertSame(
            GenerateBookAudio::MAX_PDF_EXTRACTION_MEGABYTES,
            self::controllerLimitMegabytes()
        );
    }

    public function test_get_local_ips_selalu_mengembalikan_alamat_valid()
    {
        $ips = AudioBukuController::getLocalIps();

        $this->assertNotEmpty($ips);

        foreach ($ips as $name => $ip) {
            $this->assertIsString($ip, "Label {$name} harus berisi string.");
            $this->assertNotFalse(
                filter_var($ip, FILTER_VALIDATE_IP),
                "Label {$name} berisi nilai yang bukan IP: {$ip}"
            );
        }
    }

    public function test_get_local_ips_tidak_pernah_kosong()
    {
        $this->assertNotEmpty(AudioBukuController::getLocalIps());
        $this->assertNotEmpty(AudioBukuController::getLocalIps());
    }

    private static function controllerLimitMegabytes(): int
    {
        $constant = (new \ReflectionClass(AudioBukuController::class))->getConstants();

        return $constant['MAX_PDF_EXTRACTION_MEGABYTES'];
    }

    private function invokeExtractPdfText(object $target, string $path): string
    {
        $method = new \ReflectionMethod($target, 'extractPdfText');
        $method->setAccessible(true);

        return (string) $method->invoke($target, $path);
    }

    private function makeSizedFile(int $megabytes): string
    {
        $path = tempnam(sys_get_temp_dir(), 'ra_pdf_');
        $handle = fopen($path, 'wb');
        ftruncate($handle, $megabytes * 1024 * 1024);
        fclose($handle);

        return $path;
    }
}
