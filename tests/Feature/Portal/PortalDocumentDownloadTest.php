<?php

namespace Tests\Feature\Portal;

use App\Enums\DocumentFolder;
use App\Models\Document;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalDocumentDownloadTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_tenant_login_when_trying_to_download_a_document(): void
    {
        $tenant = Tenant::factory()->create();

        $document = Document::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->get($this->portalDownloadRoute($tenant, $document))
            ->assertRedirect($this->portalRoute($tenant, '/login'));
    }

    public function test_employee_can_download_their_own_visible_document(): void
    {
        Storage::fake(Document::STORAGE_DISK);

        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $employee->assignRole('employee');

        $document = Document::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'uploaded_by_user_id' => $employee->getKey(),
            'folder' => DocumentFolder::Payrolls->value,
            'original_filename' => 'nomina-junio-2026.pdf',
            'file_path' => 'tenant/'.$tenant->getKey().'/payrolls/user/'.$employee->getKey().'/nomina-junio-2026.pdf',
            'is_visible_to_employee' => true,
        ]);

        Storage::disk(Document::STORAGE_DISK)->put($document->file_path, 'pdf-content');

        $this->actingAs($employee)
            ->get($this->portalDownloadRoute($tenant, $document))
            ->assertOk()
            ->assertDownload('nomina-junio-2026.pdf');
    }

    public function test_employee_cannot_download_another_employees_document(): void
    {
        Storage::fake(Document::STORAGE_DISK);

        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $employee->assignRole('employee');

        $otherEmployee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $otherEmployee->assignRole('employee');

        $document = Document::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $otherEmployee->getKey(),
            'uploaded_by_user_id' => $otherEmployee->getKey(),
            'original_filename' => 'contrato.pdf',
            'file_path' => 'tenant/'.$tenant->getKey().'/contracts/user/'.$otherEmployee->getKey().'/contrato.pdf',
            'is_visible_to_employee' => true,
        ]);

        Storage::disk(Document::STORAGE_DISK)->put($document->file_path, 'pdf-content');

        $this->actingAs($employee)
            ->get($this->portalDownloadRoute($tenant, $document))
            ->assertForbidden();
    }

    public function test_employee_cannot_download_their_own_hidden_document(): void
    {
        Storage::fake(Document::STORAGE_DISK);

        $tenant = Tenant::factory()->create();
        $this->createRoles();

        $employee = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
        ]);
        $employee->assignRole('employee');

        $document = Document::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'user_id' => $employee->getKey(),
            'uploaded_by_user_id' => $employee->getKey(),
            'folder' => DocumentFolder::Contracts->value,
            'original_filename' => 'contrato-privado.pdf',
            'file_path' => 'tenant/'.$tenant->getKey().'/contracts/user/'.$employee->getKey().'/contrato-privado.pdf',
            'is_visible_to_employee' => false,
        ]);

        Storage::disk(Document::STORAGE_DISK)->put($document->file_path, 'pdf-content');

        $this->actingAs($employee)
            ->get($this->portalDownloadRoute($tenant, $document))
            ->assertForbidden();
    }

    private function createRoles(): void
    {
        foreach (['super-admin', 'company-admin', 'hr', 'department-manager', 'employee'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function portalRoute(Tenant $tenant, string $suffix): string
    {
        return '/portal/'.$tenant->getKey().$suffix;
    }

    private function portalDownloadRoute(Tenant $tenant, Document $document): string
    {
        return $this->portalRoute($tenant, '/documentacion/descargar/'.$document->getKey());
    }
}
