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
        Schema::create('employee_histories', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            
            // On utilise nullable pour les "old" au cas où c'est le premier enregistrement
            $table->foreignId('old_position_id')->nullable()->constrained('positions');
            $table->foreignId('new_position_id')->constrained('positions');
            
            // Statuts (String ou Enum selon votre table employee)
            $table->string('old_status')->nullable();
            $table->string('new_status');

            // Qui a fait le changement ? (Lien vers la table users)
            $table->foreignId('changed_by')->nullable()->constrained('users');

            $table->text('reason')->nullable(); // Justification du changement
            
            // On utilise uniquement created_at car un historique n'est jamais modifié
            $table->timestamp('created_at')->useCurrent(); 
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_histories');
    }
};
