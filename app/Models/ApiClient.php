<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'api_key',
        'ip_whitelist',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
