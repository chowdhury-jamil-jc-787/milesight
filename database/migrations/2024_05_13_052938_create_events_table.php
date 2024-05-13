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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->string('device_name');
            $table->string('mac_address');
            $table->string('sn');
            $table->timestamp('time');
            $table->string('detection_region');
            $table->string('detection_region_name');
            $table->string('license_plate');
            $table->string('country_region');
            $table->string('plate_type');
            $table->string('plate_color');
            $table->string('vehicle_type');
            $table->string('vehicle_color');
            $table->string('vehicle_brand');
            $table->string('direction');
            $table->float('speed');
            $table->json('data_list');
            $table->integer('resolution_width');
            $table->integer('resolution_height');
            $table->integer('coordinate_x1');
            $table->integer('coordinate_y1');
            $table->integer('coordinate_x2');
            $table->integer('coordinate_y2');
            $table->integer('vehicle_tracking_box_x1');
            $table->integer('vehicle_tracking_box_y1');
            $table->integer('vehicle_tracking_box_x2');
            $table->integer('vehicle_tracking_box_y2');
            $table->string('license_plate_snapshot');
            $table->string('vehicle_snapshot');
            $table->string('full_snapshot');
            $table->string('violation_snapshot');
            $table->string('evidence_snapshot0');
            $table->string('evidence_snapshot1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
