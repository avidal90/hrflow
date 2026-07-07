<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DepartmentApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_employee_cannot_list_departments_in_api_index(): void
    {
        $tenant = Tenant::factory()->create();

        $this->seedRoles();

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $employee->assignRole('employee');

        Sanctum::actingAs($employee);

        $this->getJson('/api/departments')->assertForbidden();
    }

    public function test_company_admin_can_create_department_using_form_request_defaults(): void
    {
        $tenant = Tenant::factory()->create();

        $this->seedRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $companyAdmin->assignRole('company-admin');

        Sanctum::actingAs($companyAdmin);

        $response = $this->postJson('/api/departments', [
            'name' => 'People Ops',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('name', 'People Ops')
            ->assertJsonPath('tenant_id', (string) $tenant->getKey());

        $this->assertDatabaseHas('departments', [
            'name' => 'People Ops',
            'tenant_id' => (string) $tenant->getKey(),
        ]);
    }

    public function test_department_creation_rejects_manager_from_other_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();

        $this->seedRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $companyAdmin->assignRole('company-admin');

        $foreignManager = User::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
        ]);

        Sanctum::actingAs($companyAdmin);

        $response = $this->postJson('/api/departments', [
            'name' => 'Legal',
            'manager_user_id' => $foreignManager->getKey(),
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['manager_user_id']);
    }

    public function test_department_manager_cannot_create_department(): void
    {
        $tenant = Tenant::factory()->create();

        $this->seedRoles();

        $managerUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $managerUser->assignRole('department-manager');

        Sanctum::actingAs($managerUser);

        $response = $this->postJson('/api/departments', [
            'name' => 'Finance',
        ]);

        $response->assertForbidden();
    }

    private function seedRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
