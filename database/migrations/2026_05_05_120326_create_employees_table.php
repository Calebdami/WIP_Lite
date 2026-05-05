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
        Schema::create('employees', function (Blueprint $create) {
            $create->id();
            
            // Clés étrangères
            $create->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $create->foreignId('position_id')->constrained('positions');

            // Informations identitaires
            $create->string('matricule')->unique();
            $create->string('first_name');
            $create->string('last_name');
            $create->date('birth_date');
            
            // Contact
            $create->string('phone')->nullable();
            $create->string('email')->unique();
            $create->text('address')->nullable();

            // Salaire et Statut
            $create->decimal('salary_base', 15, 2); // Précision pour les montants financiers
            $create->enum('status', ['actif', 'suspendu', 'inactif'])->default('actif');

            $create->timestamps();
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
