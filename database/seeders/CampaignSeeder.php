<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
            'end_date' => null,
            'status' => 'inactive',
        ]);

        Campaign::create([
            'name' => 'Campagne Fidélisation Clients',
            'description' => 'Programme de fidélité et réduction',
            'start_date' => '2025-10-01',
            'end_date' => '2025-12-31',
            'status' => 'finished',
        ]);
    }
}