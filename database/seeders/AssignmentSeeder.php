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

        // créer 10 assignments fake
        for ($i = 0; $i < 10; $i++) {

            $employee = $employees->random();
            $campaign = $campaigns->random();
            $position = $positions->random();
            $manager = $employees->random();

            Assignment::create([
                'employee_id' => $employee->id,
                'campaign_id' => $campaign->id,
                'manager_id' => $manager->id,
                'position_id' => $position->id,
                'status' => 'active',
                'start_date' => now()->subDays(rand(1, 30)),
                'end_date' => null,
            ]);
        }
    }
}