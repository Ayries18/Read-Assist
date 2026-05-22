<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AudioBuku extends Model
{
    use HasFactory;
    protected $table = 'audio_buku';

    protected $fillable = [
        'user_id',
        'admin_id',
        'judul',
        'cover',
        'penulis',
        'kategori',
        'deskripsi',
        'file_buku',
        'file_audio',
        'audio_status',
        'qr_token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
