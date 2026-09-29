<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crew_members', function (Blueprint $table) {
            $table->unique(['id', 'tenant_id', 'yacht_id'], 'crew_members_yacht_identity');
        });
    }

    public function down(): void
    {
        Schema::table('crew_members', function (Blueprint $table) {
            $table->dropUnique('crew_members_yacht_identity');
        });
    }
};
