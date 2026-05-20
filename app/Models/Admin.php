<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;

#[Fillable(['nama', 'email', 'password'])]
#[Hidden(['password'])]
class Admin extends Model
{
    protected $table = 'admin';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function audioBuku()
    {
        return $this->hasMany(AudioBuku::class, 'admin_id');
    }
}
