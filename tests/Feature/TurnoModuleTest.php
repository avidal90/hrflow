<?php

namespace Tests\Feature;

use App\Filament\Resources\Turnos\TurnoResource;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\Turno;
use App\Models\TurnoAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TurnoModuleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_turno_calculates_total_hours_when_saved(): void
    {
        $tenant = Tenant::factory()->create();

        $turno = Turno::create([
            'tenant_id' => $tenant->getKey(),
            'name' => 'Mañana',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'break_minutes' => 60,
        ]);

        $this->assertSame('8.00', (string) $turno->refresh()->total_hours);
    }

    public function test_turno_assignments_support_seasonal_and_permanent_vigency_windows(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $winterTurno = Turno::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $summerTurno = Turno::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $winterAssignment = TurnoAssignment::create([
            'tenant_id' => $tenant->getKey(),
            'turno_id' => $winterTurno->getKey(),
            'user_id' => $employee->getKey(),
            'valid_from' => '2026-11-01',
            'valid_until' => '2027-03-31',
        ]);

        $permanentAssignment = TurnoAssignment::create([
            'tenant_id' => $tenant->getKey(),
            'turno_id' => $summerTurno->getKey(),
            'user_id' => $employee->getKey(),
            'valid_from' => null,
            'valid_until' => null,
        ]);

        $this->assertTrue(TurnoAssignment::query()->activeOn('2026-12-01')->whereKey($winterAssignment->getKey())->exists());
        $this->assertFalse(TurnoAssignment::query()->activeOn('2026-07-01')->whereKey($winterAssignment->getKey())->exists());
        $this->assertTrue(TurnoAssignment::query()->activeOn('2026-07-01')->whereKey($permanentAssignment->getKey())->exists());
    }

    public function test_turno_resource_is_scoped_to_tenant_and_user_assignment(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();

        $this->seedRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $companyAdmin->assignRole('company-admin');

        $ownTurno = Turno::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        Turno::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
        ]);

        $this->actingAs($companyAdmin);

        $visibleTurnos = TurnoResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($ownTurno->getKey(), $visibleTurnos);
        $this->assertCount(1, $visibleTurnos);

        $employeeUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $employeeUser->assignRole('employee');

        $employeeUser->update([
            'employee_code' => 'EMP-TEST-1',
            'employment_status' => 'active',
        ]);

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
            'valid_from' => '2026-06-01',
            'valid_until' => '2026-12-31',
        ]);

        $this->actingAs($employeeUser);

        $employeeVisibleTurnos = TurnoResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($assignedTurno->getKey(), $employeeVisibleTurnos);
        $this->assertNotContains($unassignedTurno->getKey(), $employeeVisibleTurnos);
    }

    private function seedRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
