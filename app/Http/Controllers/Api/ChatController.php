<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $channel = $request->query('channel', 'general');
        $user = Auth::user();

        // Role-based channel access
        if ($channel === 'admins-only' && !$user->hasRole('President')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($channel === 'dev-team' && !$user->hasRole('SuperAdmin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // DM Privacy Check
        if (str_starts_with($channel, 'dm:')) {
            $ids = explode(':', $channel);
            if (!in_array($user->id, [$ids[1] ?? 0, $ids[2] ?? 0])) {
                return response()->json(['error' => 'DM is private'], 403);
            }
        }

        // Group Privacy Check
        if (str_starts_with($channel, 'group:')) {
            $groupId = str_replace('group:', '', $channel);
            if (!$user->chatGroups()->where('chat_groups.id', $groupId)->exists()) {
                return response()->json(['error' => 'Not a group member'], 403);
            }
        }

        return Message::with('user:id,name,role')
            ->where('channel', $channel)
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get()
            ->reverse()
            ->values();
    }

    public function channels(Request $request)
    {
        $user = Auth::user();
        $channels = [
            ['id' => 'general', 'name' => 'general', 'icon' => '🌍', 'type' => 'public'],
            ['id' => 'announcements', 'name' => 'announcements', 'icon' => '📢', 'type' => 'public'],
        ];

        if ($user->hasRole('President') || $user->hasRole('Secretary') || $user->hasRole('Treasurer') || $user->hasRole('SuperAdmin')) {
            $channels[] = ['id' => 'core-team', 'name' => 'core-team', 'icon' => '🛡️', 'type' => 'protected'];
        }

        if ($user->hasRole('SuperAdmin')) {
            $channels[] = ['id' => 'dev-terminal', 'name' => 'dev-terminal', 'icon' => '💻', 'type' => 'private'];
        }

        // Add user-joined groups
        $groups = $user->chatGroups()->get()->map(function($group) {
            return [
                'id' => 'group:' . $group->id,
                'name' => $group->name,
                'icon' => $group->icon ?: '👥',
                'type' => 'group',
                'description' => $group->description
            ];
        });

        return response()->json(array_merge($channels, $groups->toArray()));
    }

    public function initiateCall(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'type' => 'required|in:audio,video',
            'channel_id' => 'required|string'
        ]);

        broadcast(new \App\Events\ChatCallSent(
            Auth::user()->only(['id', 'name']),
            $request->to_user_id,
            $request->type,
            $request->channel_id
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    public function callResponse(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'status' => 'required|in:accepted,rejected'
        ]);

        broadcast(new \App\Events\ChatCallResponse(
            $request->status,
            $request->to_user_id
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    public function createGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'member_ids' => 'required|array',
            'icon' => 'nullable|string'
        ]);

        $group = \App\Models\ChatGroup::create([
            'name' => $request->name,
            'icon' => $request->icon ?? '👥',
            'creator_id' => Auth::id()
        ]);

        // Add creator and selected members
        $memberIds = array_unique(array_merge([Auth::id()], $request->member_ids));
        $group->members()->attach($memberIds);

        return response()->json([
            'success' => true,
            'group' => [
                'id' => 'group:' . $group->id,
                'name' => $group->name,
                'icon' => $group->icon,
                'type' => 'group'
            ]
        ]);
    }

    public function members()
    {
        $users = \App\Models\User::select('id', 'name', 'role', 'profile_image')
            ->where('is_hidden', false)
            ->get();

        $grouped = $users->groupBy('role');

        return response()->json($grouped);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $message = Message::findOrFail($id);

        // Power check: Owner or President/SuperAdmin can delete
        if ($message->user_id === $user->id || $user->hasRole('President') || $user->hasRole('SuperAdmin')) {
            $message->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string|max:1000',
            'channel' => 'required|string',
            'file' => 'nullable|file|max:10240' // 10MB limit
        ]);

        $channel = $request->channel;
        $user = Auth::user();
        
        // Strict channel access control
        if ($channel === 'dev-team' && !$user->hasRole('SuperAdmin')) { 
             return response()->json(['error' => 'Forbidden'], 403);
        }

        // DM Privacy Check
        if (str_starts_with($channel, 'dm:')) {
            $ids = explode(':', $channel);
            if (!in_array($user->id, [$ids[1] ?? 0, $ids[2] ?? 0])) {
                return response()->json(['error' => 'DM is private'], 403);
            }
        }

        // Group Privacy Check
        if (str_starts_with($channel, 'group:')) {
            $groupId = str_replace('group:', '', $channel);
            if (!$user->chatGroups()->where('chat_groups.id', $groupId)->exists()) {
                return response()->json(['error' => 'Not a group member'], 403);
            }
        }

        $filePath = null;
        $fileType = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('chat_files', 'public');
            $fileType = $file->getMimeType();
        }

        $message = Message::create([
            'user_id' => Auth::id(),
            'channel' => $channel,
            'content' => $request->content ?? '',
            'file_path' => $filePath,
            'file_type' => $fileType
        ]);

        $message->load('user:id,name,role');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message);
    }
}
