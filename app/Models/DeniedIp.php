<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeniedIp extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'banned_until',
        'offense_count',
    ];

    protected $casts = [
        'banned_until' => 'datetime',
    ];
}
