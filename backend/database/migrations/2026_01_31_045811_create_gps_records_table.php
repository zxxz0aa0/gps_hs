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
        Schema::create('gps_records', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate', 20);
            $table->string('driver_name', 50)->nullable();
            $table->string('fleet_number', 20)->nullable();
            $table->date('date');
            $table->time('time');
            $table->string('location', 255)->nullable();
            $table->decimal('longitude', 10, 6);
            $table->decimal('latitude', 10, 6);
            $table->timestamps();

            $table->index(['license_plate', 'date']);
            $table->index('date');
            $table->unique(['license_plate', 'date', 'time', 'longitude', 'latitude'], 'gps_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gps_records');
    }
};
