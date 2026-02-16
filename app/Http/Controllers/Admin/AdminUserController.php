<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name', 'asc')->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|string|in:SuperAdmin,President,Secretary,Treasurer,Member',
        ]);

        $user->update(['role' => $request->role]);

        \App\Helpers\Logger::log('role_update', 'Updated role for ' . $user->name . ' to ' . $request->role);

        return back()->with('success', 'User role updated successfully.');
    }
}
