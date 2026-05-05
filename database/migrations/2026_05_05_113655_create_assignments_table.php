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
    Schema::create('assignments', function (Blueprint $table) {
        $table->id();

        // Employé concerné
        $table->foreignId('employee_id')
            ->constrained()
            ->cascadeOnDelete();

        // Campagne liée
        $table->foreignId('campaign_id')
            ->constrained()
            ->cascadeOnDelete();

        // Manager (c’est aussi un employee)
        $table->foreignId('manager_id')
            ->nullable()
            ->constrained('employees')
            ->nullOnDelete();

        // Poste / rôle dans la campagne
        $table->foreignId('position_id')
            ->constrained()
            ->cascadeOnDelete();

        // Statut de l'affectation
        $table->enum('status', ['active', 'terminated', 'suspended'])
            ->default('active');

        // Dates
        $table->date('start_date');
        $table->date('end_date')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
