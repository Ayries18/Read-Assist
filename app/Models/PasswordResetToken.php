<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $fillable = ['email', 'token', 'role'];

    public $timestamps = false;
}
