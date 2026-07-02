<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Departments\Pages\ViewDepartment;
use App\Filament\Resources\Departments\RelationManagers\UsersRelationManager;
use App\Models\Department;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DepartmentMembersRelationManagerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_company_admin_sees_department_members(): void
    {
        $this->createRoles();
        $tenant = Tenant::factory()->create();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $department = Department::factory()->create(['tenant_id' => $tenant->getKey()]);

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $department->getKey(),
        ]);
        $employee->assignRole('employee');

        $this->actingAs($admin);

        Livewire::test(UsersRelationManager::class, [
            'ownerRecord' => $department,
            'pageClass' => ViewDepartment::class,
        ])
            ->assertCanSeeTableRecords([$employee]);
    }

    public function test_members_from_other_departments_are_not_visible(): void
    {
        $this->createRoles();
        $tenant = Tenant::factory()->create();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $departmentA = Department::factory()->create(['tenant_id' => $tenant->getKey()]);
        $departmentB = Department::factory()->create(['tenant_id' => $tenant->getKey()]);

        $memberA = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $departmentA->getKey(),
        ]);
        $memberA->assignRole('employee');

        $memberB = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $departmentB->getKey(),
        ]);
        $memberB->assignRole('employee');

        $this->actingAs($admin);

        Livewire::test(UsersRelationManager::class, [
            'ownerRecord' => $departmentA,
            'pageClass' => ViewDepartment::class,
        ])
            ->assertCanSeeTableRecords([$memberA])
            ->assertCanNotSeeTableRecords([$memberB]);
    }

    public function test_department_manager_cannot_manage_members(): void
    {
        $this->createRoles();
        $tenant = Tenant::factory()->create();

        $manager = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $manager->assignRole('department-manager');

        $department = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'manager_user_id' => $manager->getKey(),
        ]);

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $department->getKey(),
        ]);
        $employee->assignRole('employee');

        $this->actingAs($manager);

        Livewire::test(UsersRelationManager::class, [
            'ownerRecord' => $department,
            'pageClass' => ViewDepartment::class,
        ])
            ->assertCanSeeTableRecords([$employee])
            ->assertTableActionHidden('dissociate', $employee);
    }

    public function test_company_admin_can_dissociate_member(): void
    {
        $this->createRoles();
        $tenant = Tenant::factory()->create();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $department = Department::factory()->create(['tenant_id' => $tenant->getKey()]);

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $department->getKey(),
        ]);
        $employee->assignRole('employee');

        $this->actingAs($admin);

        Livewire::test(UsersRelationManager::class, [
            'ownerRecord' => $department,
            'pageClass' => ViewDepartment::class,
        ])
            ->callTableAction('dissociate', $employee)
            ->assertNotified();

        $this->assertNull($employee->fresh()->department_id);
    }

    private function createRoles(): void
    {
        foreach (['company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
