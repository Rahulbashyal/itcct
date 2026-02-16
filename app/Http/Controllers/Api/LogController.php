<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        // Only SuperAdmin or President can view logs
        if (!in_array($request->user()->role, ['SuperAdmin', 'President'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return SystemLog::with('user:id,name,role')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
    }
}
