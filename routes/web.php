<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [App\Http\Controllers\PublicController::class, 'index'])->name('home');

// Public Pages
Route::get('/hall-of-fame', [App\Http\Controllers\PublicController::class, 'hallOfFame'])->name('hall-of-fame');
Route::get('/events', [App\Http\Controllers\PublicController::class, 'events'])->name('events');
Route::get('/projects', [App\Http\Controllers\PublicController::class, 'projects'])->name('projects');
Route::get('/news', [App\Http\Controllers\PublicController::class, 'news'])->name('news');
Route::get('/transparency', [App\Http\Controllers\PublicController::class, 'transparency'])->name('transparency');
Route::get('/election-portal', [App\Http\Controllers\PublicController::class, 'electionPortal'])->name('election-portal');
Route::get('/about', [App\Http\Controllers\PublicController::class, 'about'])->name('about');
Route::get('/contact', [App\Http\Controllers\PublicController::class, 'contact'])->name('contact');

Route::middleware('guest')->group(function () {
    Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'show'])->name('login');
    Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

    // Email Verification Notice (must be inside auth, not guest)
    Route::get('verify-email', function () {
        return Inertia::render('Auth/VerifyEmail');
    })->name('verification.notice');
    
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // WebDE Desktop Environment (verification handled at API level)
    Route::get('/desktop', function () {
        return Inertia::render('Desktop');
    })->name('desktop');

    // Desktop API Routes (Moved to api.php)
    // Route::prefix('api/v1')->group(...);

    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    Route::get('/vault', [App\Http\Controllers\VaultController::class, 'index'])->name('vault.index');
    Route::post('/vault', [App\Http\Controllers\VaultController::class, 'store'])->name('vault.store');
    Route::get('/vault/{vaultFile}/download', [App\Http\Controllers\VaultController::class, 'download'])->name('vault.download');

    Route::get('/learning', [App\Http\Controllers\LearningController::class, 'index'])->name('learning.index');

    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [App\Http\Controllers\ChatController::class, 'store'])->name('chat.store');

    Route::get('/elections', [App\Http\Controllers\ElectionController::class, 'index'])->name('elections.index');
    Route::get('/elections/{election}', [App\Http\Controllers\ElectionController::class, 'show'])->name('elections.show');
    Route::post('/elections/{election}/vote', [App\Http\Controllers\ElectionController::class, 'vote'])->name('elections.vote');

    // Admin Governance Center
    Route::middleware(['role:SuperAdmin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/role', [App\Http\Controllers\Admin\AdminUserController::class, 'updateRole'])->name('users.update-role');
        Route::get('/elections', [App\Http\Controllers\Admin\AdminElectionController::class, 'index'])->name('elections.index');
        Route::post('/elections', [App\Http\Controllers\Admin\AdminElectionController::class, 'store'])->name('elections.store');
        Route::patch('/elections/{election}/toggle', [App\Http\Controllers\Admin\AdminElectionController::class, 'toggleActive'])->name('elections.toggle');

        Route::get('/finance', [App\Http\Controllers\Admin\AdminFinanceController::class, 'index'])->name('finance.index');
        Route::post('/finance', [App\Http\Controllers\Admin\AdminFinanceController::class, 'store'])->name('finance.store');

        Route::get('/news', [App\Http\Controllers\Admin\AdminNewsController::class, 'index'])->name('news.index');
        Route::post('/news', [App\Http\Controllers\Admin\AdminNewsController::class, 'store'])->name('news.store');
        Route::delete('/news/{news}', [App\Http\Controllers\Admin\AdminNewsController::class, 'destroy'])->name('news.destroy');

        Route::get('/events', [App\Http\Controllers\Admin\AdminEventController::class, 'index'])->name('events.index');
        Route::post('/events', [App\Http\Controllers\Admin\AdminEventController::class, 'store'])->name('events.store');
        Route::delete('/events/{event}', [App\Http\Controllers\Admin\AdminEventController::class, 'destroy'])->name('events.destroy');
    });
});
