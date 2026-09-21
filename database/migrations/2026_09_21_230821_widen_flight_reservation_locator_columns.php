<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flight_reservations', function (Blueprint $table) {
            // Downtown Travel order IDs are UUIDs (36 chars); Travelport PNRs stay short.
            $table->string('universal_locator', 64)->nullable()->change();
            $table->string('air_reservation_locator', 64)->nullable()->change();
            $table->string('provider_locator', 64)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('flight_reservations', function (Blueprint $table) {
            $table->string('universal_locator', 32)->nullable()->change();
            $table->string('air_reservation_locator', 32)->nullable()->change();
            $table->string('provider_locator', 32)->nullable()->change();
        });
    }
};
