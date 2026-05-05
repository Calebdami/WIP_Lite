<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimesheetHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('timesheet_historys')->insert([
            [
                'timesheet_id' => 1,
                'employee_id' => 1,
                'old_status' => 'brouillon',
                'new_status' => 'soumis',
                'changed_by' => 2,
                'reason' => 'Validation initiale',
                'created_at' => Carbon::now(),
            ],
            [
                'timesheet_id' => 2,
                'employee_id' => 2,
                'old_status' => 'brouillon',
                'new_status' => 'valide',
                'changed_by' => 1,
                'reason' => 'Approbation finale',
                'created_at' => Carbon::now(),
            ],
            [
                'timesheet_id' => 3,
                'employee_id' => 3,
                'old_status' => 'soumis',
                'new_status' => 'brouillon',
                'changed_by' => 3,
                'reason' => 'Correction demandée',
                'created_at' => Carbon::now(),
            ],
        ]);
    }
}
