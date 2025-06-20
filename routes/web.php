<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModuleHandlerController;
use App\Http\Controllers\ConditionsController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ClockController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/simdash', [SimulationController::class, 'index'])->name('simulatiedashboard');
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('events');
        Route::post('/set', [EventController::class, 'setEvent'])->name('events.set');
        Route::post('/reset', [EventController::class, 'resetEvent'])->name('events.reset');
        Route::get('/slot-events', [EventController::class, 'getSlotEvents'])->name('events.slot-events');
    });
    Route::resource('conditions', ConditionsController::class)
        ->except(['show', 'create', 'edit'])
        ->names([
            'index' => 'conditions',
        ]);
    Route::post('/simulatie/koppel-module', [SimulationController::class, 'koppelModule']);
    Route::patch('/slots/{slot}/remove-module', [SimulationController::class, 'removeModule'])->name('slots.removeModule');
    Route::post('/effects/module/{moduleId}/{type}', [SimulationController::class, 'updateEffect'])->name('effects.update');
    Route::get('/api/modules/{module}/effects', function (\App\Models\Module $module) {
        return response()->json([
            'effects' => $module->effects->map(fn($e) => [
                'type' => $e->type,
                'value' => $e->value,
            ])
        ]);
    });

    Route::get('/module', [ModuleHandlerController::class, 'index'])->name('module.index');
    Route::post('/modules', [ModuleHandlerController::class, 'store'])->name('modules.store');
    Route::put('/modules/{id}', [ModuleHandlerController::class, 'update'])->name('modules.update');
    Route::delete('/modules/{module}', [ModuleHandlerController::class, 'destroy'])->name('modules.destroy');
});

Route::get('/api/events/{event}/effects', [EventController::class, 'getEventEffects'])->name('api.events.effects');
Route::get('/events/{event}/effects', [EventController::class, 'getEventEffectsApi']);
Route::middleware('auth')->group(function () {
    Route::post('/save-clock',  [ClockController::class, 'store'])
        ->name('clock.save');

    Route::get('/user-clock/current', [ClockController::class, 'current'])
        ->name('clock.current');
});


require __DIR__.'/auth.php';
