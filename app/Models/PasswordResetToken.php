<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $fillable = ['email', 'token', 'role', 'created_at', 'updated_at'];

    protected $primaryKey = 'email';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    /**
     * TTL token reset password dalam menit.
     */
    public const TTL_MINUTES = 60;

    /**
     * Apakah token sudah kedaluwarsa.
     */
    public function isExpired(): bool
    {
        return $this->created_at === null
            || $this->created_at->addMinutes(self::TTL_MINUTES)->isPast();
    }
}
