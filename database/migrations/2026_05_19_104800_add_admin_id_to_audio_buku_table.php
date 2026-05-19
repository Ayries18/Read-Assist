<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audio_buku', function (Blueprint $table) {
            $table->foreignId('admin_id')
                ->nullable()
                ->after('user_id')
                ->constrained('admin')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('audio_buku', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_id');
        });
    }
};
