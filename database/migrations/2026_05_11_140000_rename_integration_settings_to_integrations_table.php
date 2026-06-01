<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('integration_settings')) {
            return;
        }

        Schema::rename('integration_settings', 'integrations');

        Schema::table('integrations', function (Blueprint $table) {
            $table->renameColumn('provider', 'slug');
        });

        Schema::table('integrations', function (Blueprint $table) {
            $table->string('name')->nullable()->after('slug');
            $table->boolean('is_enabled')->default(true)->after('name');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('integrations')) {
            return;
        }

        Schema::table('integrations', function (Blueprint $table) {
            $table->dropColumn(['name', 'is_enabled']);
        });

        Schema::table('integrations', function (Blueprint $table) {
            $table->renameColumn('slug', 'provider');
        });

        Schema::rename('integrations', 'integration_settings');
    }
};
