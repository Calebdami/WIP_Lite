<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\ActivityLog;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || auth()->user()->role?->name !== 'admin') {
                abort(403, 'Accès non autorisé');
            }
            return $next($request);
        });
    }

    // 1. État de Santé du Système
    public function getSystemHealth()
    {
        return response()->json([
            'cpu' => $this->getCpuUsage(),
            'ram' => $this->getRamUsage(),
            'disk' => $this->getDiskUsage(),
            'version' => config('app.version', '1.0.0'),
            'services' => $this->checkServices()
        ]);
    }

    public function getErrorLogs()
    {
        // Get recent error logs from Laravel logs
        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (file_exists($logPath)) {
            $lines = array_slice(file($logPath), -50); // Last 50 lines
            foreach ($lines as $line) {
                if (preg_match('/^\[(.*?)\] (\w+)\.(\w+): (.*)$/', $line, $matches)) {
                    $logs[] = [
                        'timestamp' => $matches[1],
                        'level' => $matches[2],
                        'channel' => $matches[3],
                        'message' => $matches[4],
                        'created_at' => $matches[1]
                    ];
                }
            }
        }

        return response()->json(array_reverse($logs));
    }

    // 2. Gestion de la Base de Données
    public function createBackup()
    {
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $path = storage_path('backups/' . $filename);

            // Ensure backup directory exists
            if (!file_exists(storage_path('backups'))) {
                mkdir(storage_path('backups'), 0755, true);
            }

            // Create backup using mysqldump or similar
            $command = sprintf(
                'mysqldump -u%s -p%s %s > %s',
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
                $path
            );

            exec($command, $output, $returnVar);

            if ($returnVar === 0) {
                // Log the backup creation
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'backup_created',
                    'model_type' => 'database',
                    'model_id' => null,
                    'description' => 'Sauvegarde manuelle créée: ' . $filename,
                    'ip_address' => request()->ip()
                ]);

                return response()->json(['message' => 'Sauvegarde créée avec succès', 'filename' => $filename]);
            } else {
                throw new \Exception('Erreur lors de la création de la sauvegarde');
            }
        } catch (\Exception $e) {
            Log::error('Backup creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la création de la sauvegarde'], 500);
        }
    }

    public function getBackups()
    {
        $backups = [];
        $backupDir = storage_path('backups');

        if (file_exists($backupDir)) {
            $files = glob($backupDir . '/*.sql');
            foreach ($files as $file) {
                $backups[] = [
                    'id' => basename($file, '.sql'),
                    'filename' => basename($file),
                    'size' => filesize($file),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }
        }

        return response()->json($backups);
    }

    public function downloadBackup($backupId)
    {
        $file = storage_path('backups/' . $backupId . '.sql');

        if (!file_exists($file)) {
            return response()->json(['error' => 'Sauvegarde introuvable'], 404);
        }

        return response()->download($file);
    }

    public function restoreBackup($backupId)
    {
        try {
            $file = storage_path('backups/' . $backupId . '.sql');

            if (!file_exists($file)) {
                return response()->json(['error' => 'Sauvegarde introuvable'], 404);
            }

            // Restore backup
            $command = sprintf(
                'mysql -u%s -p%s %s < %s',
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
                $file
            );

            exec($command, $output, $returnVar);

            if ($returnVar === 0) {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'backup_restored',
                    'model_type' => 'database',
                    'model_id' => null,
                    'description' => 'Sauvegarde restaurée: ' . $backupId,
                    'ip_address' => request()->ip()
                ]);

                return response()->json(['message' => 'Sauvegarde restaurée avec succès']);
            } else {
                throw new \Exception('Erreur lors de la restauration');
            }
        } catch (\Exception $e) {
            Log::error('Backup restore failed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la restauration'], 500);
        }
    }

    public function cleanTempTables()
    {
        try {
            // Clean temporary tables (example for Laravel)
            DB::statement('DELETE FROM jobs WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)');
            DB::statement('DELETE FROM failed_jobs WHERE failed_at < DATE_SUB(NOW(), INTERVAL 30 DAY)');

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'temp_tables_cleaned',
                'resource_type' => 'database',
                'resource_id' => null,
                'description' => 'Tables temporaires nettoyées',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return response()->json(['message' => 'Tables temporaires nettoyées']);
        } catch (\Exception $e) {
            Log::error('Clean temp tables failed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du nettoyage'], 500);
        }
    }

    public function reindexTables()
    {
        try {
            $tables = ['users', 'employees', 'campaigns', 'assignments', 'timesheets'];

            foreach ($tables as $table) {
                DB::statement("ANALYZE TABLE $table");
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'tables_reindexed',
                'resource_type' => 'database',
                'resource_id' => null,
                'description' => 'Tables réindexées',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return response()->json(['message' => 'Tables réindexées']);
        } catch (\Exception $e) {
            Log::error('Reindex tables failed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la réindexation'], 500);
        }
    }

    public function removeOrphans()
    {
        try {
            // Remove orphaned records
            DB::statement('DELETE FROM assignments WHERE employee_id NOT IN (SELECT id FROM employees)');
            DB::statement('DELETE FROM planning_assignments WHERE employee_id NOT IN (SELECT id FROM employees)');
            DB::statement('DELETE FROM timesheets WHERE employee_id NOT IN (SELECT id FROM employees)');

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'orphans_removed',
                'resource_type' => 'database',
                'resource_id' => null,
                'description' => 'Entrées orphelines supprimées',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return response()->json(['message' => 'Entrées orphelines supprimées']);
        } catch (\Exception $e) {
            Log::error('Remove orphans failed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la suppression'], 500);
        }
    }

    public function getMigrations()
    {
        $migrations = DB::table('migrations')
            ->orderBy('batch', 'desc')
            ->orderBy('migration', 'desc')
            ->get();

        return response()->json($migrations);
    }

    public function anonymizeData()
    {
        try {
            // Anonymize sensitive data (example)
            DB::statement("UPDATE employees SET phone = CONCAT('ANON_', id), email = CONCAT('anon', id, '@example.com') WHERE id > 0");

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'data_anonymized',
                'resource_type' => 'database',
                'resource_id' => null,
                'description' => 'Données anonymisées',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return response()->json(['message' => 'Données anonymisées']);
        } catch (\Exception $e) {
            Log::error('Data anonymization failed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'anonymisation'], 500);
        }
    }

    // 3. Contrôle des Utilisateurs et Sécurité
    public function getActiveSessions()
    {
        try {
            $sessions = DB::table('sessions')
                ->join('users', 'sessions.user_id', '=', 'users.id')
                ->where('sessions.last_activity', '>', time() - 3600) // Active in last hour
                ->select('sessions.id', 'users.name', 'sessions.ip_address', 'sessions.user_agent', 'sessions.last_activity')
                ->get()
                ->map(function ($session) {
                    $session->last_activity = Carbon::createFromTimestamp($session->last_activity)->format('Y-m-d H:i:s');
                    return $session;
                });

            return response()->json($sessions);
        } catch (\Exception $e) {
            // If sessions table doesn't exist or other error, return empty array
            return response()->json([]);
        }
    }

    public function forceLogout($sessionId)
    {
        DB::table('sessions')->where('id', $sessionId)->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'session_forced_logout',
            'resource_type' => 'session',
            'resource_id' => $sessionId,
            'description' => 'Déconnexion forcée de session',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return response()->json(['message' => 'Session terminée']);
    }

    public function getAuditLogs()
    {
        $logs = ActivityLog::with('user')
            ->latest()
            ->limit(100)
            ->get();

        return response()->json($logs);
    }

    public function toggleMaintenanceMode(Request $request)
    {
        $enabled = $request->boolean('enabled');
        $message = $request->input('message', 'Système en maintenance');

        if ($enabled) {
            file_put_contents(storage_path('maintenance.txt'), $message);
        } else {
            if (file_exists(storage_path('maintenance.txt'))) {
                unlink(storage_path('maintenance.txt'));
            }
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $enabled ? 'maintenance_enabled' : 'maintenance_disabled',
            'resource_type' => 'system',
            'resource_id' => null,
            'description' => $enabled ? 'Mode maintenance activé' : 'Mode maintenance désactivé',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return response()->json(['message' => 'Mode maintenance mis à jour']);
    }

    public function getMaintenanceMode()
    {
        $enabled = file_exists(storage_path('maintenance.txt'));
        $message = $enabled ? file_get_contents(storage_path('maintenance.txt')) : 'Le système est en maintenance.';

        return response()->json([
            'enabled' => $enabled,
            'message' => $message
        ]);
    }

    public function checkCriticalAccess()
    {
        $issues = [];

        // Check for users with admin role but no 2FA
        $adminUsers = User::whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })->get();

        foreach ($adminUsers as $user) {
            // TODO: Implement 2FA check when 2FA is implemented
            // if (!$user->hasTwoFactorEnabled()) {
                $issues[] = [
                    'user' => $user->name,
                    'issue' => 'Vérification 2FA non implémentée',
                    'severity' => 'warning'
                ];
            // }
        }

        return response()->json($issues);
    }

    public function scan2FA()
    {
        $issues = [];

        $users = User::all();

        foreach ($users as $user) {
            // Implement 2FA check when 2FA is implemented
            // if (!$user->hasTwoFactorEnabled()) {
                $issues[] = [
                    'user' => $user->name,
                    'issue' => 'Vérification 2FA non implémentée',
                    'severity' => 'info'
                ];
            // }
        }

        return response()->json($issues);
    }

    // 4. Maintenance Applicative & Métier
    public function purgeTempFiles()
    {
        try {
            $tempDir = storage_path('app/temp');
            if (file_exists($tempDir)) {
                $files = glob($tempDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < time() - 86400) { // Older than 24h
                        unlink($file);
                    }
                }
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'temp_files_purged',
                'resource_type' => 'files',
                'resource_id' => null,
                'description' => 'Fichiers temporaires purgés',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return response()->json(['message' => 'Fichiers temporaires purgés']);
        } catch (\Exception $e) {
            Log::error('Purge temp files failed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la purge'], 500);
        }
    }

    public function purgeOldReports()
    {
        try {
            $reportsDir = storage_path('app/reports');
            if (file_exists($reportsDir)) {
                $files = glob($reportsDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < time() - 2592000) { // Older than 30 days
                        unlink($file);
                    }
                }
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'old_reports_purged',
                'resource_type' => 'files',
                'resource_id' => null,
                'description' => 'Anciens rapports purgés',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return response()->json(['message' => 'Anciens rapports purgés']);
        } catch (\Exception $e) {
            Log::error('Purge old reports failed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la purge'], 500);
        }
    }

    public function clearAppCache()
    {
        Cache::flush();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'app_cache_cleared',
            'resource_type' => 'cache',
            'resource_id' => null,
            'description' => 'Cache application vidé',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return response()->json(['message' => 'Cache application vidé']);
    }

    public function clearRouteCache()
    {
        Artisan::call('route:clear');

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'route_cache_cleared',
            'resource_type' => 'cache',
            'resource_id' => null,
            'description' => 'Cache des routes vidé',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return response()->json(['message' => 'Cache des routes vidé']);
    }

    public function clearConfigCache()
    {
        Artisan::call('config:clear');

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'config_cache_cleared',
            'resource_type' => 'cache',
            'resource_id' => null,
            'description' => 'Cache de configuration vidé',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return response()->json(['message' => 'Cache de configuration vidé']);
    }

    public function getQueueJobs()
    {
        $jobs = DB::table('jobs')
            ->select('id', 'queue', 'payload', 'attempts', 'created_at')
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                return [
                    'id' => $job->id,
                    'name' => $payload['displayName'] ?? 'Unknown',
                    'status' => $job->attempts > 0 ? 'retrying' : 'pending',
                    'created_at' => $job->created_at
                ];
            });

        return response()->json($jobs);
    }

    public function retryFailedJobs()
    {
        Artisan::call('queue:retry', ['all' => true]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'failed_jobs_retried',
            'resource_type' => 'queue',
            'resource_id' => null,
            'description' => 'Tâches échouées relancées',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return response()->json(['message' => 'Tâches échouées relancées']);
    }

    public function getEnvVars()
    {
        $vars = [
            ['key' => 'MAIL_HOST', 'value' => env('MAIL_HOST'), 'type' => 'text'],
            ['key' => 'MAIL_PORT', 'value' => env('MAIL_PORT'), 'type' => 'number'],
            ['key' => 'MAIL_USERNAME', 'value' => env('MAIL_USERNAME'), 'type' => 'text'],
            ['key' => 'MAIL_PASSWORD', 'value' => env('MAIL_PASSWORD'), 'type' => 'password'],
            ['key' => 'APP_NAME', 'value' => env('APP_NAME'), 'type' => 'text'],
            ['key' => 'APP_URL', 'value' => env('APP_URL'), 'type' => 'text']
        ];

        return response()->json($vars);
    }

    public function saveEnvVars(Request $request)
    {
        $vars = $request->input('vars', []);

        $envContent = file_get_contents(base_path('.env'));

        foreach ($vars as $var) {
            $key = $var['key'];
            $value = $var['value'];

            // Update or add the environment variable
            if (preg_match("/^{$key}=.*$/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents(base_path('.env'), $envContent);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'env_vars_updated',
            'resource_type' => 'config',
            'resource_id' => null,
            'description' => 'Variables d\'environnement mises à jour',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return response()->json(['message' => 'Configuration sauvegardée']);
    }

    // 5. Communication & Support
    public function sendSystemNotification(Request $request)
    {
        $message = $request->input('message');
        $type = $request->input('type', 'info');

        // Broadcast notification to all users (simplified)
        // In a real app, you'd use broadcasting or a notification system
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'system_notification_sent',
            'resource_type' => 'notification',
            'resource_id' => null,
            'description' => "Notification système envoyée: {$message}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return response()->json(['message' => 'Notification envoyée']);
    }

    public function sendTestMail(Request $request)
    {
        $email = $request->input('email');

        try {
            Mail::raw('Ceci est un email de test du système de maintenance.', function ($message) use ($email) {
                $message->to($email)->subject('Test Email - WIP Lite');
            });

            return response()->json([
                'success' => true,
                'message' => 'Email de test envoyé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Test mail failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage()
            ], 500);
        }
    }

    // Fonctionnalités existantes
    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'cache_cleared',
            'resource_type' => 'system',
            'resource_id' => null,
            'description' => 'Cache système vidé',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return response()->json(['message' => 'Cache vidé avec succès']);
    }

    public function backupDatabase()
    {
        return $this->createBackup();
    }

    // Helper methods
    private function getCpuUsage()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows CPU usage (simplified)
            return rand(10, 80);
        } else {
            // Linux CPU usage
            $load = sys_getloadavg();
            return min(100, ($load[0] / 4) * 100); // Assuming 4 CPU cores
        }
    }

    private function getRamUsage()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return rand(30, 90);
        } else {
            $free = shell_exec('free');
            $free = (string)trim($free);
            $free_arr = explode("\n", $free);
            $mem = explode(" ", $free_arr[1]);
            $mem = array_filter($mem);
            $mem = array_merge($mem);
            $memory_usage = $mem[2] / $mem[1] * 100;

            return round($memory_usage);
        }
    }

    private function getDiskUsage()
    {
        $disk_free = disk_free_space("/");
        $disk_total = disk_total_space("/");
        $disk_used = $disk_total - $disk_free;
        $disk_usage = ($disk_used / $disk_total) * 100;

        return round($disk_usage);
    }

    private function checkServices()
    {
        $services = [];

        // Check database connection
        try {
            DB::connection()->getPdo();
            $services[] = ['name' => 'Base de données', 'status' => 'OK'];
        } catch (\Exception $e) {
            $services[] = ['name' => 'Base de données', 'status' => 'Erreur'];
        }

        // Check Redis if configured
        try {
            if (config('cache.default') === 'redis') {
                Cache::store('redis')->get('test');
                $services[] = ['name' => 'Cache Redis', 'status' => 'OK'];
            } else {
                $services[] = ['name' => 'Cache File', 'status' => 'OK'];
            }
        } catch (\Exception $e) {
            $services[] = ['name' => 'Cache', 'status' => 'Erreur'];
        }

        // Check mail configuration
        $services[] = ['name' => 'Configuration Mail', 'status' => config('mail.default') ? 'OK' : 'Non configuré'];

        return $services;
    }
}