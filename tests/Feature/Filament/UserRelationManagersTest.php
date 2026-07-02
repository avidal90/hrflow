<?php

namespace Tests\Feature\Filament;

use App\Enums\DocumentFolder;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\Users\RelationManagers\LeaveRequestsRelationManager;
use App\Filament\Resources\Users\RelationManagers\TimeEntriesRelationManager;
use App\Filament\Resources\Users\RelationManagers\TurnoAssignmentsRelationManager;
use App\Models\Document;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TimeEntry;
use App\Models\Turno;
use App\Models\TurnoAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class UserRelationManagersTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_relation_managers_expose_expected_filters(): void
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

        $timeEntry = TimeEntry::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
        ]);

        $leaveRequest = LeaveRequest::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
        ]);

        $document = Document::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'uploaded_by_user_id' => $companyAdmin->getKey(),
        ]);

        $turno = Turno::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $turnoAssignment = TurnoAssignment::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'turno_id' => $turno->getKey(),
            'user_id' => $employee->getKey(),
        ]);

        $this->actingAs($companyAdmin);

        Livewire::test(TimeEntriesRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => ViewUser::class,
        ])
            ->assertCanSeeTableRecords([$timeEntry])
            ->assertTableFilterExists('status')
            ->assertTableFilterExists('work_date')
            ->assertTableFilterExists('open_entries');

        Livewire::test(LeaveRequestsRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => ViewUser::class,
        ])
            ->assertCanSeeTableRecords([$leaveRequest])
            ->assertTableFilterExists('status')
            ->assertTableFilterExists('request_type')
            ->assertTableFilterExists('pending_only');

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => ViewUser::class,
        ])
            ->assertCanSeeTableRecords([$document])
            ->assertTableFilterExists('folder')
            ->assertTableFilterExists('is_visible_to_employee');

        Livewire::test(TurnoAssignmentsRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => ViewUser::class,
        ])
            ->assertCanSeeTableRecords([$turnoAssignment])
            ->assertTableFilterExists('turno_id')
            ->assertTableFilterExists('active_now');
    }

    public function test_documents_relation_manager_allows_creating_documents_from_user_view(): void
    {
        Storage::fake(Document::STORAGE_DISK);

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

        $this->actingAs($companyAdmin);

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => ViewUser::class,
        ])
            ->assertTableHeaderActionsExistInOrder(['create'])
            ->callTableAction('create', data: [
                'folder' => DocumentFolder::Contracts->value,
                'name' => 'Contrato inicial',
                'file_path' => UploadedFile::fake()->create('contrato.pdf', 120, 'application/pdf'),
                'is_visible_to_employee' => true,
                'description' => 'Documento de prueba',
            ])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $document = Document::query()->first();

        $this->assertNotNull($document);
        $this->assertSame((string) $tenant->getKey(), (string) $document->tenant_id);
        $this->assertSame((string) $employee->getKey(), (string) $document->user_id);
        $this->assertSame((string) $companyAdmin->getKey(), (string) $document->uploaded_by_user_id);
        $this->assertStringStartsWith(
            'tenant/'.$tenant->getKey().'/'.DocumentFolder::Contracts->value.'/user/'.$employee->getKey().'/',
            $document->file_path,
        );
        Storage::disk(Document::STORAGE_DISK)->assertExists($document->file_path);
    }

    public function test_turno_assignments_relation_manager_allows_creating_assignments_from_user_view(): void
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

        $turno = Turno::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'name' => 'Horario de invierno',
        ]);

        $this->actingAs($companyAdmin);

        Livewire::test(TurnoAssignmentsRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => ViewUser::class,
        ])
            ->assertTableHeaderActionsExistInOrder(['create'])
            ->callTableAction('create', data: [
                'turno_id' => $turno->getKey(),
                'valid_from' => '2026-09-01',
                'valid_until' => '2026-12-31',
            ])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $assignment = TurnoAssignment::query()->first();

        $this->assertNotNull($assignment);
        $this->assertSame((string) $tenant->getKey(), (string) $assignment->tenant_id);
        $this->assertSame((string) $employee->getKey(), (string) $assignment->user_id);
        $this->assertSame((string) $turno->getKey(), (string) $assignment->turno_id);
    }

    public function test_turno_assignments_relation_manager_validates_vigency_range_from_user_view(): void
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

        $turno = Turno::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->actingAs($companyAdmin);

        Livewire::test(TurnoAssignmentsRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => ViewUser::class,
        ])
            ->callTableAction('create', data: [
                'turno_id' => $turno->getKey(),
                'valid_from' => '2026-09-01',
                'valid_until' => '2026-07-01',
            ])
            ->assertHasFormErrors(['valid_until']);

        $this->assertDatabaseCount('turno_assignments', 0);
    }

    private function createRoles(): void
    {
        foreach (['company-admin', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
