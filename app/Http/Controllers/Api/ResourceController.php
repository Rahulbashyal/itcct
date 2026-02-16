<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Resource::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        return $query->latest()->get();
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
            'type' => 'required|string', // Link, Video, File
            'category' => 'required|string',
            'description' => 'nullable|string',
            'link' => 'nullable|url',
            'file' => 'nullable|file|max:10240',
            'is_published' => 'boolean'
        ]);

        if (empty($validated['link']) && !$request->hasFile('file')) {
             return response()->json(['message' => 'Either link or file is required.'], 422);
        }

        $link = $validated['link'] ?? null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('resources', 'public');
            $link = '/storage/' . $path;
        }

        $resource = Resource::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'link' => $link,
            'is_published' => $validated['is_published'] ?? true
        ]);

        return response()->json(['message' => 'Resource created successfully', 'resource' => $resource], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isSuperAdmin()) {
             return response()->json(['message' => 'Unauthorized. SuperAdmin only.'], 403);
        }

        $resource = Resource::findOrFail($id);
        $resource->delete();

        return response()->json(['message' => 'Resource deleted successfully']);
    }
}
