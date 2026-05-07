<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planning_models', function (Blueprint $table) {
            $table->string('hours_summary')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('planning_models', function (Blueprint $table) {
            $table->dropColumn('hours_summary');
        });
    }
};
