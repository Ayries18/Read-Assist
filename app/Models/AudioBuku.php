<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AudioBuku extends Model
{
    protected $table = 'audio_buku';

    protected $fillable = [
        'user_id',
        'admin_id',
        'judul',
        'penulis',
        'kategori',
        'deskripsi',
        'file_buku',
        'file_audio',
        'qr_token',
    ];
}
