<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModuleHandlerController;
use App\Http\Controllers\ConditionsController;

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
    Route::get('/conditions', [ConditionsController::class, 'index'])->name('conditions');
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

require __DIR__.'/auth.php';

