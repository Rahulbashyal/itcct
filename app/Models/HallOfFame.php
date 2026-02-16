<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HallOfFame extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'role',
        'batch',
        'achievements',
        'contribution_summary',
        'image',
        'external_links',
        'order_weight',
    ];

    protected $casts = [
        'external_links' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
