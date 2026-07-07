<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Tenants\Pages\EditTenant;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TenantManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_company_admin_cannot_change_employee_license_limit_from_filament(): void
    {
        $tenant = Tenant::factory()->create([
            'employee_license_limit' => 10,
        ]);

        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $companyAdmin->assignRole('company-admin');

        $this->actingAs($companyAdmin)
            ->get('/admin/tenants/'.$tenant->getKey().'/edit')
            ->assertForbidden();

        $this->assertSame(10, $tenant->refresh()->employee_license_limit);
    }

    public function test_super_admin_can_change_employee_license_limit_from_filament(): void
    {
        $tenant = Tenant::factory()->create([
            'employee_license_limit' => 10,
        ]);

        $principalTenant = Tenant::ensurePrincipalTenant();

        $this->createRoles();

        $superAdmin = User::factory()->create([
            'tenant_id' => $principalTenant->getKey(),
        ]);
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin);

        Livewire::test(EditTenant::class, ['record' => $tenant->getKey()])
            ->fillForm([
                'name' => $tenant->name,
                'status' => $tenant->status,
                'locale' => $tenant->locale,
                'timezone' => $tenant->timezone,
                'employee_license_limit' => 50,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertSame(50, $tenant->refresh()->employee_license_limit);
    }

    private function createRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
