<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TimesheetEntry;
use App\Models\Timesheet;
use App\Models\Employee;
use Carbon\Carbon;

class TimesheetEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timesheets = Timesheet::all();
        $employees = Employee::all();
        
        if ($timesheets->isEmpty()) {
            return;
        }

        // Créer des entrées pour chaque feuille de temps des téléconseillers
        $tcEmployees = $employees->filter(function($emp) {
            return $emp->position->code === 'TC';
        });

        foreach ($tcEmployees as $tc) {
            // Récupérer les feuilles de temps de ce téléconseiller
            $tcTimesheets = $timesheets->where('employee_id', $tc->id);
            
            foreach ($tcTimesheets as $timesheet) {
                // Créer une entrée pour chaque jour de la semaine (lundi-vendredi)
                $startDate = Carbon::parse($timesheet->period_start);
                
                for ($day = 0; $day < 5; $day++) {
                    $currentDate = $startDate->copy()->addDays($day);
                    
                    TimesheetEntry::create([
                        'timesheet_id' => $timesheet->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'check_in' => '08:30',
                        'check_out' => '17:30',
                        'total_hours' => 8.00,
                        'planned_hours' => 8.00,
                        'comment' => 'Travail sur campagne ' . $currentDate->format('d/m/Y'),
                    ]);
                }
            }
        }

        // Créer des entrées pour les superviseurs
        $supEmployees = $employees->filter(function($emp) {
            return $emp->position->code === 'SUP';
        });

        foreach ($supEmployees as $sup) {
            // Récupérer les feuilles de temps de ce superviseur
            $supTimesheets = $timesheets->where('employee_id', $sup->id);
            
            foreach ($supTimesheets as $timesheet) {
                // Créer une entrée pour chaque jour de la semaine (lundi-vendredi)
                $startDate = Carbon::parse($timesheet->period_start);
                
                for ($day = 0; $day < 5; $day++) {
                    $currentDate = $startDate->copy()->addDays($day);
                    
                    TimesheetEntry::create([
                        'timesheet_id' => $timesheet->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'check_in' => '08:00',
                        'check_out' => '18:00',
                        'total_hours' => 9.00,
                        'planned_hours' => 8.00,
                        'comment' => 'Supervision équipe ' . $currentDate->format('d/m/Y'),
                    ]);
                }
            }
        }
    }
}
