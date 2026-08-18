<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel', 20)->default('public');
            $table->string('provider', 32)->default('xconnect')->index();
            $table->string('status', 30)->default('confirmed')->index();
            $table->string('booking_id', 64)->nullable()->index();
            $table->string('reference_no', 64)->nullable()->index();
            $table->string('internal_reference', 80)->nullable()->unique();
            $table->string('hotel_id', 64)->nullable()->index();
            $table->string('hotel_name')->nullable();
            $table->string('city_id', 64)->nullable();
            $table->string('nationality', 80)->nullable();
            $table->date('check_in')->nullable()->index();
            $table->date('check_out')->nullable();
            $table->unsignedSmallInteger('nights')->nullable();
            $table->unsignedTinyInteger('rooms_count')->default(1);
            $table->string('passenger_prefix', 10)->nullable();
            $table->string('passenger_first', 80)->nullable();
            $table->string('passenger_last', 80)->nullable();
            $table->string('passenger_email', 120)->nullable();
            $table->string('passenger_phone', 40)->nullable();
            $table->string('total_price', 40)->nullable();
            $table->string('currency', 8)->nullable();
            $table->json('guests')->nullable();
            $table->json('price_snapshot')->nullable();
            $table->json('raw_result')->nullable();
            $table->json('provider_snapshot')->nullable();
            $table->timestamp('booked_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_reservations');
    }
};
