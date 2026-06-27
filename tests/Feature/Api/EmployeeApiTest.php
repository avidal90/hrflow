<?php

namespace Tests\Feature\Api;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_hr_can_create_employee_with_valid_department_and_optional_user(): void
    {
        $tenant = Tenant::factory()->create();

        $this->seedRoles();

        $hrUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $hrUser->assignRole('hr');

        $department = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        Sanctum::actingAs($hrUser);

        $response = $this->postJson('/api/employees', [
            'department_id' => $department->getKey(),
            'employee_code' => 'EMP-1000',
            'first_name' => 'Ana',
            'last_name' => 'Lopez',
            'hire_date' => '2026-01-01',
            'employment_status' => 'active',
            'job_title' => 'HR Specialist',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('employee_code', 'EMP-1000')
            ->assertJsonPath('tenant_id', (string) $tenant->getKey());

        $this->assertDatabaseHas('employees', [
            'tenant_id' => (string) $tenant->getKey(),
            'employee_code' => 'EMP-1000',
        ]);
    }

    public function test_employee_creation_rejects_department_from_other_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();

        $this->seedRoles();

        $hrUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $hrUser->assignRole('hr');

        $foreignDepartment = Department::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
        ]);

        Sanctum::actingAs($hrUser);

        $response = $this->postJson('/api/employees', [
            'department_id' => $foreignDepartment->getKey(),
            'employee_code' => 'EMP-2000',
            'first_name' => 'Juan',
            'last_name' => 'Perez',
            'hire_date' => '2026-01-01',
            'employment_status' => 'active',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['department_id']);
    }

    public function test_hr_can_update_employee_with_form_request_validation(): void
    {
        $tenant = Tenant::factory()->create();

        $this->seedRoles();

        $hrUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $hrUser->assignRole('hr');

        $department = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $employee = Employee::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $department->getKey(),
        ]);

        Sanctum::actingAs($hrUser);

        $response = $this->putJson('/api/employees/'.$employee->getKey(), [
            'tenant_id' => (string) $tenant->getKey(),
            'department_id' => $employee->department_id,
            'employee_code' => $employee->employee_code,
            'first_name' => 'Carmen',
            'last_name' => $employee->last_name,
            'hire_date' => $employee->hire_date?->format('Y-m-d'),
            'employment_status' => 'inactive',
            'job_title' => 'Analyst',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('first_name', 'Carmen')
            ->assertJsonPath('employment_status', 'inactive');
    }

    public function test_employee_creation_is_rejected_when_tenant_reaches_license_limit(): void
    {
        $tenant = Tenant::factory()->create([
            'employee_license_limit' => 1,
        ]);

        $this->seedRoles();

        $hrUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $hrUser->assignRole('hr');

        $department = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        Employee::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $department->getKey(),
        ]);

        Sanctum::actingAs($hrUser);

        $response = $this->postJson('/api/employees', [
            'department_id' => $department->getKey(),
            'employee_code' => 'EMP-9000',
            'first_name' => 'Lara',
            'last_name' => 'Martin',
            'hire_date' => '2026-01-01',
            'employment_status' => 'active',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['tenant_id']);
    }

    public function test_employee_creation_is_allowed_when_tenant_has_unlimited_licenses(): void
    {
        $tenant = Tenant::factory()->create([
            'employee_license_limit' => null,
        ]);

        $this->seedRoles();

        $hrUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $hrUser->assignRole('hr');

        $department = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        Employee::factory()->count(3)->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $department->getKey(),
        ]);

        Sanctum::actingAs($hrUser);

        $response = $this->postJson('/api/employees', [
            'department_id' => $department->getKey(),
            'employee_code' => 'EMP-9001',
            'first_name' => 'Nora',
            'last_name' => 'Sanchez',
            'hire_date' => '2026-01-01',
            'employment_status' => 'active',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('employee_code', 'EMP-9001')
            ->assertJsonPath('tenant_id', (string) $tenant->getKey());
    }

    private function seedRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
