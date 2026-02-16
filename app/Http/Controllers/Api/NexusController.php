<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\News;
use App\Models\Event;
use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class NexusController extends Controller
{
    /**
     * Get combined feed for Nexus
     */
    public function feed(Request $request)
    {
        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);

        // Fetch Posts
        $posts = Post::with(['user', 'comments.user'])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(function ($post) {
                $post->type = 'post';
                $post->is_liked = $post->is_liked; // via custom attribute
                return $post;
            });

        // Fetch News
        $news = News::with('author')
            ->where('is_published', true)
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $item->type = 'news';
                return $item;
            });

        // Fetch Events
        $events = Event::where('is_published', true)
            ->where('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $item->type = 'event';
                return $item;
            });

        // Combine and sort
        $feed = $posts->concat($news)->concat($events)->sortByDesc(function ($item) {
            return $item->created_at ?: $item->event_date;
        })->values();

        return response()->json([
            'success' => true,
            'data' => $feed
        ]);
    }

    /**
     * Store a new post
     */
    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'image' => 'nullable|string',
            'is_announcement' => 'boolean'
        ]);

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'image' => $validated['image'] ?? null,
            'is_announcement' => $validated['is_announcement'] ?? false,
            'visibility' => 'public'
        ]);

        return response()->json([
            'success' => true,
            'data' => $post->load('user')
        ]);
    }

    /**
     * Like or unlike a content
     */
    public function toggleLike(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'type' => 'required|string|in:post,news,event'
        ]);

        $modelMap = [
            'post' => Post::class,
            'news' => News::class,
            'event' => Event::class
        ];

        $likeableType = $modelMap[$validated['type']];
        $likeableId = $validated['id'];

        $existingLike = Like::where('user_id', Auth::id())
            ->where('likeable_id', $likeableId)
            ->where('likeable_type', $likeableType)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            return response()->json(['success' => true, 'action' => 'unliked']);
        } else {
            Like::create([
                'user_id' => Auth::id(),
                'likeable_id' => $likeableId,
                'likeable_type' => $likeableType
            ]);

            // Notify owner
            $target = $likeableType::find($likeableId);
            $ownerId = $target->user_id ?? $target->author_id ?? null;
            if ($ownerId && $ownerId != Auth::id()) {
                \Illuminate\Support\Facades\DB::table('notifications')->insert([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'type' => 'App\Notifications\NexusActivity',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $ownerId,
                    'data' => json_encode([
                        'user_name' => Auth::user()->name,
                        'message' => "liked your {$validated['type']}",
                        'url' => "/nexus/feed"
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json(['success' => true, 'action' => 'liked']);
        }
    }

    /**
     * Add a comment
     */
    public function storeComment(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'type' => 'required|string|in:post,news,event',
            'content' => 'required|string'
        ]);

        $modelMap = [
            'post' => Post::class,
            'news' => News::class,
            'event' => Event::class
        ];

        $commentableType = $modelMap[$validated['type']];

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'commentable_id' => $validated['id'],
            'commentable_type' => $commentableType,
            'content' => $validated['content']
        ]);

        // Notify owner
        $target = $commentableType::find($validated['id']);
        $ownerId = $target->user_id ?? $target->author_id ?? null;
        if ($ownerId && $ownerId != Auth::id()) {
            \Illuminate\Support\Facades\DB::table('notifications')->insert([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\Notifications\NexusActivity',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $ownerId,
                'data' => json_encode([
                    'user_name' => Auth::user()->name,
                    'message' => "commented on your {$validated['type']}",
                    'url' => "/nexus/feed"
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $comment->load('user')
        ]);
    }

    /**
     * Get user notifications
     */
    public function notifications()
    {
        $notifications = Auth::user()->notifications()->latest()->limit(20)->get();
        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }
}
