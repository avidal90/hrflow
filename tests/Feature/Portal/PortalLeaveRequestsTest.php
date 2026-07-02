<?php

namespace Tests\Feature\Portal;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Livewire\Portal\LeaveRequests;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PortalLeaveRequestsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_employee_can_access_requests_page(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        $this->actingAs($employee)
            ->get($this->portalRoute($tenant, '/solicitudes'))
            ->assertOk()
            ->assertSee('Mis solicitudes');
    }

    public function test_employee_can_submit_a_vacation_request(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant, vacationDays: 20);

        Livewire::actingAs($employee)
            ->test(LeaveRequests::class)
            ->set('requestType', 'vacation')
            ->set('startDate', '2026-08-01')
            ->set('endDate', '2026-08-07')
            ->set('reason', 'Vacaciones de verano')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas(LeaveRequest::class, [
            'user_id' => $employee->id,
            'tenant_id' => $tenant->getKey(),
            'request_type' => LeaveRequestType::Vacation->value,
            'status' => LeaveRequestStatus::Pending->value,
            'start_date' => '2026-08-01 00:00:00',
            'end_date' => '2026-08-07 00:00:00',
        ]);
    }

    public function test_employee_can_submit_a_paid_leave_request(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        Livewire::actingAs($employee)
            ->test(LeaveRequests::class)
            ->set('requestType', 'paid_leave')
            ->set('startDate', '2026-09-01')
            ->set('endDate', '2026-09-01')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas(LeaveRequest::class, [
            'user_id' => $employee->id,
            'request_type' => LeaveRequestType::PaidLeave->value,
        ]);
    }

    public function test_validation_requires_type_and_dates(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        Livewire::actingAs($employee)
            ->test(LeaveRequests::class)
            ->set('requestType', '')
            ->set('startDate', '')
            ->set('endDate', '')
            ->call('submit')
            ->assertHasErrors(['requestType', 'startDate', 'endDate']);
    }

    public function test_end_date_cannot_be_before_start_date(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);

        Livewire::actingAs($employee)
            ->test(LeaveRequests::class)
            ->set('requestType', 'paid_leave')
            ->set('startDate', '2026-09-10')
            ->set('endDate', '2026-09-05')
            ->call('submit')
            ->assertHasErrors(['endDate']);
    }

    public function test_vacation_request_blocked_when_days_exceed_balance(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant, vacationDays: 3);

        Livewire::actingAs($employee)
            ->test(LeaveRequests::class)
            ->set('requestType', 'vacation')
            ->set('startDate', '2026-08-01')
            ->set('endDate', '2026-08-10') // 10 days, only 3 available
            ->call('submit')
            ->assertHasErrors(['startDate']);

        $this->assertDatabaseMissing(LeaveRequest::class, ['user_id' => $employee->id]);
    }

    public function test_remaining_vacation_days_deducted_by_approved_requests(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant, vacationDays: 20);

        LeaveRequest::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->id,
            'request_type' => LeaveRequestType::Vacation->value,
            'status' => LeaveRequestStatus::Approved->value,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-05', // 5 days
        ]);

        $component = Livewire::actingAs($employee)->test(LeaveRequests::class);
        $this->assertSame(15, $component->get('remainingVacationDays'));
    }

    public function test_manager_is_notified_when_request_is_submitted(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = $this->createEmployee($tenant, role: 'department-manager');
        $dept = Department::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'manager_user_id' => $manager->id,
        ]);
        $employee = $this->createEmployee($tenant);
        $employee->update(['department_id' => $dept->id]);

        Livewire::actingAs($employee)
            ->test(LeaveRequests::class)
            ->set('requestType', 'paid_leave')
            ->set('startDate', '2026-09-01')
            ->set('endDate', '2026-09-01')
            ->call('submit');

        $this->assertCount(1, $manager->notifications);
    }

    public function test_no_notification_when_employee_has_no_manager(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);
        $employee->update(['department_id' => null]);

        Livewire::actingAs($employee)
            ->test(LeaveRequests::class)
            ->set('requestType', 'paid_leave')
            ->set('startDate', '2026-09-01')
            ->set('endDate', '2026-09-01')
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertCount(0, $employee->notifications);
    }

    public function test_employee_only_sees_own_requests(): void
    {
        $tenant = Tenant::factory()->create();
        $employee = $this->createEmployee($tenant);
        $other = $this->createEmployee($tenant);

        LeaveRequest::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $other->id,
            'request_type' => LeaveRequestType::Vacation->value,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-05',
        ]);

        Livewire::actingAs($employee)
            ->test(LeaveRequests::class)
            ->assertSee('Todavia no has enviado ninguna solicitud');
    }

    // --- helpers ---

    private function createEmployee(Tenant $tenant, int $vacationDays = 22, string $role = 'employee'): User
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $r) {
            Role::findOrCreate($r, 'web');
        }

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'annual_vacation_days' => $vacationDays,
        ]);
        $employee->assignRole($role);

        return $employee;
    }

    private function portalRoute(Tenant $tenant, string $suffix): string
    {
        return '/portal/'.$tenant->getKey().$suffix;
    }
}
