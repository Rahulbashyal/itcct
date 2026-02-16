<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\HiddenRoleScope;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_hidden',
        'permissions',
        'order_weight',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'permissions' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new HiddenRoleScope);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role', 'slug');
    }
}
