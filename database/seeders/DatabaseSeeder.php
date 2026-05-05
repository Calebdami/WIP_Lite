<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
{
    $this->call([
        RoleSeeder::class,
        AdminUserSeeder::class,
        PositionSeeder::class,
        EmployeeSeeder::class,
        CampaignSeeder::class,
        AssignmentSeeder::class,
        PlanningModelsSeeder::class,
        PlanningAssignmentsSeeder::class,
        TimesheetSeeder::class,
        TimesheetEntrySeeder::class,
        ActivityLogSeeder::class,
        EmployeeHistorySeeder::class,
        AssignmentHistorySeeder::class,
        PlanningHistoriesSeeder::class,
        TimesheetHistorySeeder::class,
    ]);
}
}
