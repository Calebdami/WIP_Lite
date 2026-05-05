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

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->date('period_start');
            $table->date('period_end');

            $table->enum('status', ['brouillon', 'soumis', 'valide'])
                ->default('brouillon');

            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->timestamp('validated_at')->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'period_start', 'period_end']);
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
