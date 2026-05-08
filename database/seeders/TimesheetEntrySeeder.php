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
            'date' => '2026-05-05',
            'check_in' => '08:00',
            'check_out' => '17:00',
            'total_hours' => 8.00,
            'comment' => 'Travail sur projet A',
            'planned_hours' => 8.00,
        ]);

        TimesheetEntry::create([
            'timesheet_id' => 1,
            'date' => '2026-05-06',
            'check_in' => '08:30',
            'check_out' => '17:30',
            'total_hours' => 8.00,
            'comment' => 'Travail sur projet B',
            'planned_hours' => 8.00,
        ]);

        // Générer 248 entrées supplémentaires
        for ($i = 3; $i <= 250; $i++) {
            $timesheetId = rand(1, 250);
            $date = now()->subDays(rand(0, 365))->format('Y-m-d');
            $startTime = rand(8, 10) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
            $endTime = rand(16, 19) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
            
            // Calculer les heures travaillées
            $start = strtotime($startTime);
            $end = strtotime($endTime);
            $totalHours = round(($end - $start) / 3600, 2);

            TimesheetEntry::create([
                'timesheet_id' => $timesheetId,
                'date' => $date,
                'check_in' => $startTime,
                'check_out' => $endTime,
                'total_hours' => $totalHours,
                'planned_hours' => 8.00,
                'comment' => 'Description de la tâche ' . $i,
            ]);
        }
    }
}
