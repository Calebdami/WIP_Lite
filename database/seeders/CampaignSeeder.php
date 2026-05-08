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
        // Créer 15 campagnes : 10 actives, 4 finies et 1 inactive
        
        // Campagnes actives (10)
        $activeCampaigns = [
            ['Campagne Marketing Printemps', 'Promotion produits saisonniers', '2026-03-01', '2026-05-31'],
            ['Campagne Vente Été', 'Soldes estivales', '2026-06-01', '2026-08-31'],
            ['Campagne Rentrée Scolaire', 'Promotion back-to-school', '2026-08-15', '2026-10-15'],
            ['Campagne Fêtes de Fin d\'Année', 'Promotions de fin d\'année', '2026-11-01', '2026-12-31'],
            ['Campagne Recrutement IT', 'Recrutement développeurs', '2026-02-01', '2026-06-30'],
            ['Campagne Service Client', 'Amélioration satisfaction client', '2026-04-01', '2026-07-31'],
            ['Campagne Digital Transformation', 'Migration vers le digital', '2026-05-01', '2026-09-30'],
            ['Campagne Formation Continue', 'Programmes de formation', '2026-03-15', '2026-08-15'],
            ['Campagne Innovation Tech', 'Nouvelles technologies', '2026-07-01', '2026-11-30'],
            ['Campagne Développement Durable', 'Initiatives écologiques', '2026-09-01', '2027-02-28'],
        ];

        foreach ($activeCampaigns as $i => $campaign) {
            Campaign::create([
                'name' => $campaign[0],
                'description' => $campaign[1],
                'start_date' => $campaign[2],
                'end_date' => $campaign[3],
                'status' => 'active',
            ]);
        }

        // Campagnes finies (4)
        $finishedCampaigns = [
            ['Campagne Hiver 2025', 'Promotions hivernales', '2025-11-01', '2026-02-28'],
            ['Campagne Saint-Valentin', 'Promotions romantiques', '2026-01-20', '2026-02-14'],
            ['Campagne Soldes d\'Hiver', 'Réductions hivernales', '2025-12-01', '2026-01-31'],
            ['Campagne Lancement Produit', 'Nouveaux produits Q4', '2025-10-01', '2025-12-15'],
        ];

        foreach ($finishedCampaigns as $campaign) {
            Campaign::create([
                'name' => $campaign[0],
                'description' => $campaign[1],
                'start_date' => $campaign[2],
                'end_date' => $campaign[3],
                'status' => 'finished',
            ]);
        }

        // Campagne inactive (1)
        Campaign::create([
            'name' => 'Campagne Test En Attente',
            'description' => 'Campagne en attente de validation',
            'start_date' => '2026-12-01',
            'end_date' => '2027-03-01',
            'status' => 'inactive',
        ]);
    }
}