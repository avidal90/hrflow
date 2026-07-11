<?php

namespace Tests\Feature\Filament;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Filament\Resources\LeaveRequests\Pages\CreateLeaveRequest;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeaveRequestManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_company_admin_can_create_leave_request_without_reason(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $companyAdmin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $companyAdmin->assignRole('company-admin');

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $employee->assignRole('employee');

        Livewire::actingAs($companyAdmin)
            ->test(CreateLeaveRequest::class)
            ->fillForm([
                'tenant_id' => $tenant->getKey(),
                'user_id' => $employee->getKey(),
                'request_type' => LeaveRequestType::Vacation->value,
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-01',
                'status' => LeaveRequestStatus::Pending->value,
                'reason' => null,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(LeaveRequest::class, [
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'request_type' => LeaveRequestType::Vacation->value,
            'status' => LeaveRequestStatus::Pending->value,
            'reason' => null,
        ]);
    }

    private function createRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
