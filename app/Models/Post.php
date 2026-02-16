<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'content',
        'image',
        'is_announcement',
        'visibility'
    ];

    protected $casts = [
        'is_announcement' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Check if the authenticated user liked this post
     */
    public function getIsLikedAttribute()
    {
        $userId = auth()->id();
        if (!$userId) return false;
        return $this->likes()->where('user_id', $userId)->exists();
    }
}
