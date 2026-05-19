<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'user@example.com',
        ], [
            'name' => 'Test User',
            'password' => bcrypt('password'),
        ]);

        $admin = Admin::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'nama' => 'Admin Read Assist',
            'password' => bcrypt('password'),
        ]);

        \App\Models\AudioBuku::updateOrCreate([
            'id' => 1,
        ], [
            'admin_id' => $admin->id,
            'judul' => 'Buku Panduan Read Assist',
            'penulis' => 'Tim Developer',
            'kategori' => 'Panduan',
            'deskripsi' => 'Selamat datang di Read Assist. Ini adalah buku panduan otomatis untuk menguji pemutar suara. Buku ini dibacakan kalimat demi kalimat menggunakan teknologi Text-to-Speech secara otomatis.',
            'file_audio' => 'tts',
            'qr_token' => \Illuminate\Support\Str::uuid(),
        ]);
    }
}
