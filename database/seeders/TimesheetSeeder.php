<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Timesheet;

class TimesheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les 2 timesheets de base
        Timesheet::create([
            'employee_id' => 1,
            'period_start' => '2026-05-05',
            'period_end' => '2026-05-11',
            'status' => 'brouillon',
        ]);

        Timesheet::create([
            'employee_id' => 1,
            'period_start' => '2026-05-12',
            'period_end' => '2026-05-18',
            'status' => 'soumis',
            'validated_by' => 2,
            'validated_at' => now(),
        ]);

        // Générer 248 timesheets supplémentaires
        $statuses = ['brouillon', 'soumis', 'validé', 'rejeté'];
        
        for ($i = 3; $i <= 250; $i++) {
            $status = $statuses[array_rand($statuses)];
            $employeeId = rand(1, 300);
            $startDate = now()->subWeeks(rand(1, 52))->startOfWeek();
            $endDate = $startDate->copy()->endOfWeek();
            $validatedBy = in_array($status, ['validé', 'rejeté']) ? rand(1, 10) : null;
            $validatedAt = $validatedBy ? $startDate->copy()->addDays(rand(1, 7)) : null;

            Timesheet::create([
                'employee_id' => $employeeId,
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
                'status' => $status,
                'validated_by' => $validatedBy,
                'validated_at' => $validatedAt,
            ]);
        }
    }
}
