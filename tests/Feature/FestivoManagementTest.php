<?php

namespace Tests\Feature;

use App\Filament\Resources\Festivos\FestivoResource;
use App\Filament\Resources\Festivos\Pages\CreateFestivo;
use App\Models\Festivo;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class FestivoManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_company_admin_can_create_a_festivo_from_filament(): void
    {
        [$tenant, $companyAdmin] = $this->createTenantUsers();

        $this->actingAs($companyAdmin);

        Livewire::test(CreateFestivo::class)
            ->fillForm([
                'tenant_id' => $tenant->getKey(),
                'date' => '2026-12-25',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $festivo = Festivo::query()->firstOrFail();

        $this->assertSame((string) $tenant->getKey(), (string) $festivo->tenant_id);
        $this->assertSame('2026-12-25', $festivo->date?->format('Y-m-d'));
    }

    public function test_hr_and_department_manager_can_view_but_cannot_mutate_festivos(): void
    {
        [$tenant, , $hrUser, $managerUser] = $this->createTenantUsers();
        $otherTenant = Tenant::factory()->create();

        $festivo = Festivo::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'date' => '2026-08-15',
        ]);

        $otherFestivo = Festivo::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
            'date' => '2026-10-12',
        ]);

        $this->assertTrue(Gate::forUser($hrUser)->allows('viewAny', Festivo::class));
        $this->assertTrue(Gate::forUser($hrUser)->allows('view', $festivo));
        $this->assertTrue(Gate::forUser($hrUser)->denies('view', $otherFestivo));
        $this->assertTrue(Gate::forUser($hrUser)->denies('create', Festivo::class));
        $this->assertTrue(Gate::forUser($hrUser)->denies('update', $festivo));
        $this->assertTrue(Gate::forUser($hrUser)->denies('delete', $festivo));

        $this->assertTrue(Gate::forUser($managerUser)->allows('viewAny', Festivo::class));
        $this->assertTrue(Gate::forUser($managerUser)->allows('view', $festivo));
        $this->assertTrue(Gate::forUser($managerUser)->denies('view', $otherFestivo));
        $this->assertTrue(Gate::forUser($managerUser)->denies('create', Festivo::class));
        $this->assertTrue(Gate::forUser($managerUser)->denies('update', $festivo));
        $this->assertTrue(Gate::forUser($managerUser)->denies('delete', $festivo));
    }

    public function test_festivo_resource_navigation_and_query_follow_tenant_scope_for_filament_roles(): void
    {
        [$tenant, $companyAdmin, $hrUser, $managerUser] = $this->createTenantUsers();
        $otherTenant = Tenant::factory()->create();

        $visibleFestivo = Festivo::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'date' => '2026-05-01',
        ]);

        $hiddenFestivo = Festivo::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
            'date' => '2026-01-06',
        ]);

        foreach ([$companyAdmin, $hrUser, $managerUser] as $user) {
            $this->actingAs($user);

            $this->assertTrue(FestivoResource::shouldRegisterNavigation());
            $this->assertSame([
                $visibleFestivo->getKey(),
            ], FestivoResource::getEloquentQuery()->pluck('id')->all());
            $this->assertNotContains($hiddenFestivo->getKey(), FestivoResource::getEloquentQuery()->pluck('id')->all());
        }
    }

    /**
     * @return array{0: Tenant, 1: User, 2: User, 3: User}
     */
    private function createTenantUsers(): array
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

        $managerUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $managerUser->assignRole('department-manager');

        return [$tenant, $companyAdmin, $hrUser, $managerUser];
    }

    private function createRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
