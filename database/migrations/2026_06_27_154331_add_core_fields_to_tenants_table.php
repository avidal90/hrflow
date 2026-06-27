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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('status')->default('active')->after('name');
            $table->string('locale')->default('es')->after('status');
            $table->string('timezone')->default('Europe/Madrid')->after('locale');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['name', 'status', 'locale', 'timezone']);
        });
    }
};
