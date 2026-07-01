<?php

namespace Tests\Feature\Api;

use App\Models\Department;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_hr_can_create_user_employee_with_valid_department(): void
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

        $response = $this->postJson('/api/users', [
            'name' => 'Ana Lopez',
            'email' => 'ana.lopez@example.test',
            'password' => 'password123',
            'department_id' => $department->getKey(),
            'employee_code' => 'EMP-1000',
            'hire_date' => '2026-01-01',
            'employment_status' => 'active',
            'job_title' => 'HR Specialist',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('employee_code', 'EMP-1000')
            ->assertJsonPath('name', 'Ana Lopez')
            ->assertJsonPath('tenant_id', (string) $tenant->getKey());

        $this->assertDatabaseHas('users', [
            'tenant_id' => (string) $tenant->getKey(),
            'employee_code' => 'EMP-1000',
            'email' => 'ana.lopez@example.test',
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

        $response = $this->postJson('/api/users', [
            'name' => 'Juan Perez',
            'email' => 'juan.perez@example.test',
            'password' => 'password123',
            'department_id' => $foreignDepartment->getKey(),
            'employee_code' => 'EMP-2000',
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

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $department->getKey(),
        ]);

        Sanctum::actingAs($hrUser);

        $response = $this->putJson('/api/users/'.$employee->getKey(), [
            'tenant_id' => (string) $tenant->getKey(),
            'department_id' => $employee->department_id,
            'employee_code' => $employee->employee_code,
            'name' => 'Carmen Ruiz',
            'email' => $employee->email,
            'hire_date' => $employee->hire_date?->format('Y-m-d'),
            'employment_status' => 'inactive',
            'job_title' => 'Analyst',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('name', 'Carmen Ruiz')
            ->assertJsonPath('employment_status', 'inactive');
    }

    public function test_employee_creation_is_rejected_when_tenant_reaches_license_limit(): void
    {
        $tenant = Tenant::factory()->create([
            'employee_license_limit' => 2,
        ]);

        $this->seedRoles();

        $hrUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $hrUser->assignRole('hr');

        $department = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $department->getKey(),
        ]);

        Sanctum::actingAs($hrUser);

        $response = $this->postJson('/api/users', [
            'name' => 'Lara Martin',
            'email' => 'lara.martin@example.test',
            'password' => 'password123',
            'department_id' => $department->getKey(),
            'employee_code' => 'EMP-9000',
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

        User::factory()->count(3)->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $department->getKey(),
        ]);

        Sanctum::actingAs($hrUser);

        $response = $this->postJson('/api/users', [
            'name' => 'Nora Sanchez',
            'email' => 'nora.sanchez@example.test',
            'password' => 'password123',
            'department_id' => $department->getKey(),
            'employee_code' => 'EMP-9001',
            'hire_date' => '2026-01-01',
            'employment_status' => 'active',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('employee_code', 'EMP-9001')
            ->assertJsonPath('name', 'Nora Sanchez')
            ->assertJsonPath('tenant_id', (string) $tenant->getKey());
    }

    private function seedRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
