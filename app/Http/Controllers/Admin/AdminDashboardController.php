<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Election;
use App\Models\ActivityLog;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_elections' => Election::where('is_active', true)->count(),
            'recent_logs' => ActivityLog::with('user:id,name')->latest()->limit(5)->get(),
            'system_status' => 'Healthy',
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats
        ]);
    }
}
