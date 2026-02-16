<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('symbol_number', '000000')->first();

        $projects = [
            [
                'title' => 'Nexus Governance Portal',
                'description' => 'The ultimate digital backbone for club administration, featuring encrypted voting and real-time ledger tracking.',
                'technologies' => ['Laravel', 'Vue.js', 'Tailwind'],
                'is_featured' => true,
                'link' => '#',
            ],
            [
                'title' => 'CCT AI Assistant',
                'description' => 'A fine-tuned LLM designed to handle student inquiries about college schedules, notes, and events.',
                'technologies' => ['Python', 'FastAPI', 'React'],
                'is_featured' => true,
                'link' => '#',
            ],
            [
                'title' => 'Smart Attendee System',
                'description' => 'Biometric-based attendance tracker for large-scale seminars and workshops using IoT protocols.',
                'technologies' => ['ESP32', 'Firebase', 'Mobile App'],
                'is_featured' => true,
                'link' => '#',
            ]
        ];

        foreach ($projects as $project) {
            Project::create($project);
        }
    }
}
