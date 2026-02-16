<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // User Info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // WebDE Desktop API
    Route::get('/desktop/icons', [App\Http\Controllers\Api\DesktopController::class, 'icons']);
    
    // Elections
    Route::get('/elections', [App\Http\Controllers\Api\ElectionController::class, 'index']);
    Route::get('/elections/{id}', [App\Http\Controllers\Api\ElectionController::class, 'show']);
    Route::post('/elections/{id}/vote', [App\Http\Controllers\Api\ElectionController::class, 'vote']);
    
    // Hall of Fame
    Route::get('/hall-of-fame', [App\Http\Controllers\Api\HallOfFameController::class, 'index']);

    // Transparency Portal
    Route::get('/transparency/data', [App\Http\Controllers\Api\TransparencyController::class, 'index']);
    Route::post('/transparency/data', [App\Http\Controllers\Api\TransparencyController::class, 'store']);

    // Content Modules
    Route::apiResource('news', App\Http\Controllers\Api\NewsController::class);
    Route::apiResource('events', App\Http\Controllers\Api\EventController::class);
    Route::apiResource('documents', App\Http\Controllers\Api\DocumentController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('resources', App\Http\Controllers\Api\ResourceController::class)->only(['index', 'store', 'destroy']);

    // Admin User Management
    Route::get('/admin/users', [App\Http\Controllers\Api\AdminUserController::class, 'index']);
    Route::post('/admin/users/{id}/role', [App\Http\Controllers\Api\AdminUserController::class, 'updateRole']);
    Route::post('/admin/users/{id}/toggle-status', [App\Http\Controllers\Api\AdminUserController::class, 'toggleStatus']);

    // Code Execution
    Route::post('/code/execute', [App\Http\Controllers\Api\CodeExecutionController::class, 'execute']);

    // IDE Management
    Route::get('/ide/workspace', [App\Http\Controllers\Api\IDEController::class, 'index']);
    Route::post('/ide/files/{id}/save', [App\Http\Controllers\Api\IDEController::class, 'saveFile']);
    Route::post('/ide/terminal/run', [App\Http\Controllers\Api\IDEController::class, 'runTerminalCommand']);

    // Chat
    Route::get('/chat/channels', [App\Http\Controllers\Api\ChatController::class, 'channels']);
    Route::get('/chat/history', [App\Http\Controllers\Api\ChatController::class, 'index']);
    Route::get('/chat/members', [App\Http\Controllers\Api\ChatController::class, 'members']);
    Route::post('/chat/send', [App\Http\Controllers\Api\ChatController::class, 'store']);
    Route::post('/chat/groups', [App\Http\Controllers\Api\ChatController::class, 'createGroup']);
    Route::post('/chat/call', [App\Http\Controllers\Api\ChatController::class, 'initiateCall']);
    Route::post('/chat/call-response', [App\Http\Controllers\Api\ChatController::class, 'callResponse']);
    Route::delete('/chat/{id}', [App\Http\Controllers\Api\ChatController::class, 'destroy']);

    // System Logs
    Route::get('/system-logs', [App\Http\Controllers\Api\LogController::class, 'index']);

    // Nexus Social Features
    Route::get('/nexus/feed', [App\Http\Controllers\Api\NexusController::class, 'feed']);
    Route::post('/nexus/posts', [App\Http\Controllers\Api\NexusController::class, 'storePost']);
    Route::post('/nexus/like', [App\Http\Controllers\Api\NexusController::class, 'toggleLike']);
    Route::post('/nexus/comments', [App\Http\Controllers\Api\NexusController::class, 'storeComment']);
    Route::get('/nexus/notifications', [App\Http\Controllers\Api\NexusController::class, 'notifications']);
    Route::post('/nexus/notifications/{id}/read', [App\Http\Controllers\Api\NexusController::class, 'markNotificationRead']);

    // Mobile Auth (Protected)
    Route::post('/auth/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/auth/user', [App\Http\Controllers\Api\AuthController::class, 'user']);
});

// Mobile Auth (Public)
Route::post('/v1/auth/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
