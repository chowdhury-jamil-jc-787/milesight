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
        Schema::create('log_file_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('device_name');
            $table->string('file'); // file path or name
            $table->date('date');   // upload date
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_file_uploads');
    }
};
