<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Workspace;
use App\Models\WorkspaceFile;
use App\Services\DockerService;

class IDEController extends Controller
{
    protected $docker;

    public function __construct(DockerService $docker)
    {
        $this->docker = $docker;
    }

    public function index(Request $request)
    {
        $workspace = Workspace::where('user_id', $request->user()->id)->first();
        
        if (!$workspace) {
            // Create default workspace if not exists
            $workspace = Workspace::create([
                'user_id' => $request->user()->id,
                'name' => 'Main Workspace',
                'project_path' => 'workspaces/' . $request->user()->id . '/main',
                'status' => 'active'
            ]);
            
            // Ensure directory exists
            $fullDir = storage_path('app/' . $workspace->project_path);
            if (!file_exists($fullDir)) {
                mkdir($fullDir, 0755, true);
            }
            
            // Initial files
            $files = [
                ['name' => 'main.py', 'lang' => 'python', 'content' => 'print("Pyton 3 online")'],
                ['name' => 'index.html', 'lang' => 'html', 'content' => '<!DOCTYPE html>\n<html>\n<head><title>Nexus Web</title></head>\n<body>\n  <h1>Nexus Engine Online</h1>\n  <p>Run a local server to view this page.</p>\n</body>\n</html>'],
                ['name' => 'Main.java', 'lang' => 'java', 'content' => 'public class Main {\n    public static void main(String[] args) {\n        System.out.println("Java 17 Online");\n    }\n}'],
                ['name' => 'script.js', 'lang' => 'javascript', 'content' => 'console.log("Nexus Script Loaded");'],
            ];

            foreach ($files as $fData) {
                $file = WorkspaceFile::create([
                    'workspace_id' => $workspace->id,
                    'name' => $fData['name'],
                    'type' => 'file',
                    'language' => $fData['lang'],
                    'content' => $fData['content'],
                    'path' => $fData['name']
                ]);
                $this->syncToDisk($file);
            }
        }

        // Ensure container is running
        $this->docker->startContainer($workspace->id, $workspace->project_path);

        return response()->json([
            'workspace' => $workspace,
            'files' => WorkspaceFile::where('workspace_id', $workspace->id)
                ->with('children')
                ->whereNull('parent_id')
                ->get()
        ]);
    }

    public function saveFile(Request $request, $fileId)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $file = WorkspaceFile::findOrFail($fileId);
        $file->update(['content' => $request->content]);

        // Sync to disk (for later Docker usage)
        $this->syncToDisk($file);

        return response()->json(['message' => 'File saved successfully']);
    }

    public function runTerminalCommand(Request $request)
    {
        $request->validate(['command' => 'required|string']);

        $workspace = Workspace::where('user_id', $request->user()->id)->first();
        if (!$workspace) {
            return response()->json(['output' => 'Error: Workspace not found'], 404);
        }

        // Start container if not running
        if (!$this->docker->startContainer($workspace->id, $workspace->project_path)) {
            return response()->json(['output' => 'Error: Failed to spin up container'], 500);
        }

        $result = $this->docker->execute($workspace->id, $request->command);

        return response()->json([
            'output' => $result['output'],
            'error' => $result['error'],
            'exit_code' => $result['exit_code']
        ]);
    }

    private function syncToDisk(WorkspaceFile $file)
    {
        $workspace = $file->workspace;
        $fullPath = storage_path('app/' . $workspace->project_path . '/' . $file->path);
        
        $dir = dirname($fullPath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($fullPath, $file->content);
    }
}
