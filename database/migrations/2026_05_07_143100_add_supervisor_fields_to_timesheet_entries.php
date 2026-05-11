<?php
/*
|--------------------------------------------------------------------------
| Migration: Add Supervisor Specific Fields to Timesheet Entries
|--------------------------------------------------------------------------
| This migration adds fields for management, on-call, and training hours
| specifically intended for supervisor timesheet tracking by CP.
*/

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
        Schema::table('timesheet_entries', function (Blueprint $table) {
            $table->decimal('management_hours', 5, 2)->default(0)->after('overtime_hours');
            $table->decimal('on_call_hours', 5, 2)->default(0)->after('management_hours');
            $table->decimal('training_hours', 5, 2)->default(0)->after('on_call_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timesheet_entries', function (Blueprint $table) {
            $table->dropColumn(['management_hours', 'on_call_hours', 'training_hours']);
        });
    }
};
