<?php

namespace Tests\Feature\Portal;

use App\Enums\LeaveRequestStatus;
use App\Models\Festivo;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\Turno;
use App\Models\TurnoAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PortalCalendarEventsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_cannot_access_calendar_events_endpoint(): void
    {
        $tenant = Tenant::factory()->create();

        $this->getJson($this->eventsRoute($tenant))
            ->assertRedirect($this->portalRoute($tenant, '/login'));
    }

    public function test_employee_can_access_calendar_page(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        $this->actingAs($employee)
            ->get($this->portalRoute($tenant, '/calendario'))
            ->assertOk()
            ->assertSee('Calendario laboral');
    }

    public function test_events_endpoint_returns_empty_array_when_no_data(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        $this->actingAs($employee)
            ->getJson($this->eventsRoute($tenant, '2026-07-01', '2026-07-31'))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_festivos_appear_as_events(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        Festivo::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'date' => '2026-07-15',
        ]);

        $response = $this->actingAs($employee)
            ->getJson($this->eventsRoute($tenant, '2026-07-01', '2026-07-31'));

        $response->assertOk()->assertJsonCount(1);
        $event = $response->json(0);

        $this->assertSame('Festivo', $event['title']);
        $this->assertSame('2026-07-15', $event['start']);
    }

    public function test_approved_leave_requests_appear_as_events(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        LeaveRequest::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'status' => LeaveRequestStatus::Approved->value,
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-14',
        ]);

        $response = $this->actingAs($employee)
            ->getJson($this->eventsRoute($tenant, '2026-07-01', '2026-07-31'));

        $response->assertOk()->assertJsonCount(1);
        $event = $response->json(0);

        $this->assertSame('2026-07-10', $event['start']);
        $this->assertSame('2026-07-15', $event['end']); // end es exclusivo en FullCalendar
    }

    public function test_pending_leave_requests_do_not_appear(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        LeaveRequest::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'status' => LeaveRequestStatus::Pending->value,
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-14',
        ]);

        $this->actingAs($employee)
            ->getJson($this->eventsRoute($tenant, '2026-07-01', '2026-07-31'))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_turno_assignments_appear_as_events_spanning_the_full_period(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        $turno = Turno::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'name' => 'Turno Manana',
            'start_time' => '08:00:00',
            'end_time' => '15:00:00',
        ]);

        TurnoAssignment::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'turno_id' => $turno->getKey(),
            'valid_from' => '2026-07-01',
            'valid_until' => '2026-07-31',
        ]);

        $response = $this->actingAs($employee)
            ->getJson($this->eventsRoute($tenant, '2026-07-01', '2026-07-31'));

        $response->assertOk()->assertJsonCount(1);
        $event = $response->json(0);

        $this->assertStringContainsString('Turno Manana', $event['title']);
        $this->assertSame('2026-07-01', $event['start']);
        $this->assertSame('2026-08-01', $event['end']); // valid_until 31 + 1 dia exclusivo
        $this->assertArrayNotHasKey('display', $event);
    }

    public function test_turno_assignments_without_weekends_are_returned_as_weekday_recurring_events(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        $turno = Turno::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'name' => 'Turno Oficina',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'includes_weekends' => false,
        ]);

        TurnoAssignment::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'turno_id' => $turno->getKey(),
            'valid_from' => '2026-07-01',
            'valid_until' => '2026-07-31',
        ]);

        $response = $this->actingAs($employee)
            ->getJson($this->eventsRoute($tenant, '2026-07-01', '2026-07-31'));

        $response->assertOk()->assertJsonCount(1);
        $event = $response->json(0);

        $this->assertStringContainsString('Turno Oficina', $event['title']);
        $this->assertSame('2026-07-01', $event['startRecur']);
        $this->assertSame('2026-08-01', $event['endRecur']);
        $this->assertSame([1, 2, 3, 4, 5], $event['daysOfWeek']);
        $this->assertArrayNotHasKey('start', $event);
        $this->assertArrayNotHasKey('end', $event);
    }

    public function test_employee_cannot_see_other_employees_leave_requests(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);
        $other = $this->createEmployee($tenant);

        LeaveRequest::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $other->getKey(),
            'status' => LeaveRequestStatus::Approved->value,
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-14',
        ]);

        $this->actingAs($employee)
            ->getJson($this->eventsRoute($tenant, '2026-07-01', '2026-07-31'))
            ->assertOk()
            ->assertExactJson([]);
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

    private function eventsRoute(Tenant $tenant, string $start = '2026-07-01', string $end = '2026-07-31'): string
    {
        return "/portal/{$tenant->getKey()}/calendario/eventos?start={$start}&end={$end}";
    }
}
