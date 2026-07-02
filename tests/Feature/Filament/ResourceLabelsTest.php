<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Departments\DepartmentResource;
use App\Filament\Resources\Documents\DocumentResource;
use App\Filament\Resources\LeaveRequests\LeaveRequestResource;
use App\Filament\Resources\Tenants\TenantResource;
use App\Filament\Resources\TimeEntries\TimeEntryResource;
use App\Filament\Resources\Turnos\TurnoResource;
use App\Filament\Resources\Users\UserResource;
use Tests\TestCase;

class ResourceLabelsTest extends TestCase
{
    public function test_resources_expose_spanish_labels(): void
    {
        $this->assertSame('solicitud', LeaveRequestResource::getModelLabel());
        $this->assertSame('solicitudes', LeaveRequestResource::getPluralModelLabel());
        $this->assertSame('Solicitudes', LeaveRequestResource::getNavigationLabel());

        $this->assertSame('fichaje', TimeEntryResource::getModelLabel());
        $this->assertSame('fichajes', TimeEntryResource::getPluralModelLabel());
        $this->assertSame('Fichajes', TimeEntryResource::getNavigationLabel());

        $this->assertSame('usuario', UserResource::getModelLabel());
        $this->assertSame('usuarios', UserResource::getPluralModelLabel());
        $this->assertSame('Usuarios', UserResource::getNavigationLabel());

        $this->assertSame('documento', DocumentResource::getModelLabel());
        $this->assertSame('documentos', DocumentResource::getPluralModelLabel());
        $this->assertSame('Documentos', DocumentResource::getNavigationLabel());

        $this->assertSame('departamento', DepartmentResource::getModelLabel());
        $this->assertSame('departamentos', DepartmentResource::getPluralModelLabel());
        $this->assertSame('Departamentos', DepartmentResource::getNavigationLabel());

        $this->assertSame('empresa', TenantResource::getModelLabel());
        $this->assertSame('empresas', TenantResource::getPluralModelLabel());
        $this->assertSame('Empresas', TenantResource::getNavigationLabel());

        $this->assertSame('turno', TurnoResource::getModelLabel());
        $this->assertSame('turnos', TurnoResource::getPluralModelLabel());
        $this->assertSame('Turnos', TurnoResource::getNavigationLabel());
    }
}
