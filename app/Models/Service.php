<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'duration_minutes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'float',
        'duration_minutes' => 'integer',
    ];
}