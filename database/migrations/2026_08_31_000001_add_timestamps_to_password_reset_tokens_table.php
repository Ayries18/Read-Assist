<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Isi created_at untuk token yang belum terisi agar data lama tetap valid.
        DB::table('password_reset_tokens')
            ->whereNull('created_at')
            ->update(['created_at' => now()]);

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};
