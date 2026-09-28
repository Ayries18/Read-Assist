<?php

namespace Tests\Support;

use App\Services\TTSEngine;
use Illuminate\Support\Facades\File;

/**
 * TTSEngine palsu untuk feature test chunked audio generation.
 *
 * Menulis file MP3 dummy sehingga test dapat memverifikasi chunk mana yang
 * benar-benar disintesis tanpa memanggil endpoint TTS sungguhan.
 */
class FakeTtsEngine extends TTSEngine
{
    /** @var array<int, int> */
    public array $generated = [];

    public function generateSentence(string $text, string $outputPath, int $index): bool
    {
        File::ensureDirectoryExists(dirname($outputPath));

        $written = file_put_contents($outputPath, 'FAKE-MP3-'.$index.'-'.$text);

        if ($written === false) {
            return false;
        }

        $this->generated[] = $index;

        return true;
    }

    public function concatAudio(array $sentenceFiles, string $outputPath): bool
    {
        File::ensureDirectoryExists(dirname($outputPath));

        $combined = '';
        foreach ($sentenceFiles as $file) {
            if (is_file($file) && filesize($file) > 0) {
                $combined .= file_get_contents($file);
            }
        }

        if ($combined === '') {
            return false;
        }

        return file_put_contents($outputPath, $combined) !== false;
    }
}
