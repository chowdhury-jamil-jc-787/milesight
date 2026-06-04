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
        Schema::create('cms_events', function (Blueprint $table) {
            $table->id();
            $table->string('device_name');
            $table->string('event_type');
            $table->text('event_desc');
            $table->dateTime('time_stamp');
            $table->timestamps();

            $table->unique(['time_stamp', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_events');
    }
};