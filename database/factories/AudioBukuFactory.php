<?php

namespace Database\Factories;

use App\Models\AudioBuku;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AudioBukuFactory extends Factory
{
    protected $model = AudioBuku::class;

    public function definition(): array
    {
        return [
            'judul' => fake()->sentence(3),
            'penulis' => fake()->name(),
            'kategori' => fake()->word(),
            'deskripsi' => fake()->paragraphs(3, true),
            'file_audio' => 'tts',
            'audio_status' => 'completed',
            'qr_token' => Str::random(32),
        ];
    }
}
