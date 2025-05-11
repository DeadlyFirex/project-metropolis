<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModuleHandlerController;

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
    Route::post('/simulatie/koppel-module', [SimulationController::class, 'koppelModule']);
    Route::patch('/slots/{slot}/remove-module', [SimulationController::class, 'removeModule'])->name('slots.removeModule');

    Route::get('/module', [ModuleHandlerController::class, 'index'])->name('module.index');
    Route::post('/modules', [ModuleHandlerController::class, 'store'])->name('modules.store');
    Route::delete('/modules/{module}', [ModuleHandlerController::class, 'destroy'])->name('modules.destroy');
});


require __DIR__ . '/auth.php';
