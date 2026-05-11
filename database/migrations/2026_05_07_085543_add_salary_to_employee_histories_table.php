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
        Schema::table('employee_histories', function (Blueprint $table) {
            $table->decimal('old_salary', 10, 2)->nullable()->after('new_status');
            $table->decimal('new_salary', 10, 2)->nullable()->after('old_salary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_histories', function (Blueprint $table) {
            $table->dropColumn(['old_salary', 'new_salary']);
        });
    }
};
