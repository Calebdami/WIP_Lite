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

    // Personnel
    Route::get('/employees', [App\Http\Controllers\Admin\EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/history', [App\Http\Controllers\Admin\EmployeeController::class, 'history'])->name('employees.history');
    Route::post('/employees', [App\Http\Controllers\Admin\EmployeeController::class, 'store'])->name('employees.store');
    Route::put('/employees/{employee}', [App\Http\Controllers\Admin\EmployeeController::class, 'update'])->name('employees.update');
    Route::get('/employees/{employee}', [App\Http\Controllers\Admin\EmployeeController::class, 'show'])->name('employees.show');
    
    // Scoring
    Route::get('/scoring', [App\Http\Controllers\Admin\ScoringController::class, 'index'])->name('scoring.index');
    Route::get('/scoring/{employee}', [App\Http\Controllers\Admin\ScoringController::class, 'show'])->name('scoring.show');
    Route::post('/scoring/tasks', [App\Http\Controllers\Admin\ScoringController::class, 'storeTask'])->name('scoring.tasks.store');
    Route::patch('/scoring/tasks/{task}/validate', [App\Http\Controllers\Admin\ScoringController::class, 'validateTask'])->name('scoring.tasks.validate');
    
    Route::get('/users', fn () => Inertia::render('Admin/Users/Index'))->name('users.index');

    // Campagnes
    Route::resource('campaigns', App\Http\Controllers\Admin\CampaignController::class);

    // Affectations
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/structure', [App\Http\Controllers\Admin\AssignmentController::class, 'structure'])->name('structure');
        Route::post('/', [App\Http\Controllers\Admin\AssignmentController::class, 'store'])->name('store');
        Route::patch('/{assignment}/release', [App\Http\Controllers\Admin\AssignmentController::class, 'release'])->name('release');
        Route::get('/schedules', fn () => Inertia::render('Admin/Assignments/Schedules'))->name('schedules');
        Route::get('/resources', [App\Http\Controllers\Admin\AssignmentController::class, 'resources'])->name('resources');
        Route::get('/tracking', fn () => Inertia::render('Admin/Assignments/Tracking'))->name('tracking');
        Route::get('/validation', fn () => Inertia::render('Admin/Assignments/Validation'))->name('validation');
        Route::get('/history', fn () => Inertia::render('Admin/Assignments/History'))->name('history');
    });

    // Temps de travail
    Route::get('/time-tracking', fn () => Inertia::render('Admin/Time/Tracking'))->name('time.tracking');

    // Configuration
    Route::prefix('config')->name('config.')->group(function () {
        Route::get('/company', fn () => Inertia::render('Admin/Config/Company'))->name('company');
        Route::get('/referentials', fn () => Inertia::render('Admin/Config/Referentials'))->name('referentials');
        Route::get('/permissions', fn () => Inertia::render('Admin/Config/Permissions'))->name('permissions');
    });

    // Sécurité
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/logs', fn () => Inertia::render('Admin/Security/Logs'))->name('logs');
        Route::get('/sessions', fn () => Inertia::render('Admin/Security/Sessions'))->name('sessions');
    });

    // Calendrier
    Route::get('/calendar', fn () => Inertia::render('Admin/Calendar/Index'))->name('calendar');

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/templates', fn () => Inertia::render('Admin/Notifications/Templates'))->name('templates');
        Route::get('/broadcast', fn () => Inertia::render('Admin/Notifications/Broadcast'))->name('broadcast');
    });

    // Maintenance
    Route::get('/maintenance', fn () => Inertia::render('Admin/Maintenance/Index'))->name('maintenance');
});

// ─── Chef de Projet (CP) ──────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('cp')->name('cp.')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Cp/Dashboard'))->name('dashboard');
    
    // Équipes
    Route::get('/teams', [\App\Http\Controllers\Cp\TeamController::class, 'index'])->name('teams');

    // Plannings
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/templates', fn () => Inertia::render('Cp/Schedules/Templates'))->name('templates');
        Route::get('/assign', fn () => Inertia::render('Cp/Schedules/Assign'))->name('assign');
        Route::get('/validation', fn () => Inertia::render('Cp/Schedules/Validation'))->name('validation');
    });

    // Saisie & Validation
    Route::prefix('time-tracking')->name('time-tracking.')->group(function () {
        Route::get('/supervisors', fn () => Inertia::render('Cp/TimeTracking/Supervisors'))->name('supervisors');
        Route::get('/validate-tc', fn () => Inertia::render('Cp/TimeTracking/ValidateTC'))->name('validate-tc');
        Route::get('/discrepancies', fn () => Inertia::render('Cp/TimeTracking/Discrepancies'))->name('discrepancies');
    });
});

// ─── Superviseur (SUP) ────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('sup')->name('sup.')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Sup/Dashboard'))->name('dashboard');
    Route::get('/my-team', [\App\Http\Controllers\Sup\TeamController::class, 'index'])->name('team');
    Route::get('/schedule', fn () => Inertia::render('Sup/Schedule/Index'))->name('schedule');
    Route::get('/time-tracking', fn () => Inertia::render('Sup/TimeTracking/Index'))->name('time-tracking');
});

// ─── Technicien (TC) ──────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('tc')->name('tc.')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Tc/Dashboard'))->name('dashboard');
    Route::get('/my-schedule', fn () => Inertia::render('Tc/Schedule/Index'))->name('schedule');
    Route::get('/my-hours', fn () => Inertia::render('Tc/Hours/Index'))->name('hours');
    Route::get('/my-profile', fn () => Inertia::render('Tc/Profile/Index'))->name('profile');
});

// ─── Profile ──────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';