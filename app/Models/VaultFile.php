<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VaultFile extends Model
{
    protected $fillable = [
        'name',
        'path',
        'mime_type',
        'size',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
