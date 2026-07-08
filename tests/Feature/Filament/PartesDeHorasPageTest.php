<?php

namespace Tests\Feature\Filament;

use App\Enums\TimeEntryStatus;
use App\Filament\Pages\PartesDeHoras;
use App\Models\Department;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PartesDeHorasPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function createRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    public function test_company_admin_can_access_partes_de_horas_page(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $this->actingAs($admin);

        Livewire::test(PartesDeHoras::class)
            ->assertStatus(200);
    }

    public function test_hr_can_access_partes_de_horas_page(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $hr = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $hr->assignRole('hr');

        $this->actingAs($hr);

        Livewire::test(PartesDeHoras::class)
            ->assertStatus(200);
    }

    public function test_department_manager_can_access_partes_de_horas_page(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $manager = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $manager->assignRole('department-manager');

        $this->actingAs($manager);

        Livewire::test(PartesDeHoras::class)
            ->assertStatus(200);
    }

    public function test_page_mounts_with_current_year_and_month(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $this->actingAs($admin);

        Livewire::test(PartesDeHoras::class)
            ->assertSet('year', now()->year)
            ->assertSet('month', now()->month)
            ->assertSet('selectedUserId', null);
    }

    public function test_chart_data_returns_all_days_of_february_leap_year(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $this->actingAs($admin);

        $component = Livewire::test(PartesDeHoras::class)
            ->set('year', 2024)
            ->set('month', 2);

        $chartData = $component->instance()->getChartData();

        $this->assertCount(29, $chartData['labels']);
        $this->assertCount(29, $chartData['datasets'][0]['data']);
    }

    public function test_chart_data_returns_31_days_for_january(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $this->actingAs($admin);

        $component = Livewire::test(PartesDeHoras::class)
            ->set('year', 2025)
            ->set('month', 1);

        $chartData = $component->instance()->getChartData();

        $this->assertCount(31, $chartData['labels']);
    }

    public function test_chart_data_returns_28_days_for_non_leap_february(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $this->actingAs($admin);

        $component = Livewire::test(PartesDeHoras::class)
            ->set('year', 2025)
            ->set('month', 2);

        $chartData = $component->instance()->getChartData();

        $this->assertCount(28, $chartData['labels']);
    }

    public function test_chart_data_returns_zeros_when_no_user_selected(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $this->actingAs($admin);

        $component = Livewire::test(PartesDeHoras::class)
            ->set('year', 2025)
            ->set('month', 6);

        $chartData = $component->instance()->getChartData();

        $this->assertSame(array_fill(0, 30, 0.0), $chartData['datasets'][0]['data']);
    }

    public function test_company_admin_sees_time_entries_aggregated_by_day(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $employee = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $employee->assignRole('employee');

        TimeEntry::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'work_date' => '2025-06-10',
            'check_in_time' => '09:00:00',
            'check_out_time' => '17:00:00',
            'duration_minutes' => 480,
            'status' => TimeEntryStatus::Complete,
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(PartesDeHoras::class)
            ->set('year', 2025)
            ->set('month', 6)
            ->set('selectedUserId', $employee->getKey());

        $chartData = $component->instance()->getChartData();

        $this->assertSame(8.0, $chartData['datasets'][0]['data'][9]);
    }

    public function test_days_without_entries_show_zero_hours(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $employee = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $employee->assignRole('employee');

        TimeEntry::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'work_date' => '2025-06-15',
            'check_in_time' => '09:00:00',
            'check_out_time' => '13:00:00',
            'duration_minutes' => 240,
            'status' => TimeEntryStatus::Complete,
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(PartesDeHoras::class)
            ->set('year', 2025)
            ->set('month', 6)
            ->set('selectedUserId', $employee->getKey());

        $chartData = $component->instance()->getChartData();

        $this->assertSame(0.0, $chartData['datasets'][0]['data'][0]);
        $this->assertSame(4.0, $chartData['datasets'][0]['data'][14]);
    }

    public function test_monthly_stats_calculated_correctly(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $employee = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $employee->assignRole('employee');

        foreach (['2025-06-01', '2025-06-02', '2025-06-03'] as $date) {
            TimeEntry::factory()->create([
                'tenant_id' => $tenant->getKey(),
                'user_id' => $employee->getKey(),
                'work_date' => $date,
                'check_in_time' => '09:00:00',
                'check_out_time' => '17:00:00',
                'duration_minutes' => 480,
                'status' => TimeEntryStatus::Complete,
            ]);
        }

        $this->actingAs($admin);

        $component = Livewire::test(PartesDeHoras::class)
            ->set('year', 2025)
            ->set('month', 6)
            ->set('selectedUserId', $employee->getKey());

        $stats = $component->instance()->getMonthlyStats();

        $this->assertSame(24.0, $stats['totalHours']);
        $this->assertSame(3, $stats['workedDays']);
        $this->assertSame(8.0, $stats['dailyAverage']);
    }

    public function test_company_admin_cannot_see_other_tenant_employee_data(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $otherEmployee = User::factory()->create(['tenant_id' => $otherTenant->getKey()]);
        $otherEmployee->assignRole('employee');

        TimeEntry::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
            'user_id' => $otherEmployee->getKey(),
            'work_date' => '2025-06-10',
            'duration_minutes' => 480,
            'status' => TimeEntryStatus::Complete,
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(PartesDeHoras::class)
            ->set('year', 2025)
            ->set('month', 6)
            ->set('selectedUserId', $otherEmployee->getKey());

        $stats = $component->instance()->getMonthlyStats();

        $this->assertSame(0.0, $stats['totalHours']);
        $this->assertSame(0, $stats['workedDays']);
    }

    public function test_department_manager_cannot_access_employee_from_other_department(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $manager = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $manager->assignRole('department-manager');

        $otherDept = Department::factory()->create(['tenant_id' => $tenant->getKey()]);

        $otherEmployee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'department_id' => $otherDept->getKey(),
        ]);
        $otherEmployee->assignRole('employee');

        TimeEntry::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $otherEmployee->getKey(),
            'work_date' => '2025-06-10',
            'duration_minutes' => 480,
            'status' => TimeEntryStatus::Complete,
        ]);

        $this->actingAs($manager);

        $component = Livewire::test(PartesDeHoras::class)
            ->set('year', 2025)
            ->set('month', 6)
            ->set('selectedUserId', $otherEmployee->getKey());

        $stats = $component->instance()->getMonthlyStats();

        $this->assertSame(0.0, $stats['totalHours']);
        $this->assertSame(0, $stats['workedDays']);
    }

    public function test_incomplete_time_entries_are_not_counted(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $employee = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $employee->assignRole('employee');

        TimeEntry::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'work_date' => '2025-06-10',
            'check_in_time' => '09:00:00',
            'check_out_time' => null,
            'duration_minutes' => null,
            'status' => TimeEntryStatus::Incomplete,
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(PartesDeHoras::class)
            ->set('year', 2025)
            ->set('month', 6)
            ->set('selectedUserId', $employee->getKey());

        $stats = $component->instance()->getMonthlyStats();

        $this->assertSame(0.0, $stats['totalHours']);
        $this->assertSame(0, $stats['workedDays']);
    }

    public function test_employee_options_scoped_to_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();
        $this->createRoles();

        $admin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $admin->assignRole('company-admin');

        $employee = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $employee->assignRole('employee');

        $otherEmployee = User::factory()->create(['tenant_id' => $otherTenant->getKey()]);
        $otherEmployee->assignRole('employee');

        $this->actingAs($admin);

        $component = Livewire::test(PartesDeHoras::class);
        $options = $component->instance()->getEmployeeOptions();

        $this->assertArrayHasKey($employee->getKey(), $options);
        $this->assertArrayNotHasKey($otherEmployee->getKey(), $options);
    }

    public function test_navigation_visible_for_authorized_roles(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createRoles();

        foreach (['company-admin', 'hr', 'department-manager'] as $role) {
            $user = User::factory()->create(['tenant_id' => $tenant->getKey()]);
            $user->assignRole($role);

            $this->actingAs($user);

            $this->assertTrue(
                PartesDeHoras::shouldRegisterNavigation(),
                "Navigation should be visible for role: {$role}",
            );
        }
    }
}
