<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audio_buku', function (Blueprint $table) {
            $table->string('file_buku')->nullable()->after('deskripsi');
        });
    }

    public function down(): void
    {
        Schema::table('audio_buku', function (Blueprint $table) {
            $table->dropColumn('file_buku');
        });
    }
};
