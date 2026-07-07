<?php

namespace Tests\Feature\Portal;

use App\Models\Festivo;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\Turno;
use App\Models\TurnoAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TodayOffReasonTest extends TestCase
{
    use LazilyRefreshDatabase;

    // -------------------------------------------------------------------------
    // todayOffReason() - ausencia aprobada
    // -------------------------------------------------------------------------

    public function test_returns_leave_when_user_has_approved_leave_today(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        LeaveRequest::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->id,
            'status' => 'approved',
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
        ]);

        $this->assertSame('leave', $employee->todayOffReason());
    }

    public function test_returns_leave_when_approved_leave_spans_today(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        LeaveRequest::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->id,
            'status' => 'approved',
            'start_date' => today()->subDays(2)->toDateString(),
            'end_date' => today()->addDays(2)->toDateString(),
        ]);

        $this->assertSame('leave', $employee->todayOffReason());
    }

    public function test_does_not_return_leave_for_pending_requests(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        LeaveRequest::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->id,
            'status' => 'pending',
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
        ]);

        $this->assertNull($employee->todayOffReason());
    }

    // -------------------------------------------------------------------------
    // todayOffReason() - festivo
    // -------------------------------------------------------------------------

    public function test_returns_festivo_when_today_is_a_company_holiday(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        Festivo::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'date' => today()->toDateString(),
        ]);

        $this->assertSame('festivo', $employee->todayOffReason());
    }

    public function test_does_not_return_festivo_for_another_tenants_holiday(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        Festivo::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
            'date' => today()->toDateString(),
        ]);

        $this->assertNull($employee->todayOffReason());
    }

    // -------------------------------------------------------------------------
    // todayOffReason() - fin de semana sin turno
    // -------------------------------------------------------------------------

    public function test_returns_weekend_when_today_is_saturday_and_turno_excludes_weekends(): void
    {
        Carbon::setTestNow(Carbon::parse('next saturday'));

        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        $turno = Turno::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'includes_weekends' => false,
        ]);

        TurnoAssignment::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->id,
            'turno_id' => $turno->id,
            'valid_from' => today()->subMonth()->toDateString(),
            'valid_until' => today()->addMonth()->toDateString(),
        ]);

        $this->assertSame('weekend', $employee->todayOffReason());

        Carbon::setTestNow();
    }

    public function test_does_not_return_weekend_when_turno_includes_weekends(): void
    {
        Carbon::setTestNow(Carbon::parse('next saturday'));

        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        $turno = Turno::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'includes_weekends' => true,
        ]);

        TurnoAssignment::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->id,
            'turno_id' => $turno->id,
            'valid_from' => today()->subMonth()->toDateString(),
            'valid_until' => today()->addMonth()->toDateString(),
        ]);

        $this->assertNull($employee->todayOffReason());

        Carbon::setTestNow();
    }

    // -------------------------------------------------------------------------
    // Dashboard: muestra el mensaje en la card HOY
    // -------------------------------------------------------------------------

    public function test_dashboard_shows_off_day_message_when_user_has_approved_leave_today(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        LeaveRequest::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->id,
            'status' => 'approved',
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
        ]);

        $this->actingAs($employee)
            ->get($this->portalRoute($tenant, '/dashboard'))
            ->assertOk()
            ->assertSee('Día libre')
            ->assertSee('¡Disfruta del día!')
            ->assertSee('Ausencia aprobada');
    }

    public function test_dashboard_shows_off_day_message_when_today_is_a_festivo(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        Festivo::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'date' => today()->toDateString(),
        ]);

        $this->actingAs($employee)
            ->get($this->portalRoute($tenant, '/dashboard'))
            ->assertOk()
            ->assertSee('Día libre')
            ->assertSee('¡Disfruta del día!')
            ->assertSee('Festivo');
    }

    public function test_dashboard_does_not_show_off_day_message_on_a_regular_work_day(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        $this->actingAs($employee)
            ->get($this->portalRoute($tenant, '/dashboard'))
            ->assertOk()
            ->assertDontSee('Día libre')
            ->assertSee('Sin fichaje');
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
