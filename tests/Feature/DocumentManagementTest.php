<?php

namespace Tests\Feature;

use App\Enums\DocumentFolder;
use App\Filament\Resources\Documents\DocumentResource;
use App\Filament\Resources\Documents\Pages\CreateDocument;
use App\Models\Document;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_hr_can_create_a_document_from_filament_and_store_it_privately(): void
    {
        Storage::fake(Document::STORAGE_DISK);

        [$tenant, $hrUser, $employee] = $this->createTenantWithHrAndEmployee();

        $this->actingAs($hrUser);

        Livewire::test(CreateDocument::class)
            ->fillForm([
                'tenant_id' => $tenant->getKey(),
                'user_id' => $employee->getKey(),
                'folder' => DocumentFolder::Payrolls->value,
                'name' => 'Nomina junio 2026',
                'description' => 'Recibo salarial mensual.',
                'is_visible_to_employee' => true,
                'file_path' => UploadedFile::fake()->create('nomina-junio-2026.pdf', 512, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $document = Document::query()->firstOrFail();

        $this->assertSame((string) $tenant->getKey(), (string) $document->tenant_id);
        $this->assertSame($employee->getKey(), $document->user_id);
        $this->assertSame($hrUser->getKey(), $document->uploaded_by_user_id);
        $this->assertSame(DocumentFolder::Payrolls, $document->folder);
        $this->assertSame(Document::STORAGE_DISK, $document->disk);
        $this->assertSame('nomina-junio-2026.pdf', $document->original_filename);
        $this->assertNotNull($document->uploaded_at);
        $this->assertStringStartsWith(
            'tenant/'.$tenant->getKey().'/'.DocumentFolder::Payrolls->value.'/user/'.$employee->getKey().'/',
            $document->file_path,
        );
        $this->assertTrue(Storage::disk(Document::STORAGE_DISK)->exists($document->file_path));
    }

    public function test_document_upload_rejects_files_larger_than_twenty_megabytes(): void
    {
        Storage::fake(Document::STORAGE_DISK);

        [$tenant, $hrUser, $employee] = $this->createTenantWithHrAndEmployee();

        $this->actingAs($hrUser);

        Livewire::test(CreateDocument::class)
            ->fillForm([
                'tenant_id' => $tenant->getKey(),
                'user_id' => $employee->getKey(),
                'folder' => DocumentFolder::Contracts->value,
                'name' => 'Contrato principal',
                'file_path' => UploadedFile::fake()->create('contrato.pdf', 21000, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_employee_and_department_manager_cannot_create_documents(): void
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $employee->assignRole('employee');

        $manager = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $manager->assignRole('department-manager');

        $this->assertTrue(Gate::forUser($employee)->denies('create', Document::class));
        $this->assertTrue(Gate::forUser($manager)->denies('create', Document::class));
    }

    public function test_document_resource_query_is_limited_to_the_current_tenant_and_not_available_to_department_managers(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();

        $this->createRoles();

        $hrUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $hrUser->assignRole('hr');

        $manager = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $manager->assignRole('department-manager');

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $otherEmployee = User::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
        ]);

        $visibleDocument = Document::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'uploaded_by_user_id' => $hrUser->getKey(),
        ]);

        $hiddenDocument = Document::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
            'user_id' => $otherEmployee->getKey(),
        ]);

        $this->actingAs($hrUser);

        $this->assertSame([
            $visibleDocument->getKey(),
        ], DocumentResource::getEloquentQuery()->pluck('id')->all());

        $this->assertNotContains($hiddenDocument->getKey(), DocumentResource::getEloquentQuery()->pluck('id')->all());

        $this->actingAs($manager);

        $this->assertSame([], DocumentResource::getEloquentQuery()->pluck('id')->all());
    }

    private function createRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    /**
     * @return array{0: Tenant, 1: User, 2: User}
     */
    private function createTenantWithHrAndEmployee(): array
    {
        $tenant = Tenant::factory()->create();

        $this->createRoles();

        $hrUser = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $hrUser->assignRole('hr');

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $employee->assignRole('employee');

        return [$tenant, $hrUser, $employee];
    }
}
