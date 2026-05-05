<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssignmentHistory;
use App\Models\Assignment;
use App\Models\Employee;
use App\Models\Campaign;
use App\Models\User;

class AssignmentHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assignments = Assignment::all();
        $employees = Employee::all();
        $campaigns = Campaign::all();
        $users = User::all();

        if ($assignments->isEmpty() || $employees->isEmpty() || $campaigns->isEmpty() || $users->isEmpty()) {
            return;
        }

        $actions = ['assign', 'release', 'transfer'];

        // créer 20 historiques
        for ($i = 0; $i < 20; $i++) {

            $assignment = $assignments->random();
            $employee = $employees->random();
            $user = $users->random();
            $action = $actions[array_rand($actions)];

            $oldCampaign = $campaigns->random();
            $newCampaign = $campaigns->random();

            $oldManager = $employees->random();
            $newManager = $employees->random();

            AssignmentHistory::create([
                'assignment_id' => $assignment->id,
                'employee_id' => $employee->id,

                'old_manager_id' => $oldManager->id,
                'new_manager_id' => $newManager->id,

                'old_campaign_id' => $oldCampaign->id,
                'new_campaign_id' => $newCampaign->id,

                'action_type' => $action,
                'changed_by' => $user->id,

                'reason' => match ($action) {
                    'assign' => 'Nouvelle affectation',
                    'release' => 'Fin de mission',
                    'transfer' => 'Changement de campagne',
                    default => 'Mise à jour'
                }
            ]);
        }
    }
}