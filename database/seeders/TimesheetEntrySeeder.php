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
        TimesheetEntry::create([
            'timesheet_id' => 1,
            'date' => '2026-05-05',
            'check_in' => '08:00',
            'check_out' => '17:00',
            'break_duration' => 60,
            'total_hours' => 8,
            'planned_hours' => 8,
            'overtime_hours' => 0,
        ]);
    }
}
