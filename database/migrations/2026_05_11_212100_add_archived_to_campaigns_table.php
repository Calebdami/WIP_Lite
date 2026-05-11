<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // On ajoute la colonne archived_at
        Schema::table('campaigns', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('status');
        });

        // Pour l'enum, on ne peut pas facilement le modifier en SQLite ou certains drivers sans perdre de données ou faire des manips complexes.
        // On va plutôt gérer le statut "archived" logiquement via archived_at, 
        // ou si on est sur MySQL on peut tenter de modifier l'enum.
        // Comme on veut que ce soit robuste, on va simplement s'assurer que si on ne peut pas modifier l'enum, on utilise archived_at.
        
        // Tentative de modification de l'enum pour MySQL
        try {
            DB::statement("ALTER TABLE campaigns MODIFY COLUMN status ENUM('active', 'inactive', 'finished', 'archived') DEFAULT 'inactive'");
        } catch (\Exception $e) {
            // Probablement SQLite ou driver non compatible, on ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
        
        try {
            DB::statement("ALTER TABLE campaigns MODIFY COLUMN status ENUM('active', 'inactive', 'finished') DEFAULT 'inactive'");
        } catch (\Exception $e) {
            // Ignore
        }
    }
};
