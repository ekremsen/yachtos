<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_memberships', function (Blueprint $table) {
            $table->unique(['id', 'tenant_id', 'user_id'], 'tenant_memberships_identity');
        });

        Schema::create('yachts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('home_port')->nullable();
            $table->enum('status', ['active', 'inactive', 'archived'])->default('inactive');
            $table->timestamps();
            $table->unique(['id', 'tenant_id'], 'yachts_tenant_identity');
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('yacht_memberships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('yacht_id');
            $table->uuid('user_id');
            $table->uuid('tenant_membership_id');
            $table->enum('role', ['owner', 'captain', 'crew', 'engineer']);
            $table->enum('status', ['active', 'inactive', 'archived'])->default('inactive');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->foreign(['yacht_id', 'tenant_id'], 'yacht_memberships_yacht_boundary')
                ->references(['id', 'tenant_id'])->on('yachts')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tenant_membership_id', 'tenant_id', 'user_id'], 'yacht_memberships_user_boundary')
                ->references(['id', 'tenant_id', 'user_id'])->on('tenant_memberships')->restrictOnDelete()->restrictOnUpdate();
            $table->index(['tenant_id', 'user_id', 'status'], 'yacht_memberships_access_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yacht_memberships');
        Schema::dropIfExists('yachts');
        Schema::table('tenant_memberships', fn (Blueprint $table) => $table->dropUnique('tenant_memberships_identity'));
    }
};
