<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class AdminNewsController extends Controller
{
    public function index()
    {
        $news = News::with('author:id,name')->latest()->get();

        return Inertia::render('Admin/News/Index', [
            'news' => $news
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'required|string|max:500',
            'image_url' => 'nullable|url',
        ]);

        News::create($validated + [
            'author_id' => auth()->id(),
            'is_published' => true,
        ]);

        \App\Helpers\Logger::log('news_post', 'Published news: ' . $validated['title']);

        return back()->with('success', 'News broadcasted successfully.');
    }

    public function destroy(News $news)
    {
        $news->delete();
        return back()->with('success', 'Article removed.');
    }
}
