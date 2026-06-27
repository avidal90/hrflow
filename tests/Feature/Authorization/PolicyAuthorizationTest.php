<?php

namespace Tests\Feature\Authorization;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_company_admin_and_hr_have_expected_tenant_permissions(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();

        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $companyAdmin->assignRole('company-admin');

        $hrUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $hrUser->assignRole('hr');

        $department = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $otherDepartment = Department::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
        ]);

        $employee = Employee::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $otherEmployee = Employee::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
        ]);

        $this->assertTrue($companyAdmin->can('viewAny', Tenant::class));
        $this->assertTrue($companyAdmin->can('view', $tenant));
        $this->assertFalse($companyAdmin->can('view', $otherTenant));
        $this->assertTrue($companyAdmin->can('update', $tenant));
        $this->assertFalse($companyAdmin->can('update', $otherTenant));
        $this->assertFalse($companyAdmin->can('create', Tenant::class));
        $this->assertFalse($companyAdmin->can('delete', $tenant));

        $this->assertTrue($hrUser->can('viewAny', Tenant::class));
        $this->assertTrue($hrUser->can('view', $tenant));
        $this->assertFalse($hrUser->can('update', $tenant));

        $this->assertTrue($companyAdmin->can('viewAny', Department::class));
        $this->assertTrue($companyAdmin->can('create', Department::class));
        $this->assertTrue($companyAdmin->can('view', $department));
        $this->assertFalse($companyAdmin->can('view', $otherDepartment));
        $this->assertTrue($companyAdmin->can('delete', $department));
        $this->assertFalse($companyAdmin->can('delete', $otherDepartment));

        $this->assertTrue($hrUser->can('viewAny', Department::class));
        $this->assertTrue($hrUser->can('create', Department::class));
        $this->assertTrue($hrUser->can('update', $department));
        $this->assertFalse($hrUser->can('delete', $department));

        $this->assertTrue($companyAdmin->can('viewAny', Employee::class));
        $this->assertTrue($companyAdmin->can('create', Employee::class));
        $this->assertTrue($companyAdmin->can('view', $employee));
        $this->assertFalse($companyAdmin->can('view', $otherEmployee));
        $this->assertTrue($companyAdmin->can('update', $employee));
        $this->assertFalse($companyAdmin->can('update', $otherEmployee));
        $this->assertTrue($companyAdmin->can('delete', $employee));

        $this->assertTrue($hrUser->can('viewAny', Employee::class));
        $this->assertTrue($hrUser->can('create', Employee::class));
        $this->assertTrue($hrUser->can('update', $employee));
        $this->assertFalse($hrUser->can('delete', $employee));
    }

    public function test_department_manager_is_limited_to_managed_departments_and_employees(): void
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

        $managerEmployee = Employee::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $managerUser->getKey(),
            'department_id' => $managedDepartment->getKey(),
        ]);

        $managedDepartment->update([
            'manager_employee_id' => $managerEmployee->getKey(),
        ]);

        $managedEmployee = Employee::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $managedDepartment->getKey(),
        ]);

        $unmanagedEmployee = Employee::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $otherDepartment->getKey(),
        ]);

        $externalDepartment = Department::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
        ]);

        $externalEmployee = Employee::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
        ]);

        $this->assertFalse($managerUser->can('viewAny', Tenant::class));
        $this->assertFalse($managerUser->can('view', $tenant));
        $this->assertTrue($managerUser->can('viewAny', Department::class));
        $this->assertFalse($managerUser->can('create', Department::class));
        $this->assertTrue($managerUser->can('view', $managedDepartment));
        $this->assertFalse($managerUser->can('view', $otherDepartment));
        $this->assertFalse($managerUser->can('view', $externalDepartment));
        $this->assertTrue($managerUser->can('update', $managedDepartment));
        $this->assertFalse($managerUser->can('update', $otherDepartment));
        $this->assertFalse($managerUser->can('delete', $managedDepartment));

        $this->assertTrue($managerUser->can('viewAny', Employee::class));
        $this->assertFalse($managerUser->can('create', Employee::class));
        $this->assertTrue($managerUser->can('view', $managedEmployee));
        $this->assertFalse($managerUser->can('view', $unmanagedEmployee));
        $this->assertFalse($managerUser->can('view', $externalEmployee));
        $this->assertTrue($managerUser->can('update', $managedEmployee));
        $this->assertFalse($managerUser->can('update', $unmanagedEmployee));
        $this->assertFalse($managerUser->can('delete', $managedEmployee));
    }

    public function test_tenant_scope_filters_queries_and_assigns_tenant_id_automatically(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();

        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $companyAdmin->assignRole('company-admin');

        $ownDepartment = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $otherDepartment = Department::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
        ]);

        $ownEmployee = Employee::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $otherEmployee = Employee::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
        ]);

        $this->actingAs($companyAdmin);

        $visibleDepartmentIds = Department::query()->forCurrentTenant()->pluck('id')->all();
        $visibleDepartmentTenantIds = Department::query()->forCurrentTenant()->pluck('tenant_id')->unique()->values()->all();
        $visibleEmployeeIds = Employee::query()->forCurrentTenant()->pluck('id')->all();
        $visibleEmployeeTenantIds = Employee::query()->forCurrentTenant()->pluck('tenant_id')->unique()->values()->all();

        $this->assertContains($ownDepartment->getKey(), $visibleDepartmentIds);
        $this->assertNotContains($otherDepartment->getKey(), $visibleDepartmentIds);
        $this->assertSame([(string) $tenant->getKey()], array_map('strval', $visibleDepartmentTenantIds));

        $this->assertContains($ownEmployee->getKey(), $visibleEmployeeIds);
        $this->assertNotContains($otherEmployee->getKey(), $visibleEmployeeIds);
        $this->assertSame([(string) $tenant->getKey()], array_map('strval', $visibleEmployeeTenantIds));

        $createdDepartment = Department::create([
            'name' => 'People Operations',
        ]);

        $this->assertSame((string) $tenant->getKey(), (string) $createdDepartment->tenant_id);

        $superAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin);

        $superAdminDepartmentTenantIds = Department::query()->forCurrentTenant()->pluck('tenant_id')->unique()->values()->all();
        $superAdminEmployeeTenantIds = Employee::query()->forCurrentTenant()->pluck('tenant_id')->unique()->values()->all();

        $this->assertContains($otherDepartment->getKey(), Department::query()->forCurrentTenant()->pluck('id')->all());
        $this->assertContains($otherEmployee->getKey(), Employee::query()->forCurrentTenant()->pluck('id')->all());
        $this->assertContains((string) $tenant->getKey(), array_map('strval', $superAdminDepartmentTenantIds));
        $this->assertContains((string) $otherTenant->getKey(), array_map('strval', $superAdminDepartmentTenantIds));
        $this->assertContains((string) $tenant->getKey(), array_map('strval', $superAdminEmployeeTenantIds));
        $this->assertContains((string) $otherTenant->getKey(), array_map('strval', $superAdminEmployeeTenantIds));
    }

    public function test_super_admin_bypasses_tenant_restrictions(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();

        $this->createRoles();

        $superAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $superAdmin->assignRole('super-admin');

        $department = Department::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
        ]);

        $employee = Employee::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
        ]);

        $this->assertTrue($superAdmin->can('create', Tenant::class));
        $this->assertTrue($superAdmin->can('delete', $otherTenant));
        $this->assertTrue($superAdmin->can('view', $otherTenant));
        $this->assertTrue($superAdmin->can('delete', $department));
        $this->assertTrue($superAdmin->can('update', $employee));
    }

    private function createRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
