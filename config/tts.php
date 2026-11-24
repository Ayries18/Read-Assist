<?php

return [
    'timeout' => env('TTS_TIMEOUT', 120),

    // Provider teks-ke-suara. Saat ini hanya 'google' yang didukung.
    // Tentukan di sini agar mudah mengganti provider tanpa mengubah kode.
    'provider' => env('TTS_PROVIDER', 'google'),

    // Opsi khusus Google Translate TTS (endpoint tidak resmi).
    // Endpoint ini hanya punya satu suara per bahasa (tl=id = laki-laki) dan
    // mengabaikan parameter gender/voice, jadi dipakai sebagai fallback.
    'google' => [
        'url' => env('GOOGLE_TTS_URL', 'https://translate.google.com/translate_tts'),
        'max_chars' => 180,
        'ide_chars' => 150,
    ],

    // Google Cloud Text-to-Speech (resmi, berbayar). Berbeda dengan endpoint
    // translate_tts, provider ini bisa memilih suara eksplisit. id-ID-Wavenet-A
    // adalah suara perempuan Bahasa Indonesia; id-ID-Wavenet-B adalah laki-laki.
    'google_cloud' => [
        'url' => env('GOOGLE_CLOUD_TTS_URL', 'https://texttospeech.googleapis.com/v1/text:synthesize'),
        'key' => env('GOOGLE_CLOUD_TTS_KEY'),
        'language_code' => env('GOOGLE_CLOUD_TTS_LANGUAGE', 'id-ID'),
        'voice_name' => env('GOOGLE_CLOUD_TTS_VOICE', 'id-ID-Wavenet-A'),
        'ssml_gender' => env('GOOGLE_CLOUD_TTS_GENDER', 'FEMALE'),
        'audio_encoding' => 'MP3',
        'speaking_rate' => (float) env('GOOGLE_CLOUD_TTS_RATE', 1.0),
        'max_chars' => (int) env('GOOGLE_CLOUD_TTS_MAX_CHARS', 4500),
    ],
];
