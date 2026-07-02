<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Departments\Pages\ListDepartments;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Filament\Resources\LeaveRequests\Pages\ListLeaveRequests;
use App\Filament\Resources\Tenants\Pages\ListTenants;
use App\Filament\Resources\TimeEntries\Pages\ListTimeEntries;
use App\Filament\Resources\Turnos\Pages\ListTurnos;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Department;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\Turno;
use App\Models\TurnoAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TableFiltersTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_list_pages_expose_useful_demo_filters(): void
    {
        $this->createRoles();

        $superAdmin = User::factory()->create([
            'tenant_id' => Tenant::ensurePrincipalTenant()->getKey(),
        ]);
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin);

        Livewire::test(ListUsers::class)
            ->assertTableFilterExists('tenant_id')
            ->assertTableFilterExists('department_id')
            ->assertTableFilterExists('employment_status')
            ->assertTableFilterExists('role');

        Livewire::test(ListDepartments::class)
            ->assertTableFilterExists('tenant_id')
            ->assertTableFilterExists('manager_user_id');

        Livewire::test(ListTenants::class)
            ->assertTableFilterExists('status')
            ->assertTableFilterExists('locale')
            ->assertTableFilterExists('license_limit_state');

        Livewire::test(ListDocuments::class)
            ->assertTableFilterExists('tenant_id')
            ->assertTableFilterExists('folder')
            ->assertTableFilterExists('user_id')
            ->assertTableFilterExists('department_id')
            ->assertTableFilterExists('is_visible_to_employee');

        Livewire::test(ListLeaveRequests::class)
            ->assertTableFilterExists('tenant_id')
            ->assertTableFilterExists('status')
            ->assertTableFilterExists('request_type')
            ->assertTableFilterExists('department_id');

        Livewire::test(ListTimeEntries::class)
            ->assertTableFilterExists('status')
            ->assertTableFilterExists('department_id')
            ->assertTableFilterExists('open_entries');

        Livewire::test(ListTurnos::class)
            ->assertTableFilterExists('tenant_id')
            ->assertTableFilterExists('assignment_status');
    }

    public function test_user_role_filter_limits_visible_records(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $companyAdmin->assignRole('company-admin');

        $hrUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $hrUser->assignRole('hr');

        $employeeUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $employeeUser->assignRole('employee');

        $this->actingAs($companyAdmin);

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords([$companyAdmin, $hrUser, $employeeUser])
            ->filterTable('role', 'hr')
            ->assertCanSeeTableRecords([$hrUser])
            ->assertCanNotSeeTableRecords([$companyAdmin, $employeeUser]);
    }

    public function test_users_page_renders_with_visible_department_filter_options(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $companyAdmin->assignRole('company-admin');

        $department = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'name' => 'People',
        ]);

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $department->getKey(),
        ]);
        $employee->assignRole('employee');

        $this->actingAs($companyAdmin);

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords([$companyAdmin, $employee])
            ->filterTable('department_id', (string) $department->getKey())
            ->assertCanSeeTableRecords([$employee])
            ->assertCanNotSeeTableRecords([$companyAdmin]);
    }

    public function test_turno_assignment_filter_separates_assigned_and_unassigned_shifts(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $companyAdmin->assignRole('company-admin');

        $employeeUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $employeeUser->assignRole('employee');

        $assignedTurno = Turno::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $unassignedTurno = Turno::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        TurnoAssignment::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'turno_id' => $assignedTurno->getKey(),
            'user_id' => $employeeUser->getKey(),
        ]);

        $this->actingAs($companyAdmin);

        Livewire::test(ListTurnos::class)
            ->assertCanSeeTableRecords([$assignedTurno, $unassignedTurno])
            ->filterTable('assignment_status', 'assigned')
            ->assertCanSeeTableRecords([$assignedTurno])
            ->assertCanNotSeeTableRecords([$unassignedTurno]);
    }

    private function createRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
