<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HallOfFame;
use Illuminate\Http\Request;

class HallOfFameController extends Controller
{
    public function index()
    {
        $members = \Illuminate\Support\Facades\Cache::remember('hall_of_fame_list', 3600, function () {
            return HallOfFame::orderBy('order_weight', 'desc')
                ->orderBy('batch', 'desc')
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'role' => $member->role,
                        'batch' => $member->batch,
                        'achievements' => $member->achievements,
                        'contribution' => $member->contribution_summary,
                        'image' => $member->image ? asset($member->image) : 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=random',
                        'links' => $member->external_links ?? [],
                    ];
                });
        });

        return response()->json(['members' => $members]);
    }
}
