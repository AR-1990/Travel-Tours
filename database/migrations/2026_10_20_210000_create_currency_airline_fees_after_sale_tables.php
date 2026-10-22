<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3)->default('USD');
            $table->string('quote_currency', 3);
            $table->decimal('rate', 18, 8);
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['base_currency', 'quote_currency']);
        });

        Schema::create('airline_fees', function (Blueprint $table) {
            $table->id();
            $table->string('airline_code', 3);
            $table->string('fee_type', 40)->default('service'); // service, ticketing, other
            $table->string('label', 120);
            $table->string('mode', 16)->default('fixed'); // fixed, percent
            $table->decimal('amount', 12, 4)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->timestamps();
            $table->index(['airline_code', 'is_active']);
        });

        Schema::create('after_sale_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_reservation_id')->constrained('flight_reservations')->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 32); // void_no_penalty, cancel_with_penalty, change
            $table->string('status', 32)->default('pending'); // pending, approved, rejected, completed
            $table->string('change_kind', 40)->nullable(); // name, upgrade
            $table->decimal('penalty_amount', 12, 2)->nullable();
            $table->string('penalty_currency', 3)->nullable();
            $table->text('note')->nullable();
            $table->string('new_ticket_path')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['flight_reservation_id', 'type']);
        });

        Schema::table('flight_reservations', function (Blueprint $table) {
            $table->string('booking_currency', 3)->nullable()->after('currency');
            $table->decimal('currency_rate', 18, 8)->nullable()->after('booking_currency');
            $table->string('currency_rate_base', 3)->nullable()->after('currency_rate');
            $table->decimal('airline_fees_total', 12, 2)->nullable()->after('service_fee');
            $table->json('airline_fees_snapshot')->nullable()->after('airline_fees_total');
            $table->decimal('commission_amount', 12, 2)->nullable()->after('agent_profit');
            $table->boolean('print_with_price')->nullable()->after('commission_amount');
        });
    }

    public function down(): void
    {
        Schema::table('flight_reservations', function (Blueprint $table) {
            $table->dropColumn([
                'booking_currency',
                'currency_rate',
                'currency_rate_base',
                'airline_fees_total',
                'airline_fees_snapshot',
                'commission_amount',
                'print_with_price',
            ]);
        });
        Schema::dropIfExists('after_sale_requests');
        Schema::dropIfExists('airline_fees');
        Schema::dropIfExists('currency_rates');
    }
};
