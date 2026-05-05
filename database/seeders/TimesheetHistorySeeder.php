<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TimesheetHistory;

class TimesheetHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TimesheetHistory::create([
            'timesheet_id' => 1,
            'employee_id' => 1,
            'old_status' => 'brouillon',
            'new_status' => 'soumis',
            'changed_by' => 2,
            'reason' => 'Validation initiale',
            'created_at' => now(),
        ]);
    }
}
