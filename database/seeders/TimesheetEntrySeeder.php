<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TimesheetEntry;

class TimesheetEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer quelques entrées de timesheet de base
        TimesheetEntry::create([
            'timesheet_id' => 1,
            'employee_id' => 1,
            'date' => '2026-05-05',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'hours_worked' => 8.00,
            'description' => 'Travail sur projet A',
            'project_code' => 'PROJ001',
        ]);

        TimesheetEntry::create([
            'timesheet_id' => 1,
            'employee_id' => 1,
            'date' => '2026-05-06',
            'start_time' => '08:30',
            'end_time' => '17:30',
            'hours_worked' => 8.00,
            'description' => 'Travail sur projet B',
            'project_code' => 'PROJ002',
        ]);

        // Générer 248 entrées supplémentaires
        for ($i = 3; $i <= 250; $i++) {
            $timesheetId = rand(1, 250);
            $employeeId = rand(1, 300);
            $date = now()->subDays(rand(0, 365))->format('Y-m-d');
            $startTime = rand(8, 10) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
            $endTime = rand(16, 19) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
            
            // Calculer les heures travaillées
            $start = strtotime($startTime);
            $end = strtotime($endTime);
            $hoursWorked = round(($end - $start) / 3600, 2);

            TimesheetEntry::create([
                'timesheet_id' => $timesheetId,
                'employee_id' => $employeeId,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'hours_worked' => $hoursWorked,
                'description' => 'Description de la tâche ' . $i,
                'project_code' => 'PROJ' . str_pad($i, 3, '0', STR_PAD_LEFT),
            ]);
        }
    }
}
