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
        Schema::create('graphics', function (Blueprint $table) {
            $table->id();
            $table->string('device_name');
            $table->decimal('voltage', 8, 2);
            $table->timestamp('recorded_at');  // <-- replaces $table->time('time')
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graphics');
    }
};
