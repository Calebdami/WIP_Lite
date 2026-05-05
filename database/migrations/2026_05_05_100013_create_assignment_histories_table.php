<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations.
     */
    public function up(): void
{
    Schema::create('assignment_histories', function (Blueprint $table) {
        $table->id();

        // Affectation concernée
        $table->foreignId('assignment_id')
            ->constrained()
            ->cascadeOnDelete();

        // Employé concerné
        $table->foreignId('employee_id')
            ->constrained()
            ->cascadeOnDelete();

        // Ancien / nouveau manager
        $table->foreignId('old_manager_id')
            ->nullable()
            ->constrained('employees')
            ->nullOnDelete();

        $table->foreignId('new_manager_id')
            ->nullable()
            ->constrained('employees')
            ->nullOnDelete();

        // Ancienne / nouvelle campagne
        $table->foreignId('old_campaign_id')
            ->nullable()
            ->constrained('campaigns')
            ->nullOnDelete();

        $table->foreignId('new_campaign_id')
            ->nullable()
            ->constrained('campaigns')
            ->nullOnDelete();

        // Type d'action
        $table->enum('action_type', ['assign', 'release', 'transfer']);

        // Auteur du changement
        $table->foreignId('changed_by')
            ->constrained('users')
            ->cascadeOnDelete();

        // Raison
        $table->text('reason')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_histories');
    }
};
