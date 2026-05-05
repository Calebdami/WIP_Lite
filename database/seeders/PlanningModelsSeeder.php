<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlanningModelsSeeder extends Seeder
{
    public function run()
    {
        // Créer les 3 modèles de base
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

        // Générer 247 planning models supplémentaires avec des heures variées
        for ($i = 4; $i <= 250; $i++) {
            $monday = rand(0, 12);
            $tuesday = rand(0, 12);
            $wednesday = rand(0, 12);
            $thursday = rand(0, 12);
            $friday = rand(0, 12);
            $saturday = rand(0, 8);
            $sunday = rand(0, 8);
            $total = $monday + $tuesday + $wednesday + $thursday + $friday + $saturday + $sunday;

            DB::table('planning_models')->insert([
                'name' => 'Planning Model ' . $i,
                'description' => 'Modèle de planning généré automatiquement ' . $i,
                'monday_hours' => $monday,
                'tuesday_hours' => $tuesday,
                'wednesday_hours' => $wednesday,
                'thursday_hours' => $thursday,
                'friday_hours' => $friday,
                'saturday_hours' => $saturday,
                'sunday_hours' => $sunday,
                'total_hours' => $total,
                'created_by' => rand(1, 10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}