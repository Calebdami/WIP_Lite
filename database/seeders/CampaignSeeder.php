<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer 3 campagnes de base pour la démo
        Campaign::create([
            'name' => 'Campagne Marketing 2026',
            'description' => 'Promotion des nouveaux produits 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
            'status' => 'active',
        ]);

        Campaign::create([
            'name' => 'Campagne Recrutement IT',
            'description' => 'Recrutement de développeurs Laravel et Vue',
            'start_date' => '2026-02-01',
            'end_date' => '2026-06-30',
            'status' => 'active',
        ]);

        Campaign::create([
            'name' => 'Campagne Fidélisation Clients',
            'description' => 'Programme de fidélité et réduction',
            'start_date' => '2025-10-01',
            'end_date' => '2025-12-31',
            'status' => 'finished',
        ]);

        // Générer des campagnes supplémentaires cohérentes
        $campaignTypes = [
            'Marketing', 'Recrutement', 'Ventes', 'Communication', 'Formation', 
            'Lancement Produit', 'Fidélisation', 'Acquisition', 'Rétention', 'Notoriété'
        ];
        
        $statuses = ['active', 'inactive', 'finished'];
        
        for ($i = 4; $i <= 10; $i++) {
            $type = $campaignTypes[array_rand($campaignTypes)];
            $status = $statuses[array_rand($statuses)];
            $startDate = now()->subDays(rand(0, 365));
            $endDate = $status === 'finished' ? $startDate->copy()->addDays(rand(30, 180)) : 
                      ($status === 'active' ? null : $startDate->copy()->addDays(rand(60, 365)));

            Campaign::create([
                'name' => "Campagne {$type} {$i}",
                'description' => "Description de la campagne {$type} numéro {$i} générée automatiquement",
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate ? $endDate->format('Y-m-d') : null,
                'status' => $status,
            ]);
        }
    }
}