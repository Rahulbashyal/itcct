<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminEventController extends Controller
{
    public function index()
    {
        $events = Event::latest()->get();

        return Inertia::render('Admin/Events/Index', [
            'events' => $events
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'location' => 'required|string|max:255',
            'image_url' => 'nullable|url',
        ]);

        Event::create($validated + ['is_published' => true]);

        \App\Helpers\Logger::log('event_created', 'Created event: ' . $validated['title']);

        return back()->with('success', 'Event scheduled successfully.');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return back()->with('success', 'Event cancelled.');
    }
}
