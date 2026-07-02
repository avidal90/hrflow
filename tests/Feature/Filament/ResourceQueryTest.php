<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Departments\DepartmentResource;
use App\Filament\Resources\Documents\DocumentResource;
use App\Filament\Resources\LeaveRequests\LeaveRequestResource;
use App\Filament\Resources\Tenants\TenantResource;
use App\Filament\Resources\TimeEntries\TimeEntryResource;
use App\Filament\Resources\Turnos\TurnoResource;
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

    public function test_hr_cannot_see_tenant_resource_records(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $hrUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $hrUser->assignRole('hr');

        $this->actingAs($hrUser);

        $this->assertSame([], TenantResource::getEloquentQuery()->pluck('id')->all());
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

    public function test_tenant_admin_does_not_see_super_admins_in_user_resource(): void
    {
        $tenant = Tenant::factory()->create();
        $principalTenant = Tenant::ensurePrincipalTenant();

        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $companyAdmin->assignRole('company-admin');

        $tenantEmployee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $superAdmin = User::factory()->create([
            'tenant_id' => $principalTenant->getKey(),
        ]);
        $superAdmin->assignRole('super-admin');

        $this->actingAs($companyAdmin);

        $visibleEmployeeIds = UserResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($tenantEmployee->getKey(), $visibleEmployeeIds);
        $this->assertNotContains($superAdmin->getKey(), $visibleEmployeeIds);
    }

    public function test_hr_navigation_only_includes_operational_resources(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $hrUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $hrUser->assignRole('hr');

        $this->actingAs($hrUser);

        $this->assertFalse(TenantResource::shouldRegisterNavigation());
        $this->assertTrue(UserResource::shouldRegisterNavigation());
        $this->assertTrue(DepartmentResource::shouldRegisterNavigation());
        $this->assertTrue(DocumentResource::shouldRegisterNavigation());
        $this->assertTrue(LeaveRequestResource::shouldRegisterNavigation());
        $this->assertTrue(TimeEntryResource::shouldRegisterNavigation());
        $this->assertTrue(TurnoResource::shouldRegisterNavigation());
    }

    public function test_department_manager_navigation_excludes_tenants_users_and_documents(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $managerUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $managerUser->assignRole('department-manager');

        $this->actingAs($managerUser);

        $this->assertFalse(TenantResource::shouldRegisterNavigation());
        $this->assertTrue(UserResource::shouldRegisterNavigation());
        $this->assertFalse(DocumentResource::shouldRegisterNavigation());
        $this->assertTrue(DepartmentResource::shouldRegisterNavigation());
        $this->assertTrue(LeaveRequestResource::shouldRegisterNavigation());
        $this->assertTrue(TimeEntryResource::shouldRegisterNavigation());
        $this->assertTrue(TurnoResource::shouldRegisterNavigation());
    }

    private function createRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
