<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReadAssistController extends Controller
{
    public function index()
    {
        return view('read-assist.index');
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'min:10'],
        ]);

        $text = trim($validated['text']);

        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        $wordCount = count($words);
        $sentenceCount = count($sentences);

        // Default local fallback values
        $summary = collect($sentences)
            ->map(fn ($sentence) => trim($sentence))
            ->filter()
            ->take(2)
            ->implode('. ');

        if ($summary !== '') {
            $summary .= '.';
        }

        $keywords = collect($words)
            ->map(fn ($word) => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $word)))
            ->filter(fn ($word) => strlen($word) > 4)
            ->countBy()
            ->sortDesc()
            ->take(5)
            ->keys()
            ->values();

        // Try to generate high-quality summary and keywords using Google Gemini API
        $apiKey = config('services.gemini.key');
        if ($apiKey) {
            try {
                $prompt = "Sebagai asisten membaca pintar (Read Assist), analisis teks berikut dan berikan respon dalam format JSON objek dengan key 'summary' (berisi ringkasan teks singkat dalam 2-3 kalimat yang mudah dipahami dalam bahasa Indonesia) dan 'keywords' (berisi array berisi 5 kata kunci utama unik dari teks tersebut). Jangan sertakan format markdown apa pun (seperti ```json ... ```), cukup berikan plain JSON text yang valid.

Teks:
" . $text;

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json'
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $rawJson = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($rawJson) {
                        $resultObj = json_decode($rawJson, true);
                        if (isset($resultObj['summary']) && is_string($resultObj['summary'])) {
                            $summary = trim($resultObj['summary']);
                        }
                        if (isset($resultObj['keywords']) && is_array($resultObj['keywords'])) {
                            $keywords = collect($resultObj['keywords'])
                                ->map(fn ($kw) => trim($kw))
                                ->filter()
                                ->take(5)
                                ->values();
                        }
                    }
                } else {
                    Log::error("Gemini API Error: Code " . $response->status() . " - " . $response->body());
                }
            } catch (\Throwable $e) {
                Log::error("Gemini API Exception: " . $e->getMessage());
            }
        }

        return view('read-assist.index', [
            'text' => $text,
            'result' => [
                'word_count' => $wordCount,
                'sentence_count' => $sentenceCount,
                'summary' => $summary,
                'keywords' => $keywords,
            ],
        ]);
    }
}