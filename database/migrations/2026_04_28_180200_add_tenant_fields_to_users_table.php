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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('role_id')->constrained('tenants')->nullOnDelete();
            $table->string('designation')->nullable()->after('tenant_id');
            $table->string('user_type')->default('public')->after('designation');
            $table->boolean('is_active')->default(true)->after('user_type');
            $table->index(['tenant_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_tenant_id_role_id_index');
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn(['designation', 'user_type', 'is_active']);
        });
    }
};
