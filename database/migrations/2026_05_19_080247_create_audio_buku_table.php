<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audio_buku', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('penulis')->nullable();
            $table->string('kategori')->nullable();
            $table->longText('deskripsi')->nullable();
            $table->string('file_audio');
            $table->string('qr_token')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audio_buku');
    }
};