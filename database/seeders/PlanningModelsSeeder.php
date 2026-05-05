<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlanningModelsSeeder extends Seeder
{
    public function run()
    {
        DB::table('planning_models')->insert([
            [
                'name' => 'Semaine 35h',
                'description' => 'Planning classique 7h par jour du lundi au vendredi',
                'monday_hours' => 7,
                'tuesday_hours' => 7,
                'wednesday_hours' => 7,
                'thursday_hours' => 7,
                'friday_hours' => 7,
                'saturday_hours' => 0,
                'sunday_hours' => 0,
                'total_hours' => 35,
                'created_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Semaine 40h',
                'description' => 'Planning 8h par jour du lundi au vendredi',
                'monday_hours' => 8,
                'tuesday_hours' => 8,
                'wednesday_hours' => 8,
                'thursday_hours' => 8,
                'friday_hours' => 8,
                'saturday_hours' => 0,
                'sunday_hours' => 0,
                'total_hours' => 40,
                'created_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Week-end uniquement',
                'description' => 'Travail samedi et dimanche',
                'monday_hours' => 0,
                'tuesday_hours' => 0,
                'wednesday_hours' => 0,
                'thursday_hours' => 0,
                'friday_hours' => 0,
                'saturday_hours' => 6,
                'sunday_hours' => 6,
                'total_hours' => 12,
                'created_by' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}