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
        Schema::create('vehicle_counts', function (Blueprint $table) {
            $table->id();

            $table->string('event_type', 100)->index();
            $table->string('device_name', 150);
            $table->string('mac_address', 50);
            $table->string('sn', 100)->nullable()->index();

            $table->timestamp('event_time')->index();

            $table->string('detection_region', 50)->nullable();
            $table->string('detection_region_name', 100);

            $table->unsignedBigInteger('total_vehicle')->default(0);
            $table->unsignedBigInteger('count_type_car')->default(0);
            $table->unsignedBigInteger('count_type_motorcycle')->default(0);
            $table->unsignedBigInteger('count_type_non_motor')->default(0);
            $table->unsignedBigInteger('count_type_small_vehicle')->default(0);
            $table->unsignedBigInteger('count_type_medium_vehicle')->default(0);
            $table->unsignedBigInteger('count_type_large_vehicle')->default(0);

            $table->unsignedBigInteger('car')->default(0);
            $table->unsignedBigInteger('motorbike')->default(0);
            $table->unsignedBigInteger('bus')->default(0);
            $table->unsignedBigInteger('truck')->default(0);
            $table->unsignedBigInteger('van')->default(0);
            $table->unsignedBigInteger('suv')->default(0);
            $table->unsignedBigInteger('fire_engine')->default(0);
            $table->unsignedBigInteger('ambulance')->default(0);
            $table->unsignedBigInteger('bicycle')->default(0);
            $table->unsignedBigInteger('other')->default(0);

            $table->string('full_snapshot_path')->nullable();

            $table->string('_camera', 100)->nullable();
            $table->string('_camera_mac', 50)->nullable();
            $table->string('_camera_identifier', 100)->nullable();

            $table->unsignedInteger('_local_lane')->nullable();
            $table->unsignedInteger('_global_lane')->nullable();

            $table->json('raw_payload')->nullable();

            $table->timestamps();

            // Recommended unique: combination unique
            $table->unique(
                ['device_name', 'mac_address', 'detection_region_name'],
                'vehicle_counts_device_mac_region_unique'
            );

            // Helpful indexes for fast filtering
            $table->index(['device_name', 'event_time']);
            $table->index(['mac_address', 'event_time']);
            $table->index(['detection_region_name', 'event_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_counts');
    }
};
