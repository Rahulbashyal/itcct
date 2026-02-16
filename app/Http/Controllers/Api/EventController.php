<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Helpers\WebPUtility;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cacheKey = 'events_index_' . md5(json_encode($request->all()));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function() use ($request) {
            $query = Event::query();

            if ($request->has('published_only') && $request->published_only == 'true') {
                $query->where('is_published', true);
            }
            
            if ($request->has('upcoming') && $request->upcoming == 'true') {
                 $query->where('event_date', '>=', now());
            }

            return $query->orderBy('event_date', 'asc')->paginate(10);
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->user()->isSuperAdmin() && !$request->user()->hasRole('Admin')) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:event_date',
            'location' => 'nullable|string',
            'type' => 'required|string',
            'registration_link' => 'nullable|url',
            'image_file' => 'nullable|image|max:2048',
            'is_published' => 'boolean'
        ]);

        $event = new Event($validated);
        $event->slug = Str::slug($validated['title']) . '-' . Str::random(6);

        if ($request->hasFile('image_file')) {
            $path = WebPUtility::compress($request->file('image_file'), 'event-images');
            $event->image = '/storage/' . $path;
        }

        $event->save();

        return response()->json(['message' => 'Event created successfully', 'event' => $event], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Event::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()->isSuperAdmin() && !$request->user()->hasRole('Admin')) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'event_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after_or_equal:event_date',
            'location' => 'nullable|string',
            'type' => 'sometimes|string',
            'registration_link' => 'nullable|url',
            'is_published' => 'boolean',
            'image_file' => 'nullable|image|max:2048',
        ]);

        $event->fill($validated);
        
         if (isset($validated['title'])) {
             $event->slug = Str::slug($validated['title']) . '-' . Str::random(6);
        }

        if ($request->hasFile('image_file')) {
            $path = WebPUtility::compress($request->file('image_file'), 'event-images');
            $event->image = '/storage/' . $path;
        }

        $event->save();

        return response()->json(['message' => 'Event updated successfully', 'event' => $event]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isSuperAdmin()) {
             return response()->json(['message' => 'Unauthorized. SuperAdmin only.'], 403);
        }

        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }
}
