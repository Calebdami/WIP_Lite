<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Timesheet;

class TimesheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Timesheet::create([
            'employee_id' => 1,
            'period_start' => '2026-05-05',
            'period_end' => '2026-05-11',
            'status' => 'brouillon',
        ]);

        Timesheet::create([
            'employee_id' => 1,
            'period_start' => '2026-05-12',
            'period_end' => '2026-05-18',
            'status' => 'soumis',
            'validated_by' => 2,
            'validated_at' => now(),
        ]);
    }
}
