<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\HallOfFame;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicController extends Controller
{
    public function index()
    {
        $featuredProjects = Project::where('is_featured', true)->limit(3)->get();
        $upcomingEvents = Event::where('is_published', true)->where('event_date', '>=', now())->limit(3)->get();
        
        // Get stats for landing page
        $stats = [
            'members' => \App\Models\User::count(),
            'events' => Event::count(),
            'projects' => Project::count(),
            'years_active' => now()->year - 2021,
        ];
        
        // Features list
        $features = [
            [
                'title' => 'Digital Governance',
                'description' => 'Transparent elections and democratic decision-making processes',
                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            ],
            [
                'title' => 'Technical Events',
                'description' => 'Workshops, hackathons, and tech talks from industry experts',
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            ],
            [
                'title' => 'Learning Hub',
                'description' => 'Access tutorials, resources, and hands-on coding exercises',
                'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            ],
            [
                'title' => 'Innovation & Projects',
                'description' => 'Build real-world projects and contribute to open source',
                'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
            ],
        ];
        
        return Inertia::render('Welcome', [
            'stats' => $stats,
            'features' => $features,
            'featuredProjects' => $featuredProjects,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
    public function hallOfFame()
    {
        $alumni = HallOfFame::orderBy('order_weight', 'desc')
            ->orderBy('batch', 'desc')
            ->get();

        return Inertia::render('Public/HallOfFame', [
            'alumni' => $alumni
        ]);
    }

    public function events()
    {
        $upcomingEvents = Event::where('is_published', true)
            ->where('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->get();

        $pastEvents = Event::where('is_published', true)
            ->where('event_date', '<', now())
            ->orderBy('event_date', 'desc')
            ->get();

        return Inertia::render('Public/Events', [
            'upcomingEvents' => $upcomingEvents,
            'pastEvents' => $pastEvents,
        ]);
    }

    public function news()
    {
        $news = \App\Models\News::with('author')
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Public/News', [
            'news' => $news
        ]);
    }

    public function transparency()
    {
        $transactions = \App\Models\Transaction::orderBy('transaction_date', 'desc')->limit(20)->get();
        $totalIncome = \App\Models\Transaction::where('type', 'income')->sum('amount');
        $totalExpense = \App\Models\Transaction::where('type', 'expense')->sum('amount');

        return Inertia::render('Public/Transparency', [
            'transactions' => $transactions,
            'summary' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'balance' => $totalIncome - $totalExpense
            ]
        ]);
    }

    public function projects()
    {
        $projects = Project::orderBy('created_at', 'desc')->get();
        return Inertia::render('Public/Projects', [
            'projects' => $projects
        ]);
    }

    public function electionPortal()
    {
        $activeElections = \App\Models\Election::where('status', 'live')->get();
        return Inertia::render('Public/ElectionPortal', [
            'elections' => $activeElections
        ]);
    }

    public function about()
    {
        return Inertia::render('Public/About');
    }

    public function contact()
    {
        return Inertia::render('Public/Contact');
    }
}
