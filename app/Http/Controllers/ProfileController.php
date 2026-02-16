<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return Inertia::render('Profile/Edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $request->user()->id,
            'batch' => 'nullable|string|max:20',
        ]);

        $request->user()->fill($request->only('name', 'email', 'batch'));

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        \App\Helpers\Logger::log('profile_update', 'User updated their personal information.');

        return Redirect::route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}
