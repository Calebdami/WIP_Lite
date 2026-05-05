<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlanningAssignmentsSeeder extends Seeder
{
    public function run()
    {
        // Créer les 3 planning assignments de base
        DB::table('planning_assignments')->insert([
            [
                'planning_model_id' => 1,
                'employee_id' => 1,
                'start_date' => '2026-01-01',
                'end_date' => '2026-06-30',
                'status' => 'validé',
                'validated_by' => 2,
                'validated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'planning_model_id' => 2,
                'employee_id' => 2,
                'start_date' => '2026-02-01',
                'end_date' => '2026-12-31',
                'status' => 'en attente',
                'validated_by' => null,
                'validated_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'planning_model_id' => 3,
                'employee_id' => 3,
                'start_date' => '2026-03-01',
                'end_date' => null,
                'status' => 'suspendu',
                'validated_by' => 1,
                'validated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // Générer 247 planning assignments supplémentaires
        $statuses = ['en attente', 'validé', 'suspendu'];
        
        for ($i = 4; $i <= 250; $i++) {
            $status = $statuses[array_rand($statuses)];
            $startDate = now()->subDays(rand(0, 365));
            $endDate = $status === 'suspendu' ? $startDate->copy()->addDays(rand(1, 30)) : 
                      ($status === 'validé' ? $startDate->copy()->addDays(rand(90, 365)) : null);
            $validatedBy = $status === 'validé' ? rand(1, 10) : null;
            $validatedAt = $validatedBy ? Carbon::now()->subDays(rand(1, 30)) : null;

            DB::table('planning_assignments')->insert([
                'planning_model_id' => rand(1, 250),
                'employee_id' => rand(1, 300),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate ? $endDate->format('Y-m-d') : null,
                'status' => $status,
                'validated_by' => $validatedBy,
                'validated_at' => $validatedAt,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}