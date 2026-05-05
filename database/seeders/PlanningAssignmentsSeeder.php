<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlanningAssignmentsSeeder extends Seeder
{
    public function run()
    {
        DB::table('planning_assignments')->insert([
            [
                'planning_model_id' => 1,
                'employee_id' => 1,
                'start_date' => '2026-01-01',
                'end_date' => '2026-06-30',
                'status' => 'validated',
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
                'status' => 'pending',
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
                'status' => 'suspended',
                'validated_by' => 1,
                'validated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}