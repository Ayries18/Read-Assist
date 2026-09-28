<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audio_buku', function (Blueprint $table) {
            $table->unsignedInteger('total_sentences')->default(0)->after('audio_message');
            $table->unsignedSmallInteger('total_chunks')->default(0)->after('total_sentences');
            $table->unsignedSmallInteger('current_chunk')->default(0)->after('total_chunks');
        });
    }

    public function down(): void
    {
        Schema::table('audio_buku', function (Blueprint $table) {
            $table->dropColumn(['total_sentences', 'total_chunks', 'current_chunk']);
        });
    }
};
