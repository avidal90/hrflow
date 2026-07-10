<?php

namespace Tests\Feature;

use App\Filament\Resources\Departments\Pages\CreateDepartment;
use App\Models\Department;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DepartmentFormTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_manager_selector_only_shows_users_with_department_manager_role(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $manager = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $manager->assignRole('department-manager');

        $employee = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $employee->assignRole('employee');

        $hrUser = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $hrUser->assignRole('hr');

        $this->actingAs($admin);

        $options = User::query()
            ->where('tenant_id', $tenant->getKey())
            ->whereHas('roles', fn ($q) => $q->where('name', 'department-manager'))
            ->pluck('id')
            ->all();

        $this->assertContains($manager->getKey(), $options);
        $this->assertNotContains($employee->getKey(), $options);
        $this->assertNotContains($hrUser->getKey(), $options);
        $this->assertNotContains($admin->getKey(), $options);
    }

    public function test_company_admin_can_create_department_with_a_manager(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $manager = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $manager->assignRole('department-manager');

        $this->actingAs($admin);

        Livewire::test(CreateDepartment::class)
            ->fillForm([
                'tenant_id' => $tenant->getKey(),
                'name' => 'Ingeniería',
                'manager_user_id' => $manager->getKey(),
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $department = Department::query()->firstOrFail();

        $this->assertSame((string) $manager->getKey(), (string) $department->manager_user_id);
    }

    public function test_company_admin_can_create_department_without_a_manager(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $this->actingAs($admin);

        Livewire::test(CreateDepartment::class)
            ->fillForm([
                'tenant_id' => $tenant->getKey(),
                'name' => 'Operaciones',
                'manager_user_id' => null,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(Department::class, [
            'name' => 'Operaciones',
            'manager_user_id' => null,
        ]);
    }

    private function createRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
