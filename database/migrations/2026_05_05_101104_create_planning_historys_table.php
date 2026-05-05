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
        Schema::create('planning_histories', function (Blueprint $table) {
            $table->id();

            // Référence à l'affectation concernée
            $table->foreignId('planning_assignment_id')->constrained('planning_assignments')->onDelete('cascade');

            $table->string('old_status')->nullable(); // nullable si c'est la création
            $table->string('new_status');

            // Qui a fait le changement
            $table->foreignId('changed_by')->constrained('users');

            $table->text('reason')->nullable();

            // On n'utilise souvent que created_at pour un historique (pas besoin d'updated_at)
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planning_historys');
    }
};
