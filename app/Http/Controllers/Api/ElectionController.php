<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\candidate;
use App\Models\Election;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ElectionController extends Controller
{
    /**
     * List all active/upcoming elections
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $elections = Election::query()
            ->where('status', '!=', 'archived')
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($election) use ($user) {
                // Check if user has already voted in this election
                $hasVoted = Vote::where('user_id', $user->id)
                    ->where('election_id', $election->id)
                    ->exists();
                
                return [
                    'id' => $election->id,
                    'title' => $election->title,
                    'status' => $this->calculateStatus($election),
                    'start_date' => $election->start_date,
                    'end_date' => $election->end_date,
                    'has_voted' => $hasVoted,
                    'can_vote' => $this->canVote($user, $election, $hasVoted),
                ];
            });

        return response()->json(['elections' => $elections]);
    }

    /**
     * Get single election details with candidates
     */
    public function show(Request $request, $id)
    {
        $election = Election::with(['candidates.user'])->findOrFail($id);
        $user = $request->user();

        // Check if user has voted
        $userVotes = Vote::where('user_id', $user->id)
            ->where('election_id', $election->id)
            ->get()
            ->pluck('candidate_id')
            ->toArray();

        $candidates = $election->candidates->map(function ($candidate) use ($user, $userVotes) {
            return [
                'id' => $candidate->id,
                'name' => $candidate->user->name,
                'position' => $candidate->position,
                'photo' => $candidate->user->profile_photo_url, // Assuming standard Laravel Jetstream/Fortify
                'bio' => $candidate->vision_statement,
                'manifesto_url' => $candidate->manifesto_path ? asset($candidate->manifesto_path) : null,
                'is_voted' => in_array($candidate->id, $userVotes),
            ];
        })->groupBy('position');

        return response()->json([
            'election' => [
                'id' => $election->id,
                'title' => $election->title,
                'description' => $election->description,
                'status' => $this->calculateStatus($election),
                'ends_at' => $election->end_date,
            ],
            'candidates' => $candidates,
            'user_status' => [
                'has_voted' => count($userVotes) > 0,
                'voted_candidates' => $userVotes
            ]
        ]);
    }

    /**
     * Cast a vote
     */
    public function vote(Request $request, $id)
    {
        $user = $request->user();
        $election = Election::findOrFail($id);

        // Security: President cannot vote
        if ($user->role === 'President') {
            return response()->json(['message' => 'Presidents are not eligible to vote.'], 403);
        }

        // Security: Election must be active
        if ($this->calculateStatus($election) !== 'active') {
            return response()->json(['message' => 'Election is not currently active.'], 403);
        }

        // Validate request
        $validated = $request->validate([
            'votes' => 'required|array', // ['position_name' => candidate_id]
            'votes.*' => 'required|exists:candidates,id'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['votes'] as $position => $candidateId) {
                // Check if already voted for this position
                $existingVote = Vote::where('user_id', $user->id)
                    ->where('election_id', $election->id)
                    ->where('position', $position)
                    ->exists();

                if ($existingVote) {
                    throw new \Exception("You have already voted for the position of $position.");
                }

                // Verify candidate belongs to this election and position
                $candidate = Candidate::where('id', $candidateId)
                    ->where('election_id', $election->id)
                    ->where('position', $position)
                    ->firstOrFail();

                // Record vote
                Vote::create([
                    'user_id' => $user->id,
                    'election_id' => $election->id,
                    'candidate_id' => $candidate->id,
                    'position' => $position,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                ]);

                // Increment cached count
                $candidate->increment('votes_count');
            }
            
            DB::commit();
            return response()->json(['message' => 'Vote cast successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // Helper: Calculate status based on dates
    private function calculateStatus($election)
    {
        $now = now();
        if ($now < $election->start_date) return 'upcoming';
        if ($now > $election->end_date) return 'completed';
        return $election->is_active ? 'active' : 'paused';
    }

    // Helper: Check if user can vote
    private function canVote($user, $election, $hasVoted)
    {
        if ($user->role === 'President') return false;
        if ($hasVoted) return false;
        if ($this->calculateStatus($election) !== 'active') return false;
        return true;
    }
}
