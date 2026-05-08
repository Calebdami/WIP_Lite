<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            [
                'code' => 'ADMIN',
                'name' => 'Administrateur',
                'description' => 'Accès complet à la gestion du personnel et des paramètres.',
            ],
            [
                'code' => 'CP',
                'name' => 'Chef de Plateau',
                'description' => 'Responsable de la gestion des superviseurs et des campagnes.',
            ],
            [
                'code' => 'SUP',
                'name' => 'Superviseur',
                'description' => 'Gestion directe des téléconseillers et saisie des heures travaillées.',
            ],
            [
                'code' => 'TC',
                'name' => 'Téléconseiller',
                'description' => 'Conseiller les clients et saisir les heures travaillées.',
            ],
        ];
        
        foreach ($positions as $position) {
            Position::updateOrCreate(['code' => $position['code']], $position);
        }
    }
}
