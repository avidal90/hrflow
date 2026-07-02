<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $principalTenant = Tenant::ensurePrincipalTenant();

        User::query()
            ->whereHas('roles', function ($query): void {
                $query->where('name', 'super-admin');
            })
            ->update([
                'tenant_id' => $principalTenant->getKey(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertimos el movimiento del superadministrador para evitar dejar el sistema sin tenant principal.
    }
};
