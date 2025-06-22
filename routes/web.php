<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController,
    SimulationController,
    ModuleHandlerController,
    ConditionsController,
    EventController,
    ClockController,
    FeedbackController
};

// Public Routes
Route::view('/', 'welcome');
Route::view('/dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');

// Authenticated routes
Route::middleware('auth')->group(function () {

    // ---- Profile
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    // ---- Simulation Dashboard ----
    Route::prefix('simulation')->controller(SimulationController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('simulatie.dashboard');
        Route::get('/effects', 'getEffectsHtml')->name('simulatie.effects');
    });

    Route::controller(SimulationController::class)->group(function () {
        Route::patch('/slots/{slot}/approve', 'approve')->name('slots.approve');
        Route::patch('/slots/{slot}/remove-module', 'removeModule')->name('slots.removeModule');
        Route::post('/slots/link', 'koppelModule')->name('slots.link');
        Route::post('/slots/move', 'moveModule')->name('slots.move');
        Route::post('/effects/module/{moduleId}/{type}', 'updateEffect')->name('effects.update');
    });

    // ---- Modules
    Route::controller(ModuleHandlerController::class)->prefix('modules')->group(function () {
        Route::get('/', 'index')->name('module.index');
        Route::post('/', 'store')->name('modules.store');
        Route::put('/{id}', 'update')->name('modules.update');
        Route::delete('/{module}', 'destroy')->name('modules.destroy');
        Route::post('/bulk-destroy', 'bulkDestroy')->name('modules.bulkDestroy');
    });

    // ---- Events
    Route::prefix('events')->controller(EventController::class)->group(function () {
        Route::get('/', 'index')->name('events');
        Route::post('/set', 'setEvent')->name('events.set');
        Route::post('/reset', 'resetEvent')->name('events.reset');
        Route::get('/slot-events', 'getSlotEvents')->name('events.slot-events');
        Route::get('/{event}/effects', 'getEventEffects')->name('api.events.effects');
        Route::get('/{event}/effects', 'getEventEffectsApi');
    });

    // ---- Conditions
    Route::resource('conditions', ConditionsController::class)
        ->except(['show', 'create', 'edit'])
        ->names([
            'index' => 'conditions',
        ]);

    // ---- Feedback
    Route::controller(FeedbackController::class)->prefix('feedback')->group(function () {
        Route::post('/', 'store')->name('feedback.store');
        Route::get('/{feedback}/edit', 'edit')->name('feedback.edit');
        Route::patch('/{feedback}', 'update')->name('feedback.update');
        Route::delete('/{feedback}', 'destroy')->name('feedback.destroy');
    });

    // ---- Clock
    Route::controller(ClockController::class)->group(function () {
        Route::post('/save-clock', 'store')->name('clock.save');
        Route::get('/current', 'current')->name('clock.current');
    });
});

// Public API
Route::get('/api/modules/{module}/effects', function (\App\Models\Module $module) {
    return response()->json([
        'effects' => $module->effects->map(fn($e) => [
            'type' => $e->type,
            'value' => $e->value,
        ])
    ]);
});

// ========== Auth Scaffolding ==========
require __DIR__ . '/auth.php';
