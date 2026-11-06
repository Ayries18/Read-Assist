<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TTSEngine
{
    protected Client $http;

    protected int $timeout;

    public function __construct(?Client $http = null)
    {
        $this->http = $http ?? new Client([
            'timeout' => config('tts.timeout', 120),
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
        ]);
        $this->timeout = config('tts.timeout', 120);
    }

    public function generateSentence(string $text, string $outputPath, int $index): bool
    {
        $this->ensureDir(dirname($outputPath));

        $provider = config('tts.provider', 'google');

        // Dispatch ke provider yang dikonfigurasi. Tambahkan case baru di sini
        // saat provider TTS lain ditambahkan (mis. openai, elevenlabs, dll).
        try {
            $result = match ($provider) {
                'google' => $this->generateWithGoogleTts($text, $outputPath),
                default => $this->generateWithGoogleTts($text, $outputPath),
            };

            if ($result) {
                Log::info("TTS: provider '{$provider}' berhasil untuk kalimat #{$index}");

                return true;
            }
            Log::warning("TTS: provider '{$provider}' mengembalikan hasil kosong untuk kalimat #{$index}");
        } catch (\Throwable $e) {
            Log::error("TTS: provider '{$provider}' gagal untuk kalimat #{$index}: {$e->getMessage()}");
        }

        return false;
    }

    protected function generateWithGoogleTts(string $text, string $outputPath): bool
    {
        $maxLen = (int) config('tts.google.max_chars', 180);
        $text = trim($text);
        if ($text === '') {
            return false;
        }

        if (mb_strlen($text) > $maxLen) {
            $words = preg_split('/\s+/', $text);
            $parts = [];
            $buf = '';
            foreach ($words as $word) {
                $candidate = trim($buf.' '.$word);
                if (mb_strlen($candidate) <= $maxLen) {
                    $buf = $candidate;
                } else {
                    if ($buf !== '') {
                        $parts[] = $buf;
                    }
                    $buf = $word;
                }
            }
            if ($buf !== '') {
                $parts[] = $buf;
            }

            $combined = '';
            foreach ($parts as $i => $part) {
                $partPath = $this->partFilePath($outputPath, $i);
                $success = $this->downloadTts($part, $partPath);
                if (! $success) {
                    Log::warning("TTS: kalimat terpotong gagal pada bagian ke-{$i}; output dibatalkan.");
                    $this->cleanupPartFiles($outputPath);
                    @unlink($outputPath);

                    return false;
                }
                $combined .= file_get_contents($partPath);
                @unlink($partPath);
            }

            if ($combined === '') {
                return false;
            }
            file_put_contents($outputPath, $combined);
        } else {
            $success = $this->downloadTts($text, $outputPath);
            if (! $success) {
                return false;
            }
        }

        return file_exists($outputPath) && filesize($outputPath) > 0;
    }

    protected function downloadTts(string $text, string $outputPath): bool
    {
        $url = config('tts.google.url', 'https://translate.google.com/translate_tts');
        $query = http_build_query([
            'ie' => 'UTF-8',
            'q' => $text,
            'tl' => 'id',
            'client' => 'tw-ob',
        ]);

        $maxAttempts = 3;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = $this->http->get($url.'?'.$query);

                if ($response->getStatusCode() === 200) {
                    $body = $response->getBody()->getContents();
                    if (! empty($body)) {
                        file_put_contents($outputPath, $body);

                        return true;
                    }
                }
            } catch (\Throwable $e) {
                if ($attempt === $maxAttempts) {
                    Log::warning("TTS Download attempt {$attempt} failed: {$e->getMessage()}");

                    return false;
                }
                // Exponential backoff sleep (0.5s, 1s)
                usleep($attempt * 500000);
            }
        }

        return false;
    }

    /**
     * Nama file sementara untuk bagian kalimat yang dipotong. Memakai prefix
     * "tmp_" dan diakhiri ".mp3" agar tidak termakan pola glob sentence_*.mp3
     * milik resume (patch: file part yang lama "sentence_0001.mp3.part0.mp3"
     * bocor ke dalam jumlah kalimat yang dianggap sudah selesai).
     */
    protected function partFilePath(string $outputPath, int $index): string
    {
        return dirname($outputPath).DIRECTORY_SEPARATOR.'tmp_'.basename($outputPath).'_part'.$index.'.mp3';
    }

    /**
     * Menghapus semua file part sementara milik satu kalimat (maksimal 64 part).
     */
    protected function cleanupPartFiles(string $outputPath): void
    {
        for ($i = 0; $i < 64; $i++) {
            $partPath = $this->partFilePath($outputPath, $i);
            if (is_file($partPath)) {
                @unlink($partPath);
            } else {
                break;
            }
        }

        $this->cleanupLegacyPartFiles($outputPath);
    }

    /**
     * Membersihkan sisa file part lama bergaya "sentence_0001.mp3.part0.mp3"
     * yang mungkin masih tersisa di produksi dari logika sebelum v1.1.
     */
    protected function cleanupLegacyPartFiles(string $outputPath): void
    {
        for ($i = 0; $i < 64; $i++) {
            $legacyPath = $outputPath.'.part'.$i.'.mp3';
            if (is_file($legacyPath)) {
                @unlink($legacyPath);
            } else {
                break;
            }
        }
    }

    public function concatAudio(array $sentenceFiles, string $outputPath): bool
    {
        $this->ensureDir(dirname($outputPath));

        if (empty($sentenceFiles)) {
            return false;
        }

        $existingFiles = array_values(array_filter($sentenceFiles, fn ($f) => file_exists($f) && filesize($f) > 0));
        if (empty($existingFiles)) {
            return false;
        }

        if (count($existingFiles) === 1) {
            return copy($existingFiles[0], $outputPath);
        }

        return $this->concatAudioStreaming($existingFiles, $outputPath);
    }

    /**
     * Menggabungkan potongan MP3 dengan streaming (bukan menyalin seluruh
     * isi ke satu string) supaya buku dengan ribuan kalimat tidak menghabiskan
     * memory_limit pada shared hosting.
     *
     * @param  array<int, string>  $existingFiles
     */
    protected function concatAudioStreaming(array $existingFiles, string $outputPath): bool
    {
        $output = @fopen($outputPath, 'wb');
        if ($output === false) {
            return false;
        }

        $written = 0;

        foreach ($existingFiles as $file) {
            $input = @fopen($file, 'rb');
            if ($input === false) {
                continue;
            }

            $bytes = stream_copy_to_stream($input, $output);
            fclose($input);

            if ($bytes > 0) {
                $written += $bytes;
            }
        }

        fclose($output);

        return $written > 0 && file_exists($outputPath) && filesize($outputPath) > 0;
    }

    protected function ensureDir(string $dir): void
    {
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public static function splitSentences(string $text, string $title = 'Buku'): array
    {
        $ideLen = (int) config('tts.google.ide_chars', 150);
        $sentences = [];

        $sentences[] = 'Membaca buku: '.$title.'.';

        $paragraphs = preg_split('/\R+/', $text);
        foreach ($paragraphs as $para) {
            $trimmed = trim($para);
            if ($trimmed === '') {
                continue;
            }

            $parts = preg_split('/(?<=[.!?])\s+/', $trimmed);
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') {
                    continue;
                }

                if (mb_strlen($part) > $ideLen) {
                    $words = preg_split('/\s+/', $part);
                    $buf = '';
                    foreach ($words as $word) {
                        $candidate = trim($buf.' '.$word);
                        if (mb_strlen($candidate) <= $ideLen) {
                            $buf = $candidate;
                        } else {
                            if ($buf !== '') {
                                $sentences[] = $buf;
                            }
                            $buf = $word;
                        }
                    }
                    if ($buf !== '') {
                        $sentences[] = $buf;
                    }
                } else {
                    $sentences[] = $part;
                }
            }
        }

        return $sentences;
    }
}
