<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('currency', 3)->default('USD');
            $table->decimal('balance', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique('tenant_id');
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 32); // topup, sale, refund, reverse, adjustment
            $table->string('direction', 8); // credit, debit
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('description', 500)->nullable();
            $table->string('pnr', 32)->nullable();
            $table->string('order_ref', 64)->nullable();
            $table->foreignId('flight_reservation_id')->nullable()->constrained('flight_reservations')->nullOnDelete();
            $table->foreignId('reverses_transaction_id')->nullable()->constrained('wallet_transactions')->nullOnDelete();
            $table->string('vendor', 64)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index('pnr');
        });

        Schema::create('tenant_markups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('scope', 32); // airline, provider, agent, global
            $table->string('scope_key', 64)->nullable(); // EY, travelport, tenant id, *
            $table->string('mode', 16)->default('percent'); // percent, fixed
            $table->decimal('value', 12, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('priority')->default(100);
            $table->timestamps();

            $table->index(['scope', 'scope_key', 'is_active']);
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::table('flight_reservations', function (Blueprint $table) {
            $table->string('form_of_payment', 32)->nullable()->after('fare_basis');
            $table->decimal('service_fee', 12, 2)->nullable()->after('form_of_payment');
            $table->decimal('markup_amount', 12, 2)->nullable()->after('service_fee');
            $table->decimal('sell_total', 12, 2)->nullable()->after('markup_amount');
            $table->decimal('agent_profit', 12, 2)->nullable()->after('sell_total');
            $table->string('currency', 3)->nullable()->after('agent_profit');
        });
    }

    public function down(): void
    {
        Schema::table('flight_reservations', function (Blueprint $table) {
            $table->dropColumn([
                'form_of_payment',
                'service_fee',
                'markup_amount',
                'sell_total',
                'agent_profit',
                'currency',
            ]);
        });

        Schema::dropIfExists('tenant_markups');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
