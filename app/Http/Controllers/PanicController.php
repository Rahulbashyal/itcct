<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class PanicController extends Controller
{
    public function index(Request $request, $key)
    {
        if ($key !== config('app.panic_key')) {
            abort(404);
        }

        $logs = [];
        if (File::exists(storage_path('logs/laravel.log'))) {
            $logs = array_slice(explode("\n", File::get(storage_path('logs/laravel.log'))), -50);
        }

        return view('panic', [
            'key' => $key,
            'logs' => $logs,
            'system_status' => [
                'php_version' => PHP_VERSION,
                'disk_free' => round(disk_free_space("/") / (1024 * 1024 * 1024), 2) . " GB",
                'db_connection' => config('database.default'),
            ]
        ]);
    }

    public function reset(Request $request, $key)
    {
        if ($key !== config('app.panic_key')) {
            abort(403);
        }

        // Emergency Resets
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        
        return back()->with('message', 'System caches cleared successfully.');
    }
}
