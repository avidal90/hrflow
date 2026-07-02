<?php

namespace Tests\Feature\Portal;

use App\Enums\TimeEntryStatus;
use App\Livewire\Portal\TimeTracker;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PortalTimeTrackingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_employee_can_access_time_tracking_page(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        $this->actingAs($employee)
            ->get($this->portalRoute($tenant, '/control-horario'))
            ->assertOk()
            ->assertSee('Registro de jornada');
    }

    public function test_employee_can_start_tracking(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        Livewire::actingAs($employee)
            ->test(TimeTracker::class)
            ->assertSee('Iniciar jornada')
            ->call('startTracking')
            ->assertSee('Finalizar jornada');

        $this->assertDatabaseHas(TimeEntry::class, [
            'user_id' => $employee->id,
            'tenant_id' => $tenant->getKey(),
            'status' => TimeEntryStatus::Incomplete->value,
        ]);
    }

    public function test_employee_can_stop_tracking(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        TimeEntry::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->id,
            'work_date' => today(),
            'check_in_time' => '09:00:00',
            'check_out_time' => null,
            'status' => TimeEntryStatus::Incomplete->value,
        ]);

        Livewire::actingAs($employee)
            ->test(TimeTracker::class)
            ->assertSee('Finalizar jornada')
            ->call('stopTracking')
            ->assertSee('Iniciar jornada');

        $this->assertDatabaseMissing(TimeEntry::class, [
            'user_id' => $employee->id,
            'check_out_time' => null,
        ]);
    }

    public function test_employee_cannot_start_two_active_sessions(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        TimeEntry::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->id,
            'work_date' => today(),
            'check_in_time' => '09:00:00',
            'check_out_time' => null,
            'status' => TimeEntryStatus::Incomplete->value,
        ]);

        Livewire::actingAs($employee)
            ->test(TimeTracker::class)
            ->call('startTracking')
            ->assertStatus(422);

        $this->assertSame(1, TimeEntry::where('user_id', $employee->id)
            ->where('status', TimeEntryStatus::Incomplete->value)
            ->count());
    }

    public function test_employee_only_sees_own_entries(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);
        $other = $this->createEmployee($tenant);

        TimeEntry::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $other->id,
            'work_date' => today(),
            'check_in_time' => '08:00:00',
            'check_out_time' => '16:00:00',
        ]);

        Livewire::actingAs($employee)
            ->test(TimeTracker::class)
            ->assertSee('Iniciar jornada')
            ->assertDontSee('08:00');
    }

    public function test_employee_cannot_stop_another_employees_entry(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);
        $other = $this->createEmployee($tenant);

        $entry = TimeEntry::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $other->id,
            'work_date' => today(),
            'check_in_time' => '09:00:00',
            'check_out_time' => null,
            'status' => TimeEntryStatus::Incomplete->value,
        ]);

        // Montamos el componente como $employee: no tiene activeEntry propio
        Livewire::actingAs($employee)
            ->test(TimeTracker::class)
            ->call('stopTracking')
            ->assertStatus(422); // no hay activeEntry para este usuario

        $this->assertNull($entry->fresh()->check_out_time);
    }

    // --- helpers ---

    private function createEmployee(Tenant $tenant): User
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $employee = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $employee->assignRole('employee');

        return $employee;
    }

    private function portalRoute(Tenant $tenant, string $suffix): string
    {
        return '/portal/'.$tenant->getKey().$suffix;
    }
}
