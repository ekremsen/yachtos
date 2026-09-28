<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crew_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('yacht_id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('position', 100)->nullable();
            $table->string('email', 254)->nullable();
            $table->string('phone', 40)->nullable();
            $table->char('nationality', 2)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->foreign(['yacht_id', 'tenant_id'], 'crew_members_yacht_boundary')
                ->references(['id', 'tenant_id'])->on('yachts')->restrictOnDelete()->restrictOnUpdate();
            $table->index(['yacht_id', 'status', 'last_name', 'first_name'], 'crew_members_roster_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crew_members');
    }
};
