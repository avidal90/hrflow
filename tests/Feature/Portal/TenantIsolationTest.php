<?php

namespace Tests\Feature\Portal;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use LazilyRefreshDatabase;

    // -------------------------------------------------------------------------
    // Middleware: EnsureUserBelongsToTenant
    // -------------------------------------------------------------------------

    public function test_authenticated_user_cannot_access_dashboard_of_another_tenant(): void
    {
        $ownTenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();

        $user = $this->createEmployee($ownTenant);

        $this->actingAs($user)
            ->get($this->portalRoute($otherTenant, '/dashboard'))
            ->assertRedirect($this->portalRoute($ownTenant, '/dashboard'));
    }

    public function test_authenticated_user_cannot_access_any_protected_route_of_another_tenant(): void
    {
        $ownTenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();

        $user = $this->createEmployee($ownTenant);

        $protectedRoutes = [
            '/control-horario',
            '/solicitudes',
            '/documentacion',
            '/calendario',
        ];

        foreach ($protectedRoutes as $route) {
            $this->actingAs($user)
                ->get($this->portalRoute($otherTenant, $route))
                ->assertRedirect($this->portalRoute($ownTenant, '/dashboard'));
        }
    }

    public function test_authenticated_user_can_access_their_own_tenant_portal_normally(): void
    {
        $tenant = Tenant::factory()->create();
        $user = $this->createEmployee($tenant);

        $this->actingAs($user)
            ->get($this->portalRoute($tenant, '/dashboard'))
            ->assertOk();
    }

    public function test_cross_tenant_access_does_not_expose_data_from_another_tenant(): void
    {
        $ownTenant = Tenant::factory()->create(['name' => 'Empresa Propia']);
        $otherTenant = Tenant::factory()->create(['name' => 'Empresa Ajena']);

        $user = $this->createEmployee($ownTenant);

        $response = $this->actingAs($user)
            ->get($this->portalRoute($otherTenant, '/dashboard'));

        $response->assertRedirect($this->portalRoute($ownTenant, '/dashboard'));
        $response->assertDontSee('Empresa Ajena');
    }

    public function test_unauthenticated_user_is_redirected_to_tenant_login_not_affected_by_isolation_middleware(): void
    {
        $tenant = Tenant::factory()->create();

        $this->get($this->portalRoute($tenant, '/dashboard'))
            ->assertRedirect($this->portalRoute($tenant, '/login'));
    }

    // -------------------------------------------------------------------------
    // Tenant no encontrado
    // -------------------------------------------------------------------------

    public function test_accessing_a_nonexistent_tenant_portal_returns_404(): void
    {
        $this->get('/portal/tenant-que-no-existe/dashboard')
            ->assertStatus(404);
    }

    public function test_accessing_a_nonexistent_tenant_portal_shows_friendly_error_page(): void
    {
        $this->get('/portal/tenant-que-no-existe/login')
            ->assertStatus(404)
            ->assertSee('Portal no disponible')
            ->assertSee('Buscar mi empresa');
    }

    public function test_accessing_a_nonexistent_tenant_portal_on_any_path_returns_404(): void
    {
        foreach (['/login', '/dashboard', '/control-horario', '/solicitudes'] as $path) {
            $this->get('/portal/tenant-inexistente'.$path)
                ->assertStatus(404);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createEmployee(Tenant $tenant): User
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $user = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $user->assignRole('employee');

        return $user;
    }

    private function portalRoute(Tenant $tenant, string $suffix): string
    {
        return '/portal/'.$tenant->getKey().$suffix;
    }
}
