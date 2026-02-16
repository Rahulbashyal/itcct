<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function show()
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'symbol_number' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Bypass global scopes to find hidden users (SuperAdmins) during login
        $user = \App\Models\User::withoutGlobalScopes()
            ->where('symbol_number', $credentials['symbol_number'])
            ->first();

        \Illuminate\Support\Facades\Log::info('Login Attempt', [
            'symbol' => $credentials['symbol_number'],
            'found' => $user ? 'Yes' : 'No',
            'hidden' => $user ? $user->is_hidden : 'N/A'
        ]);

        if ($user && \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            Auth::login($user);
            
            $request->session()->regenerate();

            \App\Helpers\Logger::log('login', 'User logged in via symbol number.');

            return redirect('/desktop')->with('success', 'Logged in successfully!');
        }

        \Illuminate\Support\Facades\Log::warning('Login Failed - Credentials Mismatch', [
            'symbol' => $credentials['symbol_number']
        ]);

        return back()->with('error', 'The provided credentials do not match our records.')->withErrors([
            'symbol_number' => 'The provided credentials do not match our records.',
        ])->onlyInput('symbol_number');
    }

    public function logout(Request $request)
    {
        \App\Helpers\Logger::log('logout', 'User logged out.');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
