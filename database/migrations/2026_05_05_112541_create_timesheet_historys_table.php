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

            $table->foreignId('timesheet_id')
                ->constrained('timesheets')
                ->cascadeOnDelete();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->enum('old_status', ['brouillon', 'soumis', 'valide'])->nullable();
            $table->enum('new_status', ['brouillon', 'soumis', 'valide']);

            $table->foreignId('changed_by')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->text('reason')->nullable();

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
