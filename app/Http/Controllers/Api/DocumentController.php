<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Document::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
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
            'type' => 'required|string',
            'year' => 'nullable|string',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,txt|max:10240', // 10MB max
            'is_published' => 'boolean'
        ]);

        $path = $request->file('file')->store('documents', 'public');

        $document = Document::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'year' => $validated['year'] ?? date('Y'),
            'description' => $validated['description'],
            'file_path' => '/storage/' . $path,
            'is_published' => $validated['is_published'] ?? true
        ]);

        return response()->json(['message' => 'Document uploaded successfully', 'document' => $document], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isSuperAdmin()) {
             return response()->json(['message' => 'Unauthorized. SuperAdmin only.'], 403);
        }

        $document = Document::findOrFail($id);
        
        // Optional: delete file from storage
        // Storage::disk('public')->delete(str_replace('/storage/', '', $document->file_path));

        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }
}
