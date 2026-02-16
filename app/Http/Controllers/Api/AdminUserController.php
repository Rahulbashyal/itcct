<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Return users with basic info
        return User::select('id', 'name', 'email', 'role', 'is_hidden', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Update the user role.
     */
    public function updateRole(Request $request, $id)
    {
        if (!$request->user()->isSuperAdmin()) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'role' => 'required|string|in:User,Admin,SuperAdmin,Alumni'
        ]);

        // Prevent changing own role (safety)
        if ($user->id === $request->user()->id) {
             return response()->json(['message' => 'Cannot change your own role.'], 400);
        }

        $user->role = $validated['role'];
        $user->save();

        return response()->json(['message' => 'Role updated successfully', 'user' => $user]);
    }

    /**
     * Toggle user status (Ban/Unban).
     */
    public function toggleStatus(Request $request, $id)
    {
        if (!$request->user()->isSuperAdmin()) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
             return response()->json(['message' => 'Cannot ban yourself.'], 400);
        }

        $user->is_hidden = !$user->is_hidden;
        $user->save();

        return response()->json(['message' => 'User status updated', 'is_hidden' => $user->is_hidden]);
    }
}
