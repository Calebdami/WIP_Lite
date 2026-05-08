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
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();

            // Employé concerné par la feuille de temps
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->onDelete('cascade');

            // Période couverte (généralement une semaine : lundi → dimanche)
            $table->date('period_start');
            $table->date('period_end');

            // Statut du workflow de validation
            $table->enum('status', ['brouillon', 'soumis', 'valide', 'rejete'])
                ->default('brouillon');

            // Validation par le Chef de Plateau (référence employees)
            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();
            $table->timestamp('validated_at')->nullable();

            $table->timestamps();

            // Index pour les recherches fréquentes par période
            $table->index(['period_start', 'period_end']);
            $table->index(['employee_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};
