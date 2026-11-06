<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Akun Seeder
    |--------------------------------------------------------------------------
    |
    | Kredensial untuk `DatabaseSeeder`. Semua nilai sengaja dibiarkan kosong
    | sebagai nilai default: bagian seeder yang membutuhkan kredensial akan
    | dilewati, bukan memakai fallback lemah. Ini mencegah seeder meng-attach
    | password default yang bocor di riwayat publik repository.
    |
    | Dibaca lewat config() (bukan env() langsung di seeder) karena
    | `php artisan config:cache` membuat .env tidak dimuat — env() akan
    | mengembalikan null di production.
    |
    | Jangan pernah mengisi password ini di production: akun production
    | dikelola manual di luar repository ini.
    |
    */

    // Admin yang akan dipakai sebagai pemilik (admin_id) buku contoh.
    // Di production: HANYA dibaca, tidak pernah dibuat atau diubah.
    'admin_email' => env('SEED_ADMIN_EMAIL'),

    // Password untuk admin dummy di development. Wajib diisi bersamaan
    // dengan 'admin_email' di luar production.
    'admin_password' => env('ADMIN_SEED_PASSWORD'),

    // Akun user dummy. Tidak pernah disentuh di production.
    'user_email' => env('SEED_USER_EMAIL'),

    'user_password' => env('USER_SEED_PASSWORD'),
];
