<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\Candidate;
use App\Models\Vote;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ElectionController extends Controller
{
    public function index()
    {
        $elections = Election::where('is_active', true)
            ->orWhere('status', 'live')
            ->orderBy('start_date', 'asc')
            ->get();

        return Inertia::render('Election/Index', [
            'elections' => $elections
        ]);
    }

    public function show(Election $election)
    {
        $election->load(['candidates.user:id,name,profile_image']);
        
        // Group candidates by position
        $candidates = $election->candidates->groupBy('position');

        // Check if user has already voted
        $userVotes = Vote::where('user_id', auth()->id())
            ->where('election_id', $election->id)
            ->pluck('position')
            ->toArray();

        return Inertia::render('Election/Show', [
            'election' => $election,
            'candidatesByPosition' => $candidates,
            'userVotes' => $userVotes
        ]);
    }

    public function vote(Request $request, Election $election)
    {
        $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
        ]);

        $candidate = Candidate::findOrFail($request->candidate_id);

        // Check if election is active
        if (!$election->is_active || $election->status !== 'live') {
            return back()->with('error', 'This election is not currently open for voting.');
        }

        // Check date range
        if (now() < $election->start_date || now() > $election->end_date) {
            return back()->with('error', 'This election is outside the voting period.');
        }

        // Check if user already voted for this position
        $exists = Vote::where('user_id', auth()->id())
            ->where('election_id', $election->id)
            ->where('position', $candidate->position)
            ->exists();

        if ($exists) {
            return back()->with('error', 'You have already cast your vote for this position.');
        }

        Vote::create([
            'user_id' => auth()->id(),
            'candidate_id' => $candidate->id,
            'election_id' => $election->id,
            'position' => $candidate->position,
        ]);

        \App\Helpers\Logger::log('vote_cast', 'Voted for ' . $candidate->position . ' in ' . $election->title);

        return back()->with('success', 'Your vote has been recorded securely.');
    }
}
