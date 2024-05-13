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
        Schema::table('traffic_events', function (Blueprint $table) {
            $table->string('violation_jpg')->nullable();
            $table->string('target_jpg')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('traffic_events', function (Blueprint $table) {
            $table->dropColumn('violation_jpg');
            $table->dropColumn('target_jpg');
        });
    }
};
