<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('email');
            $table->string('country')->nullable()->after('phone');
            $table->string('photo')->nullable()->after('country');
            $table->string('agent_document')->nullable()->after('photo');
        });

        foreach (DB::table('users')->whereNull('username')->cursor() as $row) {
            $base = Str::slug(Str::before($row->email, '@')) ?: 'user';
            $candidate = $base;
            $n = 0;
            while (DB::table('users')->where('username', $candidate)->exists()) {
                $n++;
                $candidate = $base.'-'.$n;
            }
            DB::table('users')->where('id', $row->id)->update(['username' => $candidate]);
        }

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('agency_code', 40)->nullable()->unique()->after('slug');
            $table->string('agent_code', 40)->nullable()->unique()->after('agency_code');
            $table->foreignId('assigned_by')->nullable()->after('phone')->constrained('users')->nullOnDelete();
            $table->string('office_type', 32)->default('b2b_agent')->after('assigned_by');
            $table->foreignId('debtor_type_id')->nullable()->after('office_type')->constrained('debtor_types')->nullOnDelete();
            $table->string('tax_number')->nullable()->after('debtor_type_id');
            $table->string('reg_number')->nullable()->after('tax_number');
            $table->string('address_country')->nullable()->after('reg_number');
            $table->string('address_state')->nullable()->after('address_country');
            $table->string('address_city')->nullable()->after('address_state');
            $table->text('address_line')->nullable()->after('address_city');
            $table->json('documents')->nullable()->after('address_line');
            $table->string('logo')->nullable()->after('documents');
            $table->string('currency', 3)->default('USD')->after('logo');
            $table->foreignId('parent_tenant_id')->nullable()->after('currency')->constrained('tenants')->nullOnDelete();
        });

        foreach (DB::table('tenants')->whereNull('agency_code')->cursor() as $tenant) {
            $agency = 'AGY-'.strtoupper(Str::random(6));
            while (DB::table('tenants')->where('agency_code', $agency)->exists()) {
                $agency = 'AGY-'.strtoupper(Str::random(6));
            }
            $agent = 'AGT-'.strtoupper(Str::random(6));
            while (DB::table('tenants')->where('agent_code', $agent)->exists()) {
                $agent = 'AGT-'.strtoupper(Str::random(6));
            }
            DB::table('tenants')->where('id', $tenant->id)->update([
                'agency_code' => $agency,
                'agent_code' => $agent,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_tenant_id');
            $table->dropConstrainedForeignId('debtor_type_id');
            $table->dropConstrainedForeignId('assigned_by');
            $table->dropColumn([
                'agency_code',
                'agent_code',
                'office_type',
                'tax_number',
                'reg_number',
                'address_country',
                'address_state',
                'address_city',
                'address_line',
                'documents',
                'logo',
                'currency',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'country', 'photo', 'agent_document']);
        });
    }
};
