<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Timesheet;
use App\Models\Employee;
use App\Models\Campaign;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TimesheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        $campaigns = Campaign::all();
        
        if ($employees->isEmpty() || $campaigns->isEmpty()) {
            return;
        }

        // Créer des feuilles de temps pour les téléconseillers
        $tcEmployees = $employees->filter(function($emp) {
            return $emp->position->code === 'TC';
        });
        
        foreach ($tcEmployees as $tc) {
            // Une feuille de temps par semaine pour les 4 dernières semaines
            for ($week = 1; $week <= 4; $week++) {
                $weekStartDate = now()->subWeeks($week)->startOfWeek();
                
                Timesheet::create([
                    'employee_id' => $tc->id,
                    'period_start' => $weekStartDate->format('Y-m-d'),
                    'period_end' => $weekStartDate->copy()->addDays(6)->format('Y-m-d'),
                    'status' => 'brouillon',
                ]);
            }
        }

        // Créer des feuilles de temps pour les superviseurs
        $supEmployees = $employees->filter(function($emp) {
            return $emp->position->code === 'SUP';
        });
        
        foreach ($supEmployees as $sup) {
            // Une feuille de temps par semaine pour les 4 dernières semaines
            for ($week = 1; $week <= 4; $week++) {
                $weekStartDate = now()->subWeeks($week)->startOfWeek();
                
                Timesheet::create([
                    'employee_id' => $sup->id,
                    'period_start' => $weekStartDate->format('Y-m-d'),
                    'period_end' => $weekStartDate->copy()->addDays(6)->format('Y-m-d'),
                    'status' => 'brouillon',
                ]);
            }
        }
    }
}
