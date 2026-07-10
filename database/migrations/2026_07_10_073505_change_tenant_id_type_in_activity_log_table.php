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
    // tenant_id is created as string in 2026_07_10_072526_add_tenant_id_and_ip_address_to_activity_log_table.php.
}

public function down(): void
{
    // No-op.
}
};
