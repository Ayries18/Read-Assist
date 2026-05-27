<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'muwarisin@gmail.com',
        ], [
            'name' => 'Muwarisin',
            'password' => Hash::make('Aris1234'),
        ]);

        User::updateOrCreate([
            'email' => 'muwarisin@gamil.com',
        ], [
            'name' => 'Muwarisin',
            'password' => Hash::make('Aris1234'),
        ]);

        $admin = Admin::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'nama' => 'Admin Read Assist',
            'password' => Hash::make('password'),
        ]);

        $sampleDescription = 'Selamat datang di Read Assist. Aplikasi ini adalah sistem pemutar buku audio untuk tunanetra. Dengan Read Assist, pengguna dapat mendengarkan buku hanya dengan memindai kode QR. Sangat mudah dan praktis.

Read Assist memiliki beberapa fitur utama. Fitur pertama adalah pemutar suara otomatis. Buku akan dibacakan kalimat demi kalimat dengan suara yang jelas. Fitur kedua adalah kode QR. Setiap buku memiliki kode QR unik. Pengguna tunanetra cukup memindai kode QR untuk langsung mendengarkan buku.

Fitur ketiga adalah dukungan berbagai format file. Read Assist mendukung file PDF dan EPUB. Sistem akan mengekstrak teks dari file tersebut secara otomatis. Fitur keempat adalah mode aksesibilitas tinggi. Tersedia pengaturan kontras tinggi dan ukuran teks yang dapat disesuaikan.

Read Assist dikembangkan untuk membantu teman-teman tunanetra. Kami percaya bahwa akses terhadap ilmu pengetahuan adalah hak semua orang. Dengan teknologi Text-to-Speech, buku-buku dapat dinikmati tanpa harus melihat layar.

Cara menggunakan Read Assist sangat sederhana. Pertama, buka halaman utama aplikasi. Kedua, pilih buku yang ingin didengarkan dari katalog. Ketiga, scan kode QR menggunakan ponsel Anda. Keempat, buku akan mulai dibacakan secara otomatis. Nikmati pengalaman membaca yang baru.

Read Assist juga mendukung navigasi keyboard. Tekan spasi untuk memutar atau menjeda suara. Tekan panah kanan untuk kalimat selanjutnya. Tekan panah kiri untuk kalimat sebelumnya. Tekan Escape untuk berhenti. Semua pintasan ini memudahkan pengguna tunanetra dalam bernavigasi.

Kami harap Anda menikmati aplikasi Read Assist. Terima kasih telah menggunakan layanan kami. Selamat membaca dan mendengarkan.';

        \App\Models\AudioBuku::updateOrCreate([
            'id' => 1,
        ], [
            'admin_id' => $admin->id,
            'judul' => 'Buku Panduan Read Assist',
            'penulis' => 'Tim Developer',
            'kategori' => 'Panduan',
            'deskripsi' => $sampleDescription,
            'file_audio' => 'tts',
            'qr_token' => \Illuminate\Support\Str::uuid(),
        ]);
    }
}
