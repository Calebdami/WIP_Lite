<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('timesheet_historys', function (Blueprint $table) {
            $table->id();

            // Feuille de temps concernée
            $table->foreignId('timesheet_id')
                ->constrained('timesheets')
                ->cascadeOnDelete();

            // Employé propriétaire de la feuille
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            // Transition de statut
            $table->enum('old_status', ['brouillon', 'soumis', 'valide', 'rejete'])->nullable();
            $table->enum('new_status', ['brouillon', 'soumis', 'valide', 'rejete']);

            // Auteur du changement (superviseur ou CP)
            $table->foreignId('changed_by')
                ->constrained('employees')
                ->cascadeOnDelete();

            // Raison du changement (obligatoire en cas de rejet)
            $table->text('reason')->nullable();

            // Date du changement (pas de updated_at car un historique ne se modifie pas)
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_historys');
    }
};
