<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listening_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('audio_buku_id')->constrained('audio_buku')->cascadeOnDelete();
            $table->integer('sentence_index')->default(0);
            $table->boolean('completed')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'audio_buku_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listening_progress');
    }
};
