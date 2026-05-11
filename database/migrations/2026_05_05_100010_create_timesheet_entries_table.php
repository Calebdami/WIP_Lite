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
        Schema::create('timesheet_entries', function (Blueprint $table) {
            $table->id();

            // Référence à la feuille de temps parente
            $table->foreignId('timesheet_id')
                ->constrained('timesheets')
                ->onDelete('cascade');

            // Jour concerné
            $table->date('date');

            // Heures d'arrivée et de départ
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();

            // Durée de pause effective (en minutes)
            $table->unsignedSmallInteger('break_duration')->default(0);

            // Heures réellement travaillées ce jour (calculées)
            $table->decimal('total_hours', 5, 2)->default(0);

            // Heures prévues ce jour (copiées du planning)
            $table->decimal('planned_hours', 5, 2)->default(0);

            // Heures supplémentaires (calculées : total_hours - planned_hours si positif)
            $table->decimal('overtime_hours', 5, 2)->default(0);

            // Type d'absence éventuelle (maladie, congé, etc.)
            $table->string('absence_type')->nullable();

            // Commentaire / remarque du superviseur
            $table->text('comment')->nullable();

            $table->timestamps();

            // Unicité : une seule entrée par jour et par feuille de temps
            $table->unique(['timesheet_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_entries');
    }
};
