<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('yacht_id');
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->enum('type', ['preventive', 'corrective', 'inspection']);
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->date('due_date');
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();
            $table->unique(['id', 'tenant_id', 'yacht_id'], 'maintenance_tasks_yacht_identity');
            $table->foreign(['yacht_id', 'tenant_id'], 'maintenance_tasks_yacht_boundary')
                ->references(['id', 'tenant_id'])->on('yachts')->restrictOnDelete()->restrictOnUpdate();
            $table->index(['yacht_id', 'status', 'due_date'], 'maintenance_tasks_schedule');
            $table->index(['yacht_id', 'priority', 'due_date'], 'maintenance_tasks_priority_due');
        });

        Schema::create('maintenance_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('yacht_id');
            $table->uuid('maintenance_task_id');
            $table->uuid('crew_member_id');
            $table->timestampsTz();
            $table->unique(['maintenance_task_id', 'crew_member_id'], 'maintenance_assignment_unique_pair');
            $table->foreign(['maintenance_task_id', 'tenant_id', 'yacht_id'], 'maintenance_assignment_task_boundary')
                ->references(['id', 'tenant_id', 'yacht_id'])->on('maintenance_tasks')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['crew_member_id', 'tenant_id', 'yacht_id'], 'maintenance_assignment_crew_boundary')
                ->references(['id', 'tenant_id', 'yacht_id'])->on('crew_members')->restrictOnDelete()->restrictOnUpdate();
            $table->index(['yacht_id', 'crew_member_id'], 'maintenance_assignment_crew_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_assignments');
        Schema::dropIfExists('maintenance_tasks');
    }
};
