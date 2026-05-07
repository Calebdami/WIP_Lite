<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            [
                'name' => 'Administrateur',
                'code' => 'ADMIN',
                'description' => 'Accès complet à la gestion du personnel et des paramètres.'
            ],
            [
                'name' => 'Chef de Projet',
                'code' => 'CP',
                'description' => 'Responsable du suivi des livrables et de la gestion d\'équipe.'
            ],
            [
                'name' => 'Superviseur',
                'code' => 'SUP',
                'description' => 'Contrôle la qualité et assiste les techniciens sur le terrain.'
            ],
            [
                'name' => 'Teleconseiller',
                'code' => 'TC',
                'description' => 'Effectue des appels sortants pour des enquêtes ou sondages.'
            ],
        ];

        foreach ($positions as $pos) {
            Position::updateOrCreate(['code' => $pos['code']], $pos);
        }
    }
}
