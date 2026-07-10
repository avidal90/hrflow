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
Schema::connection(config('activitylog.database_connection'))
    ->table(config('activitylog.table_name'), function (Blueprint $table) {
        $table->string('tenant_id')->nullable()->index()->after('batch_uuid');
        $table->string('ip_address', 45)->nullable()->after('tenant_id');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'ip_address']);
        });
    }
};
