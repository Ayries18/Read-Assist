<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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