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

    protected $appends = [
        'slug',
        'unique_token',
        'audio_path',
        'qr_code',
    ];

    public function getSlugAttribute()
    {
        return $this->qr_token;
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['qr_token'] = $value;
    }

    public function getUniqueTokenAttribute()
    {
        return $this->qr_token;
    }

    public function setUniqueTokenAttribute($value)
    {
        $this->attributes['qr_token'] = $value;
    }

    public function getAudioPathAttribute()
    {
        return $this->file_audio;
    }

    public function setAudioPathAttribute($value)
    {
        $this->attributes['file_audio'] = $value;
    }

    public function getQrCodeAttribute()
    {
        return 'qr/qr-book-' . $this->id . '.svg';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
