<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\Employee;
use App\Models\Campaign;
use App\Models\Position;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        $campaigns = Campaign::all();
        $positions = Position::all();

        if ($employees->isEmpty() || $campaigns->isEmpty() || $positions->isEmpty()) {
            return;
        }

        // créer 250 assignments fake
        for ($i = 0; $i < 250; $i++) {

            $employee = $employees->random();
            $campaign = $campaigns->random();
            $position = $positions->random();
            $manager = $employees->random();
            $status = rand(0, 10) > 2 ? 'active' : (rand(0, 1) ? 'terminated' : 'suspended');
            $startDate = now()->subDays(rand(1, 365));
            $endDate = $status === 'terminated' ? $startDate->copy()->addDays(rand(30, 180)) : 
                      ($status === 'suspended' ? $startDate->copy()->addDays(rand(1, 29)) : null);

            Assignment::create([
                'employee_id' => $employee->id,
                'campaign_id' => $campaign->id,
                'manager_id' => $manager->id,
                'position_id' => $position->id,
                'status' => $status,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate ? $endDate->format('Y-m-d') : null,
            ]);
        }
    }
}