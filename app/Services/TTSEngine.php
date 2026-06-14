<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TTSEngine
{
    protected Client $http;
    protected int $timeout;

    public function __construct()
    {
        $this->http = new Client([
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

        try {
            $result = $this->generateWithGoogleTts($text, $outputPath);
            if ($result) {
                Log::info("TTS: Google TTS berhasil untuk kalimat #{$index}");
                return true;
            }
        } catch (\Throwable $e) {
            Log::error("TTS: Google TTS gagal untuk kalimat #{$index}: {$e->getMessage()}");
        }

        return false;
    }

    protected function generateWithGoogleTts(string $text, string $outputPath): bool
    {
        $maxLen = 180;
        $text = trim($text);
        if ($text === '') return false;

        if (mb_strlen($text) > $maxLen) {
            $words = preg_split('/\s+/', $text);
            $parts = [];
            $buf = '';
            foreach ($words as $word) {
                $candidate = trim($buf . ' ' . $word);
                if (mb_strlen($candidate) <= $maxLen) {
                    $buf = $candidate;
                } else {
                    if ($buf !== '') $parts[] = $buf;
                    $buf = $word;
                }
            }
            if ($buf !== '') $parts[] = $buf;

            $combined = '';
            foreach ($parts as $i => $part) {
                $partPath = $outputPath . '.part' . $i . '.mp3';
                $success = $this->downloadTts($part, $partPath);
                if ($success) {
                    $combined .= file_get_contents($partPath);
                    @unlink($partPath);
                }
            }

            if ($combined === '') return false;
            file_put_contents($outputPath, $combined);
        } else {
            $success = $this->downloadTts($text, $outputPath);
            if (!$success) return false;
        }

        return file_exists($outputPath) && filesize($outputPath) > 0;
    }

    protected function downloadTts(string $text, string $outputPath): bool
    {
        $url = 'https://translate.google.com/translate_tts';
        $query = http_build_query([
            'ie' => 'UTF-8',
            'q' => $text,
            'tl' => 'id',
            'client' => 'tw-ob',
        ]);

        $response = $this->http->get($url . '?' . $query);

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $body = $response->getBody()->getContents();
        if (empty($body)) return false;

        file_put_contents($outputPath, $body);
        return true;
    }

    public function concatAudio(array $sentenceFiles, string $outputPath): bool
    {
        $this->ensureDir(dirname($outputPath));

        if (empty($sentenceFiles)) return false;

        $existingFiles = array_values(array_filter($sentenceFiles, fn($f) => file_exists($f) && filesize($f) > 0));
        if (empty($existingFiles)) return false;

        if (count($existingFiles) === 1) {
            return copy($existingFiles[0], $outputPath);
        }

        $combined = '';
        foreach ($existingFiles as $file) {
            $combined .= file_get_contents($file);
        }

        if (empty($combined)) return false;

        file_put_contents($outputPath, $combined);
        return file_exists($outputPath) && filesize($outputPath) > 0;
    }

    protected function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public static function splitSentences(string $text, string $title = 'Buku'): array
    {
        $sentences = [];

        $sentences[] = 'Membaca buku: ' . $title . '.';

        $paragraphs = preg_split('/\R+/', $text);
        foreach ($paragraphs as $para) {
            $trimmed = trim($para);
            if ($trimmed === '') continue;

            $parts = preg_split('/(?<=[.!?])\s+/', $trimmed);
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') continue;

                if (mb_strlen($part) > 150) {
                    $words = preg_split('/\s+/', $part);
                    $buf = '';
                    foreach ($words as $word) {
                        $candidate = trim($buf . ' ' . $word);
                        if (mb_strlen($candidate) <= 150) {
                            $buf = $candidate;
                        } else {
                            if ($buf !== '') $sentences[] = $buf;
                            $buf = $word;
                        }
                    }
                    if ($buf !== '') $sentences[] = $buf;
                } else {
                    $sentences[] = $part;
                }
            }
        }

        return $sentences;
    }
}
