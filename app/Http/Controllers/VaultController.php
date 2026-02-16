<?php

namespace App\Http\Controllers;

use App\Models\VaultFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class VaultController extends Controller
{
    public function index()
    {
        $files = VaultFile::with('user:id,name')->latest()->get();

        return Inertia::render('Vault/Index', [
            'files' => $files
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB limit
        ]);

        $file = $request->file('file');
        $path = $file->store('vault', 'local');

        VaultFile::create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'user_id' => $request->user()->id,
        ]);

        \App\Helpers\Logger::log('vault_upload', 'Uploaded file: ' . $file->getClientOriginalName());

        return back()->with('success', 'File uploaded to vault successfully.');
    }

    public function download(VaultFile $vaultFile)
    {
        return Storage::disk('local')->download($vaultFile->path, $vaultFile->name);
    }
}
