<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'link',
        'type',
        'category',
        'description',
        'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];
}
