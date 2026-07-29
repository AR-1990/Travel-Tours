<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flight_reservations', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('ticketed_at');
            $table->string('gds_version', 20)->nullable()->after('provider_locator');
            $table->json('gds_snapshot')->nullable()->after('raw_result');
        });
    }

    public function down(): void
    {
        Schema::table('flight_reservations', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'gds_version', 'gds_snapshot']);
        });
    }
};
