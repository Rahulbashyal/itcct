<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\User;
use Illuminate\Database\Seeder;

class ElectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create an active election
        $election = Election::create([
            'title' => 'Executive Committee Election 2081',
            'description' => 'Selecting the new leadership for the Crimson College IT Club.',
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(5),
            'is_active' => true,
            'status' => 'live',
        ]);

        // 2. Create standard users for candidates (if not exists)
        $candidateUsers = [
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'role' => 'Member'],
            ['name' => 'Bob Williams', 'email' => 'bob@example.com', 'role' => 'Member'],
            ['name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'role' => 'Member'],
        ];

        foreach ($candidateUsers as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => bcrypt('password'),
                    'role' => $u['role'],
                    'symbol_number' => rand(100000, 999999),
                    'is_hidden' => false,
                ]
            );

            // 3. Create Candidate Profile
            Candidate::create([
                'user_id' => $user->id,
                'election_id' => $election->id,
                'position' => 'President',
                'vision_statement' => "I promise to bring more hackathons and workshops to our college. Vote for " . $user->name . "!",
                'votes_count' => rand(0, 50),
            ]);
        }
    }
}
