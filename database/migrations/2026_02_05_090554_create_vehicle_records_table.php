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
        Schema::create('vehicle_records', function (Blueprint $table) {
            $table->id();

            $table->string('serial')->index();
            $table->string('device_name')->index();

            // Your "Timestamp" column (avoid naming it exactly "timestamp" because it's a reserved-ish word)
            $table->dateTime('captured_at')->index();

            $table->string('license_plate_no')->index();

            // store file path like: vehicle_snapshots/xxxx.jpg
            $table->string('vehicle_snapshot')->nullable();

            $table->decimal('vehicle_height', 8, 2)->nullable(); // ex: 123.45

            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_records');
    }
};
