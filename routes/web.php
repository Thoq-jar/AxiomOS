<?php

use App\Http\Controllers\AppStoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogsController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function() {
    return view('welcome');
})->name('home');
Route::get('/appstore', [AppStoreController::class, 'index'])->name('appstore');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::get('/logs', [LogsController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('logs');

Route::middleware(['auth'])->group(function() {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::prefix('api')->group(function() {
    Route::get('/system-stats', [DashboardController::class, 'getSystemStats']);
    Route::get('/weather', [DashboardController::class, 'getWeather']);

    Route::get('/apps', [AppStoreController::class, 'getApps']);
    Route::get('/apps/featured', [AppStoreController::class, 'getFeaturedApps']);
    Route::get('/apps/category/{category}', [AppStoreController::class, 'getAppsByCategory']);
    Route::get('/apps/search', [AppStoreController::class, 'searchApps']);
    Route::get('/apps/{id}', [AppStoreController::class, 'getApp']);
    Route::post('/apps/{id}/install', [AppStoreController::class, 'installApp']);
    Route::delete('/apps/{id}/uninstall', [AppStoreController::class, 'uninstallApp']);
    Route::get('/apps/{id}/status', [AppStoreController::class, 'getInstallationStatus']);

    Route::get('/logs', [LogsController::class, 'getLogs']);
    Route::delete('/logs', [LogsController::class, 'clearLogs']);
});

require __DIR__ . '/auth.php';
