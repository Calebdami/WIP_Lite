<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::take(5)->get();
        $positions = Position::all();

        foreach ($employees as $employee) {
            EmployeeHistory::create([
                'employee_id' => $employee->id,
                'old_position_id' => $positions->where('code', 'TC')->first()?->id,
                'new_position_id' => $employee->position_id,
                'old_status' => 'actif',
                'new_status' => $employee->status,
                'changed_by' => 1, // ID de l'admin
                'reason' => 'Promotion annuelle suite aux résultats.',
                'created_at' => now()->subMonths(fake()->numberBetween(1, 12)),
            ]);
        }
    }
}
