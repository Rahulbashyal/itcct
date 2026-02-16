<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Helpers\WebPUtility;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cacheKey = 'news_index_' . md5(json_encode($request->all()));
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function() use ($request) {
            $query = News::with('author');

            if ($request->has('published_only') && $request->published_only == 'true') {
                $query->where('is_published', true);
            }

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            }

            return $query->latest()->paginate(10);
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Protected by auth:sanctum via route group
        if (!$request->user()->isSuperAdmin() && !$request->user()->hasRole('Admin')) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'nullable|string',
            'category' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048', // Upload handling
            'is_published' => 'boolean'
        ]);

        $news = new News($validated);
        $news->slug = Str::slug($validated['title']) . '-' . Str::random(6);
        $news->author_id = $request->user()->id;

        if ($request->hasFile('image_file')) {
            $path = WebPUtility::compress($request->file('image_file'), 'news-images');
            $news->image = '/storage/' . $path;
        }

        $news->save();

        return response()->json(['message' => 'News created successfully', 'news' => $news], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return News::with('author')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()->isSuperAdmin() && !$request->user()->hasRole('Admin')) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $news = News::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'summary' => 'nullable|string',
            'category' => 'nullable|string',
            'is_published' => 'boolean',
            'image_file' => 'nullable|image|max:2048',
        ]);

        $news->fill($validated);
        
        if (isset($validated['title'])) {
             $news->slug = Str::slug($validated['title']) . '-' . Str::random(6);
        }

        if ($request->hasFile('image_file')) {
            // Delete old?
            // if ($news->image) Storage::disk('public')->delete(str_replace('/storage/', '', $news->image));
            
            $path = WebPUtility::compress($request->file('image_file'), 'news-images');
            $news->image = '/storage/' . $path;
        }

        $news->save();

        return response()->json(['message' => 'News updated successfully', 'news' => $news]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isSuperAdmin()) {
             return response()->json(['message' => 'Unauthorized. SuperAdmin only.'], 403);
        }

        $news = News::findOrFail($id);
        $news->delete();

        return response()->json(['message' => 'News deleted successfully']);
    }
}
