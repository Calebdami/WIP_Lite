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

            $table->foreignId('timesheet_id')
                ->constrained('timesheets')
                ->cascadeOnDelete();

            $table->date('date');

            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();

            $table->unsignedInteger('break_duration')->default(0); // en minutes

            $table->decimal('total_hours', 5, 2)->default(0);
            $table->decimal('planned_hours', 5, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);

            $table->string('absence_type')->nullable();
            $table->text('comment')->nullable();

            $table->timestamps();

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
