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

use App\Http\Controllers\UserController;

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
    
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

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

    // API Routes for Maintenance
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        // System Health
        Route::get('/health', [App\Http\Controllers\MaintenanceController::class, 'getSystemHealth']);
        Route::get('/logs/errors', [App\Http\Controllers\MaintenanceController::class, 'getErrorLogs']);

        // Database Management
        Route::post('/database/backup', [App\Http\Controllers\MaintenanceController::class, 'createBackup']);
        Route::get('/database/backups', [App\Http\Controllers\MaintenanceController::class, 'getBackups']);
        Route::get('/database/backups/{backupId}/download', [App\Http\Controllers\MaintenanceController::class, 'downloadBackup']);
        Route::post('/database/backups/{backupId}/restore', [App\Http\Controllers\MaintenanceController::class, 'restoreBackup']);
        Route::post('/database/clean-temp', [App\Http\Controllers\MaintenanceController::class, 'cleanTempTables']);
        Route::post('/database/reindex', [App\Http\Controllers\MaintenanceController::class, 'reindexTables']);
        Route::post('/database/remove-orphans', [App\Http\Controllers\MaintenanceController::class, 'removeOrphans']);
        Route::get('/database/migrations', [App\Http\Controllers\MaintenanceController::class, 'getMigrations']);
        Route::post('/database/anonymize', [App\Http\Controllers\MaintenanceController::class, 'anonymizeData']);

        // User Control & Security
        Route::get('/sessions', [App\Http\Controllers\MaintenanceController::class, 'getActiveSessions']);
        Route::delete('/sessions/{sessionId}', [App\Http\Controllers\MaintenanceController::class, 'forceLogout']);
        Route::get('/audit-logs', [App\Http\Controllers\MaintenanceController::class, 'getAuditLogs']);
        Route::get('/maintenance-mode', [App\Http\Controllers\MaintenanceController::class, 'getMaintenanceMode']);
        Route::post('/maintenance-mode', [App\Http\Controllers\MaintenanceController::class, 'toggleMaintenanceMode']);
        Route::get('/permissions/check', [App\Http\Controllers\MaintenanceController::class, 'checkCriticalAccess']);
        Route::get('/2fa/scan', [App\Http\Controllers\MaintenanceController::class, 'scan2FA']);

        // Application Maintenance
        Route::post('/files/purge-temp', [App\Http\Controllers\MaintenanceController::class, 'purgeTempFiles']);
        Route::post('/files/purge-reports', [App\Http\Controllers\MaintenanceController::class, 'purgeOldReports']);
        Route::post('/cache/clear-app', [App\Http\Controllers\MaintenanceController::class, 'clearAppCache']);
        Route::post('/cache/clear-routes', [App\Http\Controllers\MaintenanceController::class, 'clearRouteCache']);
        Route::post('/cache/clear-config', [App\Http\Controllers\MaintenanceController::class, 'clearConfigCache']);
        Route::get('/queue/jobs', [App\Http\Controllers\MaintenanceController::class, 'getQueueJobs']);
        Route::post('/queue/retry-failed', [App\Http\Controllers\MaintenanceController::class, 'retryFailedJobs']);
        Route::get('/config/env', [App\Http\Controllers\MaintenanceController::class, 'getEnvVars']);
        Route::post('/config/env', [App\Http\Controllers\MaintenanceController::class, 'saveEnvVars']);

        // Communication & Support
        Route::post('/notifications/send', [App\Http\Controllers\MaintenanceController::class, 'sendSystemNotification']);
        Route::post('/mail/test', [App\Http\Controllers\MaintenanceController::class, 'sendTestMail']);

        // Existing functionalities
        Route::post('/clear-cache', [App\Http\Controllers\MaintenanceController::class, 'clearCache']);
        Route::post('/backup-database', [App\Http\Controllers\MaintenanceController::class, 'backupDatabase']);
    });
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

    // Mes Heures (Consultation)
    Route::get('/my-hours', fn () => Inertia::render('Cp/Hours/Index'))->name('hours');
});

// ─── Superviseur (SUP) ────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('sup')->name('sup.')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Sup/Dashboard'))->name('dashboard');
    Route::get('/my-team', [\App\Http\Controllers\Sup\TeamController::class, 'index'])->name('team');
    Route::get('/schedule', fn () => Inertia::render('Sup/Schedule/Index'))->name('schedule');
    Route::get('/time-tracking', fn () => Inertia::render('Sup/TimeTracking/Index'))->name('time-tracking');
    Route::get('/my-hours', fn () => Inertia::render('Sup/Hours/Index'))->name('hours');
});

// ─── Technicien (TC) ──────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('tc')->name('tc.')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Tc/Dashboard'))->name('dashboard');
    Route::get('/my-schedule', fn () => Inertia::render('Tc/Schedule/Index'))->name('schedule');
    Route::get('/my-hours', fn () => Inertia::render('Tc/Hours/Index'))->name('hours');
    Route::get('/my-profile', fn () => Inertia::render('Tc/Profile/Index'))->name('profile');
});

// ─── API Feuilles de temps ────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('api/timesheets')->name('api.timesheets.')->group(function () {
    // CRUD de base
    Route::get('/', [App\Http\Controllers\TimesheetController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\TimesheetController::class, 'store'])->name('store');
    Route::get('/{timesheet}', [App\Http\Controllers\TimesheetController::class, 'show'])->name('show');
    Route::delete('/{timesheet}', [App\Http\Controllers\TimesheetController::class, 'destroy'])->name('destroy');

    // Workflow de validation
    Route::post('/{timesheet}/submit', [App\Http\Controllers\TimesheetController::class, 'submit'])->name('submit');
    Route::post('/{timesheet}/validate', [App\Http\Controllers\TimesheetController::class, 'validate_timesheet'])->name('validate');
    Route::post('/{timesheet}/reject', [App\Http\Controllers\TimesheetController::class, 'reject'])->name('reject');
    Route::post('/validate-batch', [App\Http\Controllers\TimesheetController::class, 'validateBatch'])->name('validate-batch');
    Route::post('/batch-update-hours', [App\Http\Controllers\TimesheetController::class, 'batchUpdateHours'])->name('batch-update-hours');

    // Historique
    Route::get('/{timesheet}/history', [App\Http\Controllers\TimesheetController::class, 'history'])->name('history');

    // Entrées (saisie des heures)
    Route::get('/{timesheet}/entries', [App\Http\Controllers\TimesheetEntryController::class, 'show'])->name('entries.show');
    Route::put('/entries/{entry}', [App\Http\Controllers\TimesheetEntryController::class, 'update'])->name('entries.update');
    Route::put('/{timesheet}/entries/batch', [App\Http\Controllers\TimesheetEntryController::class, 'batchUpdate'])->name('entries.batch');

    // Consultation TC (mes heures)
    Route::get('/my/hours', [App\Http\Controllers\TimesheetController::class, 'myHours'])->name('my-hours');
});

// API Agents (pour les sélections)
Route::middleware(['auth'])->get('/api/employees', function() {
    return App\Models\Employee::orderBy('last_name')->get();
});

// ─── Profile ──────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';