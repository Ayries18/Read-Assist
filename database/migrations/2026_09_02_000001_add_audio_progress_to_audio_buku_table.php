<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audio_buku', function (Blueprint $table) {
            $table->unsignedTinyInteger('audio_progress')->default(0)->after('audio_status');
            $table->string('audio_message')->nullable()->after('audio_progress');
        });
    }

    public function down(): void
    {
        Schema::table('audio_buku', function (Blueprint $table) {
            $table->dropColumn(['audio_progress', 'audio_message']);
        });
    }
};
