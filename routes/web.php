<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\ModuleHandlerController;
use App\Http\Controllers\ConditionsController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FeedbackController;

// ========== Publiek ==========
Route::get('/', fn() => view('welcome'));
Route::get('/dashboard', fn() => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');

// ========== Authenticated gebruikers ==========
Route::middleware('auth')->group(function () {

    // Profielbeheer
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Simulatie Dashboard
    Route::get('/simdash', [SimulationController::class, 'index'])->name('simulatiedashboard');
    Route::post('/save-clock', [SimulationController::class, 'saveClock'])->name('save.clock');
    Route::patch('/slots/{slot}/approve', [SimulationController::class, 'approve'])->name('slots.approve');
    Route::patch('/slots/{slot}/remove-module', [SimulationController::class, 'removeModule'])->name('slots.removeModule');
    Route::post('/simulatie/koppel-module', [SimulationController::class, 'koppelModule']);
    Route::post('/simulatie/verplaats-module', [SimulationController::class, 'moveModule'])->name('slots.update');
    Route::post('/effects/module/{moduleId}/{type}', [SimulationController::class, 'updateEffect'])->name('effects.update');

    // Modulebeheer
    Route::get('/module', [ModuleHandlerController::class, 'index'])->name('module.index');
    Route::post('/modules', [ModuleHandlerController::class, 'store'])->name('modules.store');
    Route::put('/modules/{id}', [ModuleHandlerController::class, 'update'])->name('modules.update');
    Route::delete('/modules/{module}', [ModuleHandlerController::class, 'destroy'])->name('modules.destroy');
    Route::post('/modules/bulk-destroy', [ModuleHandlerController::class, 'bulkDestroy'])->name('modules.bulkDestroy');

    // Eventbeheer
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('events');
        Route::post('/set', [EventController::class, 'setEvent'])->name('events.set');
        Route::post('/reset', [EventController::class, 'resetEvent'])->name('events.reset');
        Route::get('/slot-events', [EventController::class, 'getSlotEvents'])->name('events.slot-events');
    });

    // Conditiebeheer
    Route::resource('conditions', ConditionsController::class)
        ->except(['show', 'create', 'edit'])
        ->names(['index' => 'conditions']);

    // Feedback (beveiligd)
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/feedback/{feedback}/edit', [FeedbackController::class, 'edit'])->name('feedback.edit');
    Route::patch('/feedback/{feedback}', [FeedbackController::class, 'update'])->name('feedback.update');
    Route::delete('/feedback/{feedback}', [FeedbackController::class, 'destroy'])->name('feedback.destroy');
});

// ========== API routes ==========
Route::get('/api/modules/{module}/effects', function (\App\Models\Module $module) {
    return response()->json([
        'effects' => $module->effects->map(fn($e) => [
            'type' => $e->type,
            'value' => $e->value,
        ])
    ]);
});

Route::get('/api/events/{event}/effects', [EventController::class, 'getEventEffects'])->name('api.events.effects');
Route::get('/events/{event}/effects', [EventController::class, 'getEventEffectsApi']);

// ========== Auth scaffolding ==========
require __DIR__ . '/auth.php';
