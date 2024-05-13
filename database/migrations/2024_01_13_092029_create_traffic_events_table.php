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
            Schema::create('traffic_events', function (Blueprint $table) {
                $table->id();
                $table->string('event');
                $table->string('device');
                $table->timestamp('time'); // Assuming time is a timestamp
                $table->integer('region');
                $table->string('region_name')->nullable();
                $table->string('license_plate')->nullable();
                $table->string('plate_type')->nullable();
                $table->string('plate_color')->nullable();
                $table->string('vehicle_type')->nullable();
                $table->string('vehicle_color')->nullable();
                $table->string('vehicle_brand')->nullable();
                $table->integer('object_tracking_box_x1')->nullable();
                $table->integer('object_tracking_box_y1')->nullable();
                $table->integer('object_tracking_box_x2')->nullable();
                $table->integer('object_tracking_box_y2')->nullable();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traffic_events');
    }
};
