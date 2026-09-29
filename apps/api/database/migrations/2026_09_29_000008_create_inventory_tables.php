<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('yacht_id');
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->string('category', 80);
            $table->enum('unit', ['piece', 'liter', 'kilogram', 'meter', 'pack']);
            $table->decimal('current_quantity', 12, 3)->default(0);
            $table->decimal('minimum_quantity', 12, 3)->nullable();
            $table->string('storage_location', 120)->nullable();
            $table->timestampsTz();
            $table->unique(['id', 'tenant_id', 'yacht_id'], 'inventory_items_yacht_identity');
            $table->foreign(['yacht_id', 'tenant_id'], 'inventory_items_yacht_boundary')
                ->references(['id', 'tenant_id'])->on('yachts')->restrictOnDelete()->restrictOnUpdate();
            $table->index(['yacht_id', 'name'], 'inventory_items_yacht_name');
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('yacht_id');
            $table->uuid('inventory_item_id');
            $table->uuid('performed_by_user_id');
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->decimal('quantity', 12, 3);
            $table->decimal('balance_after', 12, 3);
            $table->string('reason', 180);
            $table->text('note')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->unique(['id', 'tenant_id', 'yacht_id'], 'stock_movements_yacht_identity');
            $table->foreign(['inventory_item_id', 'tenant_id', 'yacht_id'], 'stock_movements_item_boundary')
                ->references(['id', 'tenant_id', 'yacht_id'])->on('inventory_items')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('performed_by_user_id', 'stock_movements_actor_boundary')
                ->references('id')->on('users')->restrictOnDelete()->restrictOnUpdate();
            $table->index(['inventory_item_id', 'created_at'], 'stock_movements_item_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_items');
    }
};
