<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Departments\DepartmentResource;
use App\Filament\Resources\Tenants\TenantResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Department;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ResourceQueryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_company_admin_only_sees_own_tenant_in_tenant_resource(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();

        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $companyAdmin->assignRole('company-admin');

        $this->actingAs($companyAdmin);

        $this->assertSame(
            [(string) $tenant->getKey()],
            array_map('strval', TenantResource::getEloquentQuery()->pluck('id')->all()),
        );
        $this->assertNotContains((string) $otherTenant->getKey(), array_map('strval', TenantResource::getEloquentQuery()->pluck('id')->all()));
    }

    public function test_department_manager_resource_queries_are_limited_to_managed_scope(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();

        $this->createRoles();

        $managerUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $managerUser->assignRole('department-manager');

        $managedDepartment = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $otherDepartment = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $managerUser->update([
            'department_id' => $managedDepartment->getKey(),
        ]);

        $managedDepartment->update([
            'manager_user_id' => $managerUser->getKey(),
        ]);

        $managedEmployee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $managedDepartment->getKey(),
        ]);

        $unmanagedEmployee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $otherDepartment->getKey(),
        ]);

        Department::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
        ]);

        User::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
        ]);

        $this->actingAs($managerUser);

        $this->assertSame(
            [$managedDepartment->getKey()],
            DepartmentResource::getEloquentQuery()->pluck('id')->all(),
        );

        $visibleEmployeeIds = UserResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($managerUser->getKey(), $visibleEmployeeIds);
        $this->assertContains($managedEmployee->getKey(), $visibleEmployeeIds);
        $this->assertNotContains($unmanagedEmployee->getKey(), $visibleEmployeeIds);
    }

    private function createRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
