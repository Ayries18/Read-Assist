<?php

return [
    'timeout' => env('TTS_TIMEOUT', 120),

    // Provider teks-ke-suara. Saat ini hanya 'google' yang didukung.
    // Tentukan di sini agar mudah mengganti provider tanpa mengubah kode.
    'provider' => env('TTS_PROVIDER', 'google'),

    // Opsi khusus Google Translate TTS (endpoint tidak resmi).
    'google' => [
        'url' => env('GOOGLE_TTS_URL', 'https://translate.google.com/translate_tts'),
        'max_chars' => 180,
        'ide_chars' => 150,
    ],
];
