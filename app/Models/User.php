<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    public const ROLE_FINANCE = 'finance';
    public const ROLE_HR = 'hr';
    public const ROLE_ICT = 'ict';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'password' => 'hashed',
    ];

    public function isFinance(): bool
    {
        return $this->role === self::ROLE_FINANCE;
    }

    public function isHr(): bool
    {
        return $this->role === self::ROLE_HR;
    }

    public function isIct(): bool
    {
        return $this->role === self::ROLE_ICT;
    }
}
