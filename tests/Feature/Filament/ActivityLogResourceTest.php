<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Models\Activity;
use App\Models\Department;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ActivityLogResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function createRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function createActivityFor(int|string $tenantId, int|string|null $causerId = null): Activity
    {
        return Activity::create([
            'log_name' => 'default',
            'description' => 'created',
            'event' => 'created',
            'subject_type' => User::class,
            'subject_id' => 1,
            'causer_type' => $causerId ? User::class : null,
            'causer_id' => $causerId,
            'tenant_id' => $tenantId,
            'properties' => [],
        ]);
    }

    public function test_superadmin_can_view_any_activity_log(): void
    {
        $this->createRoles();

        $superAdmin = User::factory()->create(['tenant_id' => null]);
        $superAdmin->assignRole('super-admin');

        $tenant = Tenant::factory()->create();
        $this->createActivityFor($tenant->getKey());

        $this->actingAs($superAdmin);

        $this->assertTrue($superAdmin->can('viewAny', Activity::class));
        $this->assertTrue(ActivityLogResource::shouldRegisterNavigation());
    }

    public function test_superadmin_sees_all_tenants_activity(): void
    {
        $this->createRoles();

        $superAdmin = User::factory()->create(['tenant_id' => null]);
        $superAdmin->assignRole('super-admin');

        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $activity1 = $this->createActivityFor($tenant1->getKey());
        $activity2 = $this->createActivityFor($tenant2->getKey());

        $this->actingAs($superAdmin);

        $ids = ActivityLogResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($activity1->getKey(), $ids);
        $this->assertContains($activity2->getKey(), $ids);
    }

    public function test_company_admin_can_view_own_tenant_activity(): void
    {
        $this->createRoles();

        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();

        $companyAdmin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $companyAdmin->assignRole('company-admin');

        $ownActivity = $this->createActivityFor($tenant->getKey());
        $otherActivity = $this->createActivityFor($otherTenant->getKey());

        $this->actingAs($companyAdmin);

        $this->assertTrue($companyAdmin->can('viewAny', Activity::class));
        $this->assertTrue($companyAdmin->can('view', $ownActivity));
        $this->assertFalse($companyAdmin->can('view', $otherActivity));
    }

    public function test_company_admin_query_is_scoped_to_own_tenant(): void
    {
        $this->createRoles();

        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();

        $companyAdmin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $companyAdmin->assignRole('company-admin');

        $ownActivity = $this->createActivityFor($tenant->getKey());
        $otherActivity = $this->createActivityFor($otherTenant->getKey());

        $this->actingAs($companyAdmin);

        $ids = ActivityLogResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($ownActivity->getKey(), $ids);
        $this->assertNotContains($otherActivity->getKey(), $ids);
    }

    public function test_employee_cannot_view_activity_logs(): void
    {
        $this->createRoles();

        $tenant = Tenant::factory()->create();
        $employee = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $employee->assignRole('employee');

        $activity = $this->createActivityFor($tenant->getKey());

        $this->actingAs($employee);

        $this->assertFalse($employee->can('viewAny', Activity::class));
        $this->assertFalse(ActivityLogResource::shouldRegisterNavigation());
        $this->assertFalse($employee->can('view', $activity));
    }

    public function test_activity_log_cannot_be_created_via_resource(): void
    {
        $this->assertFalse(ActivityLogResource::canCreate());
    }

    public function test_model_logs_activity_on_create(): void
    {
        $this->createRoles();

        $tenant = Tenant::factory()->create();
        $superAdmin = User::factory()->create(['tenant_id' => null]);
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin);

        $initialCount = Activity::count();

        User::factory()->create(['tenant_id' => $tenant->getKey()]);

        $this->assertGreaterThan($initialCount, Activity::count());

        $log = Activity::latest()->first();
        $this->assertSame('created', $log->event);
        $this->assertSame(User::class, $log->subject_type);
    }

    public function test_activity_log_stores_string_tenant_id_without_error(): void
    {
        $this->createRoles();

        $tenant = Tenant::factory()->create();
        $companyAdmin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $companyAdmin->assignRole('company-admin');

        $this->actingAs($companyAdmin);

        $department = Department::factory()->create(['tenant_id' => $tenant->getKey()]);

        $log = Activity::where('subject_type', Department::class)
            ->where('subject_id', $department->getKey())
            ->latest()
            ->first();

        $this->assertNotNull($log, 'Se esperaba un registro de actividad para el departamento creado.');
        $this->assertSame((string) $tenant->getKey(), (string) $log->tenant_id);
        $this->assertSame('created', $log->event);
    }

    public function test_activity_log_stores_ip_address(): void
    {
        $this->createRoles();

        $tenant = Tenant::factory()->create();
        $companyAdmin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $companyAdmin->assignRole('company-admin');

        $this->actingAs($companyAdmin);

        Department::factory()->create(['tenant_id' => $tenant->getKey()]);

        $log = Activity::where('subject_type', Department::class)
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
    }

    public function test_activity_log_records_updated_event_with_changes(): void
    {
        $this->createRoles();

        $tenant = Tenant::factory()->create();
        $companyAdmin = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $companyAdmin->assignRole('company-admin');

        $this->actingAs($companyAdmin);

        $department = Department::factory()->create(['tenant_id' => $tenant->getKey()]);

        $oldName = $department->name;
        $department->update(['name' => 'Nombre actualizado']);

        $log = Activity::where('subject_type', Department::class)
            ->where('subject_id', $department->getKey())
            ->where('event', 'updated')
            ->latest()
            ->first();

        $this->assertNotNull($log, 'Se esperaba un registro de actividad para la actualización.');
        $this->assertArrayHasKey('name', $log->properties['attributes'] ?? []);
        $this->assertArrayHasKey('name', $log->properties['old'] ?? []);
        $this->assertSame($oldName, $log->properties['old']['name']);
        $this->assertSame('Nombre actualizado', $log->properties['attributes']['name']);
    }
}
