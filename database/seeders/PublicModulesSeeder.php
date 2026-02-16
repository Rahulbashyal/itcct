<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\HallOfFame;
use Illuminate\Database\Seeder;

class PublicModulesSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing Hall of Fame entries
        HallOfFame::truncate();

        // Seed Hall of Fame
        $alumni = [
            [
                'name' => 'Bishal Sharma Parajuli',
                'role' => 'Fifth President (2025–Present)',
                'batch' => '2082',
                'achievements' => 'Current President leading the club towards national-level excellence and technical innovation.',
                'contribution_summary' => 'Focusing on sustainable growth and industry partnerships.',
                'order_weight' => 50
            ],
            [
                'name' => 'Rahul Bashyal',
                'role' => 'Fourth President (2024–2025)',
                'batch' => '2081',
                'achievements' => 'Spearheaded the digital transformation of the club and developed the IT Ecosystem platform.',
                'contribution_summary' => 'Modernized club infrastructure and digital presence.',
                'order_weight' => 40
            ],
            [
                'name' => 'Nabin Khanal',
                'role' => 'Third President (2023–2024)',
                'batch' => '2080',
                'achievements' => 'Enhanced club outreach and established strong community engagement initiatives.',
                'contribution_summary' => 'Built strong internal team culture and expanded member base.',
                'order_weight' => 30
            ],
            [
                'name' => 'Amrit Lamshal',
                'role' => 'Second President (2022–2023)',
                'batch' => '2079',
                'achievements' => 'Focused on internal growth, skill development, and organizing seminal tech workshops.',
                'contribution_summary' => 'Pioneered several technical training programs for freshmen.',
                'order_weight' => 20
            ],
            [
                'name' => 'Ashok Kc',
                'role' => 'Founding President (2021–2022)',
                'batch' => '2078',
                'achievements' => 'Entrepreneur and CEO of Abssoft Company Pvt Ltd. Founded the club in 2021 with a vision to empower tech enthusiasts.',
                'contribution_summary' => 'Established the vision and foundational structure of the IT Club.',
                'order_weight' => 10
            ]
        ];

        foreach ($alumni as $person) {
            HallOfFame::updateOrCreate(['name' => $person['name']], $person);
        }

        // Seed Events
        $events = [
            [
                'title' => 'National IT Hackathon 2026',
                'slug' => 'national-it-hackathon-2026',
                'event_date' => now()->addMonths(2),
                'location' => 'Main Auditorium, CCT',
                'description' => 'A 48-hour challenge to build solutions for digital governance.',
                'registration_link' => 'https://example.com/register-hackathon'
            ],
            [
                'title' => 'Open Source Contribution Day',
                'slug' => 'open-source-contribution-day',
                'event_date' => now()->addDays(15),
                'location' => 'IT Lab 2',
                'description' => 'Learn how to contribute to major open-source projects with local mentors.',
                'registration_link' => 'https://example.com/register-os'
            ]
        ];

        foreach ($events as $event) {
            Event::updateOrCreate(['slug' => $event['slug']], $event);
        }

        // Seed News
        $admin = \App\Models\User::where('symbol_number', '000000')->first();
        if ($admin) {
            \App\Models\News::updateOrCreate(
                ['slug' => 'digital-ecosystem-launch'],
                [
                    'title' => 'Digital Ecosystem Launch',
                    'content' => 'The IT Club of Crimson College has officially launched its new digital governance platform. This system will handle everything from elections to financial transparency.',
                    'summary' => 'The IT Club transition to a fully digital governance platform is now complete.',
                    'author_id' => $admin->id,
                    'is_published' => true
                ]
            );

            \App\Models\News::updateOrCreate(
                ['slug' => 'national-hackathon-prep'],
                [
                    'title' => 'National Hackathon Preparations',
                    'content' => 'Teams have started drafting the problem statements for the upcoming National Hackathon 2026. Stay tuned for registration details.',
                    'summary' => 'Problem statements for the 2026 Hackathon are currently being finalized.',
                    'author_id' => $admin->id,
                    'is_published' => true
                ]
            );

            // Seed Transactions
            $transactions = [
                ['amount' => 50000, 'type' => 'income', 'category' => 'Sponsorship', 'description' => 'CAN Infotech Platinum Sponsorship', 'transaction_date' => now()->subDays(10)],
                ['amount' => 15000, 'type' => 'income', 'category' => 'Membership Fee', 'description' => 'Batch 2081 Membership Collection', 'transaction_date' => now()->subDays(5)],
                ['amount' => 12500, 'type' => 'expense', 'category' => 'Hardware', 'description' => 'High-Speed Networking Cables for Lab', 'transaction_date' => now()->subDays(3)],
                ['amount' => 6000, 'type' => 'expense', 'category' => 'Refreshments', 'description' => 'Orientation Program Snacks', 'transaction_date' => now()->subDays(1)],
            ];

            foreach ($transactions as $tx) {
                \App\Models\Transaction::create($tx + ['user_id' => $admin->id]);
            }
        }
    }
}
