<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\AudioBuku;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = $this->resolveSeedAdmin();

        if (! $this->isProduction()) {
            $this->seedDevelopmentUser();
        }

        $this->seedSampleBook($admin);
    }

    /**
     * Akun admin pemilik buku contoh.
     *
     * Di production akun admin hanya DICARI berdasarkan email dan tidak
     * pernah dibuat maupun di-update: password-nya dikelola manual di luar
     * repository ini. Menjalankan seeder di production tidak boleh mengubah
     * kredensial admin existing.
     *
     * Di luar production, akun dummy hanya dibuat bila email dan password
     * keduanya diisi lewat .env. Tidak ada nilai default, jadi seeder tidak
     * pernah menghasilkan password lemah yang bocor di riwayat git.
     */
    protected function resolveSeedAdmin(): ?Admin
    {
        $email = config('seeder.admin_email');

        if ($this->isProduction()) {
            if (blank($email)) {
                $this->command?->warn('SEED_ADMIN_EMAIL kosong: buku contoh dibuat tanpa admin_id.');

                return null;
            }

            return Admin::where('email', $email)->first();
        }

        $password = config('seeder.admin_password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('SEED_ADMIN_EMAIL / ADMIN_SEED_PASSWORD kosong: akun admin dummy dilewati.');

            return null;
        }

        return Admin::updateOrCreate(['email' => $email], [
            'nama' => 'Admin Read Assist',
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Akun user dummy hanya untuk development.
     *
     * Di production seluruh akun dikelola manual, jadi seeder tidak boleh
     * membuat maupun menimpa password user existing. Kredensial production
     * juga tidak pernah dibaca di sini.
     */
    protected function seedDevelopmentUser(): void
    {
        $email = config('seeder.user_email');
        $password = config('seeder.user_password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('SEED_USER_EMAIL / USER_SEED_PASSWORD kosong: akun user dummy dilewati.');

            return;
        }

        User::updateOrCreate(['email' => $email], [
            'name' => 'Muwarisin',
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Buku contoh untuk mencoba fitur reader.
     *
     * `admin_id` hanya dimasukkan ke payload bila admin benar-benar ditemukan.
     * Kolomnya nullable, jadi buku tetap bisa dibuat tanpa admin — yang penting
     * `null` tidak menimpa admin_id milik buku yang sudah ada.
     *
     * Di production memakai firstOrCreate: data buku existing tidak ditulis
     * ulang. Di luar production boleh updateOrCreate supaya seeding ulang
     * menyegarkan isi buku contoh.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function seedSampleBook(?Admin $admin, array $attributes = []): void
    {
        $attributes = array_merge([
            'judul' => 'Buku Panduan Read Assist',
            'penulis' => 'Tim Developer',
            'kategori' => 'Panduan',
            'deskripsi' => $this->sampleDescription(),
            'file_audio' => 'tts',
            'qr_token' => Str::uuid(),
        ], $attributes);

        if ($admin !== null) {
            $attributes['admin_id'] = $admin->id;
        }

        $book = $this->isProduction()
            ? AudioBuku::firstOrCreate(['id' => 1], $attributes)
            : AudioBuku::updateOrCreate(['id' => 1], $attributes);

        $this->command?->info("Buku contoh #{$book->id} siap (admin_id: ".($book->admin_id ?? 'null').').');
    }

    protected function isProduction(): bool
    {
        return app()->environment('production');
    }

    protected function sampleDescription(): string
    {
        return 'Selamat datang di Read Assist. Aplikasi ini adalah sistem pemutar buku audio untuk tunanetra. Dengan Read Assist, pengguna dapat mendengarkan buku hanya dengan memindai kode QR. Sangat mudah dan praktis.

Read Assist memiliki beberapa fitur utama. Fitur pertama adalah pemutar suara otomatis. Buku akan dibacakan kalimat demi kalimat dengan suara yang jelas. Fitur kedua adalah kode QR. Setiap buku memiliki kode QR unik. Pengguna tunanetra cukup memindai kode QR untuk langsung mendengarkan buku.

Fitur ketiga adalah dukungan berbagai format file. Read Assist mendukung file PDF dan EPUB. Sistem akan mengekstrak teks dari file tersebut secara otomatis. Fitur keempat adalah mode aksesibilitas tinggi. Tersedia pengaturan kontras tinggi dan ukuran teks yang dapat disesuaikan.

Read Assist dikembangkan untuk membantu teman-teman tunanetra. Kami percaya bahwa akses terhadap ilmu pengetahuan adalah hak semua orang. Dengan teknologi Text-to-Speech, buku-buku dapat dinikmati tanpa harus melihat layar.

Cara menggunakan Read Assist sangat sederhana. Pertama, buka halaman utama aplikasi. Kedua, pilih buku yang ingin didengarkan dari katalog. Ketiga, scan kode QR menggunakan ponsel Anda. Keempat, buku akan mulai dibacakan secara otomatis. Nikmati pengalaman membaca yang baru.

Read Assist juga mendukung navigasi keyboard. Tekan spasi untuk memutar atau jeda suara. Tekan panah kanan untuk kalimat selanjutnya. Tekan panah kiri untuk kalimat sebelumnya. Tekan Escape untuk berhenti. Semua pintasan ini memudahkan pengguna tunanetra dalam bernavigasi.

Kami harap Anda menikmati aplikasi Read Assist. Terima kasih telah menggunakan layanan kami. Selamat membaca dan mendengarkan.';
    }
}
