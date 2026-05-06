<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

// Legacy dashboard (fallback) - Redirects to role-specific dashboard
Route::get('/dashboard', function () {
    $user = auth()->user();
    $roleName = $user->role?->name ?? 'tc';

    $redirectRoute = match ($roleName) {
        'admin' => 'admin.dashboard',
        'cp'    => 'cp.dashboard',
        'sup'   => 'sup.dashboard',
        'tc'    => 'tc.dashboard',
        default => 'dashboard',
    };

    if ($redirectRoute === 'dashboard') {
        return Inertia::render('Dashboard');
    }

    return redirect()->route($redirectRoute);
})->middleware(['auth', 'verified'])->name('dashboard');

// ─── Admin ────────────────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Admin/Dashboard'))->name('dashboard');
});

// ─── Chef de Projet (CP) ──────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('cp')->name('cp.')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Cp/Dashboard'))->name('dashboard');
});

// ─── Superviseur (SUP) ────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('sup')->name('sup.')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Sup/Dashboard'))->name('dashboard');
});

// ─── Technicien (TC) ──────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('tc')->name('tc.')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Tc/Dashboard'))->name('dashboard');
});

// ─── Profile ──────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';