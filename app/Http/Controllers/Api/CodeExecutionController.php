<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CodeExecutionController extends Controller
{
    /**
     * Execute code via Piston API
     */
    public function execute(Request $request)
    {
        $request->validate([
            'language' => 'required|string',
            'code' => 'required|string',
        ]);

        $languageMap = [
            'python' => ['language' => 'python', 'version' => '3.10.0'],
            'javascript' => ['language' => 'javascript', 'version' => '18.15.0'],
            'php' => ['language' => 'php', 'version' => '8.2.3'],
            'cpp' => ['language' => 'cpp', 'version' => '10.2.0'],
        ];

        $langConfig = $languageMap[$request->language] ?? null;

        if (!$langConfig) {
            return response()->json(['output' => 'Error: Unsupported language'], 400);
        }

        try {
            $response = Http::post('https://emkc.org/api/v2/piston/execute', [
                'language' => $langConfig['language'],
                'version' => $langConfig['version'],
                'files' => [
                    ['content' => $request->code]
                ],
            ]);

            $result = $response->json();

            if (isset($result['run'])) {
                $output = $result['run']['stdout'] . $result['run']['stderr'];
                return response()->json(['output' => $output ?: 'No output']);
            }

            return response()->json(['output' => 'Execution failed: ' . ($result['message'] ?? 'Unknown error')], 500);

        } catch (\Exception $e) {
            return response()->json(['output' => 'System Error: ' . $e->getMessage()], 500);
        }
    }
}
