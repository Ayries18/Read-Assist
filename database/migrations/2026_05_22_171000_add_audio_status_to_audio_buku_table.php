<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audio_buku', function (Blueprint $table) {
            $table->string('file_audio')->nullable()->change();
            $table->string('audio_status', 20)->default('pending')->after('file_audio');
        });
    }

    public function down(): void
    {
        Schema::table('audio_buku', function (Blueprint $table) {
            $table->dropColumn('audio_status');
            $table->string('file_audio')->nullable(false)->change();
        });
    }
};
