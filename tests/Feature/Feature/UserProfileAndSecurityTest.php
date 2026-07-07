<?php

namespace Tests\Feature\Feature;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Department;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class UserProfileAndSecurityTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_company_admin_can_reset_a_user_password_from_filament_and_notify_them(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $companyAdmin->assignRole('company-admin');

        $department = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $department->getKey(),
        ]);
        $employee->assignRole('employee');

        $this->actingAs($companyAdmin);

        Livewire::test(EditUser::class, ['record' => $employee->getKey()])
            ->callAction('resetPassword', [
                'password' => 'NuevaSegura1!',
                'password_confirmation' => 'NuevaSegura1!',
            ])
            ->assertNotified();

        $this->assertTrue(Hash::check('NuevaSegura1!', $employee->refresh()->password));
        $this->assertSame(1, $employee->notifications()->count());
    }

    public function test_company_admin_can_change_a_users_role_from_filament_edit_page(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $companyAdmin->assignRole('company-admin');

        $department = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $department->getKey(),
        ]);
        $employee->assignRole('employee');

        $this->actingAs($companyAdmin);

        Livewire::test(EditUser::class, ['record' => $employee->getKey()])
            ->fillForm([
                'tenant_id' => (string) $tenant->getKey(),
                'name' => $employee->name,
                'email' => $employee->email,
                'department_id' => $department->getKey(),
                'role_name' => 'hr',
                'employee_code' => $employee->employee_code,
                'hire_date' => $employee->hire_date?->format('Y-m-d'),
                'employment_status' => $employee->employment_status,
                'annual_vacation_days' => 30,
                'job_title' => $employee->job_title,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $employee->refresh();

        $this->assertTrue($employee->hasRole('hr'));
        $this->assertFalse($employee->hasRole('employee'));
        $this->assertSame(30, $employee->annual_vacation_days);
    }

    public function test_company_admin_cannot_manage_their_own_role(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $companyAdmin->assignRole('company-admin');

        $department = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $companyAdmin->update([
            'department_id' => $department->getKey(),
        ]);

        $this->assertFalse(User::canManageRoleAssignments($companyAdmin, $companyAdmin));
        $this->assertTrue($companyAdmin->hasRole('company-admin'));
    }

    public function test_super_admin_cannot_manage_their_own_role(): void
    {
        $principalTenant = Tenant::ensurePrincipalTenant();

        $this->createRoles();

        $superAdmin = User::factory()->create([
            'tenant_id' => $principalTenant->getKey(),
        ]);
        $superAdmin->assignRole('super-admin');

        $department = Department::factory()->create([
            'tenant_id' => $principalTenant->getKey(),
        ]);

        $superAdmin->update([
            'department_id' => $department->getKey(),
        ]);

        $this->assertFalse(User::canManageRoleAssignments($superAdmin, $superAdmin));
        $this->assertTrue($superAdmin->hasRole('super-admin'));
    }

    public function test_super_admin_can_promote_another_user_to_super_admin(): void
    {
        $principalTenant = Tenant::ensurePrincipalTenant();

        $this->createRoles();

        $superAdmin = User::factory()->create([
            'tenant_id' => $principalTenant->getKey(),
        ]);
        $superAdmin->assignRole('super-admin');

        $department = Department::factory()->create([
            'tenant_id' => $principalTenant->getKey(),
        ]);

        $candidate = User::factory()->create([
            'tenant_id' => $principalTenant->getKey(),
            'department_id' => $department->getKey(),
        ]);
        $candidate->assignRole('employee');

        $this->actingAs($superAdmin);

        Livewire::test(EditUser::class, ['record' => $candidate->getKey()])
            ->fillForm([
                'tenant_id' => (string) $principalTenant->getKey(),
                'name' => $candidate->name,
                'email' => $candidate->email,
                'department_id' => $department->getKey(),
                'role_name' => 'super-admin',
                'employee_code' => $candidate->employee_code,
                'hire_date' => $candidate->hire_date?->format('Y-m-d'),
                'employment_status' => $candidate->employment_status,
                'annual_vacation_days' => 26,
                'job_title' => $candidate->job_title,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertTrue($candidate->refresh()->hasRole('super-admin'));
    }

    public function test_api_rejects_a_weak_password_when_creating_users(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $companyAdmin->assignRole('company-admin');

        $department = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $response = $this->actingAs($companyAdmin)->postJson('/api/users', [
            'name' => 'Ana Debil',
            'email' => 'ana.debil@example.test',
            'password' => 'password',
            'department_id' => $department->getKey(),
            'employee_code' => 'EMP-WEAK',
            'hire_date' => '2026-01-01',
            'employment_status' => 'active',
            'annual_vacation_days' => 23,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['password']);
    }

    public function test_employee_can_manage_avatar_and_password_from_their_profile_page(): void
    {
        Storage::fake('avatars');

        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $employee->assignRole('employee');

        $this->actingAs($employee);

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Mi perfil')
            ->assertSee($employee->email);

        $this->post(route('profile.avatar.update'), [
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 300, 300),
        ])->assertRedirect(route('profile.show'));

        $employee->refresh();

        $this->assertNotNull($employee->avatar_path);
        $this->assertTrue(Storage::disk('avatars')->exists($employee->avatar_path));

        $this->put(route('profile.password.update'), [
            'current_password' => 'password',
            'password' => 'PerfilSegura1!',
            'password_confirmation' => 'PerfilSegura1!',
        ])->assertRedirect(route('profile.show'));

        $this->assertTrue(Hash::check('PerfilSegura1!', $employee->refresh()->password));
    }

    private function createRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
