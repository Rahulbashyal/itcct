<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class AdminElectionController extends Controller
{
    public function index()
    {
        $elections = Election::withCount('candidates')->latest()->get();

        return Inertia::render('Admin/Elections/Index', [
            'elections' => $elections
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $election = Election::create($validated + [
            'status' => 'draft',
            'is_active' => false
        ]);

        \App\Helpers\Logger::log('election_created', 'Created election: ' . $election->title);

        return back()->with('success', 'Election created successfully.');
    }

    public function toggleActive(Election $election)
    {
        $election->update([
            'is_active' => !$election->is_active,
            'status' => $election->is_active ? 'live' : 'draft'
        ]);

        \App\Helpers\Logger::log('election_toggled', 'Toggled active state for: ' . $election->title);

        return back()->with('success', 'Election status updated.');
    }
}
