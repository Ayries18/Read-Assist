<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListeningProgress extends Model
{
    protected $fillable = ['user_id', 'audio_buku_id', 'sentence_index', 'completed'];

    protected $casts = [
        'completed' => 'boolean',
        'sentence_index' => 'integer',
    ];
}
