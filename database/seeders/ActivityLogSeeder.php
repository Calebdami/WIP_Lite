<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Assignment;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $campaigns = Campaign::all();
        $assignments = Assignment::all();

        if ($users->isEmpty()) {
            return;
        }

        $actions = ['create', 'update', 'delete'];

        // 20 logs fake
        for ($i = 0; $i < 20; $i++) {

            $user = $users->random();
            $action = $actions[array_rand($actions)];

            // choisir un modèle aléatoire
            $type = collect(['Campaign', 'Assignment'])->random();

            if ($type === 'Campaign' && $campaigns->isNotEmpty()) {
                $model = $campaigns->random();
            } elseif ($assignments->isNotEmpty()) {
                $model = $assignments->random();
            } else {
                continue;
            }

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'model_type' => $type,
                'model_id' => $model->id,
                'description' => ucfirst($action) . " sur " . $type . " #" . $model->id,
                'ip_address' => '192.168.1.' . rand(1, 255),
            ]);
        }
    }
}