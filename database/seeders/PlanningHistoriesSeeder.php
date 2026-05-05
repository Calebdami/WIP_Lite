<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlanningHistoriesSeeder extends Seeder
{
    public function run()
    {
        DB::table('planning_histories')->insert([
            [
                'planning_assignment_id' => 1,
                'old_status' => 'en attente',
                'new_status' => 'validé',
                'changed_by' => 2,
                'reason' => 'Validation par le manager',
                'created_at' => Carbon::now(),
            ],
            [
                'planning_assignment_id' => 2,
                'old_status' => 'en attente',
                'new_status' => 'suspendu',
                'changed_by' => 1,
                'reason' => 'Documents incomplets',
                'created_at' => Carbon::now(),
            ],
            [
                'planning_assignment_id' => 3,
                'old_status' => 'validé',
                'new_status' => 'suspendu',
                'changed_by' => 3,
                'reason' => 'Congé prolongé',
                'created_at' => Carbon::now(),
            ],
        ]);
    }
}