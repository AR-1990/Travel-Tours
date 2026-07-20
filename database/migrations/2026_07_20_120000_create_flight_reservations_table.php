<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel', 20)->default('public');
            $table->string('status', 30)->default('reserved')->index();
            $table->string('universal_locator', 32)->nullable()->index();
            $table->string('air_reservation_locator', 32)->nullable()->index();
            $table->string('provider_locator', 32)->nullable();
            $table->string('origin', 8)->nullable();
            $table->string('destination', 8)->nullable();
            $table->date('departure_date')->nullable();
            $table->date('return_date')->nullable();
            $table->unsignedTinyInteger('adults')->default(1);
            $table->string('carrier', 8)->nullable();
            $table->string('passenger_prefix', 10)->nullable();
            $table->string('passenger_first', 80)->nullable();
            $table->string('passenger_last', 80)->nullable();
            $table->string('passenger_email', 120)->nullable();
            $table->string('passenger_phone', 40)->nullable();
            $table->date('passenger_dob')->nullable();
            $table->string('passenger_gender', 1)->nullable();
            $table->string('total_price', 40)->nullable();
            $table->string('base_price', 40)->nullable();
            $table->string('taxes', 40)->nullable();
            $table->string('fare_basis', 40)->nullable();
            $table->json('itinerary')->nullable();
            $table->json('price_snapshot')->nullable();
            $table->json('ticket_numbers')->nullable();
            $table->json('raw_result')->nullable();
            $table->timestamp('booked_at')->nullable();
            $table->timestamp('ticketed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_reservations');
    }
};
