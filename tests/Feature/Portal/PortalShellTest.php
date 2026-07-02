<?php

namespace Tests\Feature\Portal;

use App\Enums\DocumentFolder;
use App\Enums\LeaveRequestStatus;
use App\Enums\TimeEntryStatus;
use App\Models\Document;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PortalShellTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_tenant_login_when_opening_the_portal_dashboard(): void
    {
        $tenant = Tenant::factory()->create();

        $this->get($this->portalRoute($tenant, '/dashboard'))
            ->assertRedirect($this->portalRoute($tenant, '/login'));
    }

    public function test_public_access_form_redirects_to_the_requested_portal_login(): void
    {
        $tenant = Tenant::factory()->create([
            'id' => 'northwind-demo',
        ]);

        $this->post('/acceso/portal', [
            'tenant' => $tenant->getKey(),
        ])->assertRedirect($this->portalRoute($tenant, '/login'));
    }

    public function test_employee_can_log_in_from_tenant_portal_and_is_redirected_to_their_dashboard(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'email' => 'empleado.portal@example.test',
        ]);
        $employee->assignRole('employee');

        $this->post($this->portalRoute($tenant, '/login'), [
            'email' => $employee->email,
            'password' => 'password',
        ])->assertRedirect($this->portalRoute($tenant, '/dashboard'));

        $this->get($this->portalRoute($tenant, '/dashboard'))
            ->assertOk()
            ->assertSee('Calendario')
            ->assertSee('Control horario')
            ->assertSee('Solicitudes')
            ->assertSee('Documentacion');
    }

    public function test_invalid_login_shows_a_generic_error_without_revealing_if_the_user_exists(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'email' => 'empleado.portal@example.test',
        ])->assignRole('employee');

        $this->from($this->portalRoute($tenant, '/login'))->post($this->portalRoute($tenant, '/login'), [
            'email' => 'empleado.portal@example.test',
            'password' => 'incorrecta',
        ])->assertRedirect($this->portalRoute($tenant, '/login'))
            ->assertSessionHasErrors([
                'email' => 'Credenciales incorrectas.',
            ]);

        $this->from($this->portalRoute($tenant, '/login'))->post($this->portalRoute($tenant, '/login'), [
            'email' => 'desconocido@example.test',
            'password' => 'incorrecta',
        ])->assertRedirect($this->portalRoute($tenant, '/login'))
            ->assertSessionHasErrors([
                'email' => 'Credenciales incorrectas.',
            ]);
    }

    public function test_company_admin_is_redirected_to_the_admin_panel_from_central_login(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'email' => 'admin.portal@example.test',
        ]);
        $companyAdmin->assignRole('company-admin');

        $this->post('/login', [
            'email' => $companyAdmin->email,
            'password' => 'password',
        ])->assertRedirect('/admin');
    }

    public function test_employee_cannot_use_the_administration_login(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'email' => 'empleado.admin@example.test',
        ]);
        $employee->assignRole('employee');

        $this->from('/login')->post('/login', [
            'email' => $employee->email,
            'password' => 'password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors([
                'email' => 'Este acceso es solo para administracion. Usa el portal de tu empresa para fichar y gestionar solicitudes.',
            ]);

        $this->assertGuest();
    }

    public function test_company_admin_can_enter_the_portal_and_see_the_admin_shortcut(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'email' => 'admin.portal@example.test',
        ]);
        $companyAdmin->assignRole('company-admin');

        $this->post($this->portalRoute($tenant, '/login'), [
            'email' => $companyAdmin->email,
            'password' => 'password',
        ])->assertRedirect($this->portalRoute($tenant, '/dashboard'));

        $this->actingAs($companyAdmin)
            ->get($this->portalRoute($tenant, '/dashboard'))
            ->assertOk()
            ->assertSee('Zona administrativa')
            ->assertSee('/admin', false);
    }

    public function test_dashboard_shows_real_authenticated_user_data(): void
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Acme HR',
        ]);

        $this->createRoles();

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'name' => 'Ana Portal',
            'email' => 'ana.portal@example.test',
            'job_title' => 'People Specialist',
            'annual_vacation_days' => 28,
        ]);
        $employee->assignRole('employee');

        $otherUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $otherUser->assignRole('employee');

        TimeEntry::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'work_date' => now()->toDateString(),
            'check_in_time' => '08:30:00',
            'check_out_time' => null,
            'duration_minutes' => null,
            'status' => TimeEntryStatus::Incomplete->value,
        ]);

        LeaveRequest::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'status' => LeaveRequestStatus::Pending->value,
        ]);

        LeaveRequest::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'status' => LeaveRequestStatus::Approved->value,
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
        ]);

        LeaveRequest::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $otherUser->getKey(),
            'status' => LeaveRequestStatus::Pending->value,
        ]);

        Document::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'folder' => DocumentFolder::Payrolls->value,
            'is_visible_to_employee' => true,
        ]);

        Document::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'folder' => DocumentFolder::Contracts->value,
            'is_visible_to_employee' => false,
        ]);

        $response = $this->actingAs($employee)->get($this->portalRoute($tenant, '/dashboard'));

        $response
            ->assertOk()
            ->assertSee('Ana Portal')
            ->assertSee('Acme HR')
            ->assertSee('People Specialist')
            ->assertSee('08:30')
            ->assertSee('Pendiente de salida')
            ->assertSee('1 solicitud pendiente')
            ->assertSee('2 solicitudes registradas')
            ->assertSee('1 documento disponible')
            ->assertSee(now()->addDays(10)->format('d/m/Y'));
    }

    private function portalRoute(Tenant $tenant, string $suffix): string
    {
        return '/portal/'.$tenant->getKey().$suffix;
    }

    private function createRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
