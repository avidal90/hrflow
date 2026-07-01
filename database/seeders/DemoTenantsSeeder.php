<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoTenantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $northwind = Tenant::query()->firstOrCreate(
            ['id' => 'northwind-demo'],
            [
                'name' => 'Northwind HR',
                'status' => 'active',
                'locale' => 'es',
                'timezone' => 'Europe/Madrid',
                'employee_license_limit' => 25,
                'data' => [],
            ]
        );

        $acme = Tenant::query()->firstOrCreate(
            ['id' => 'acme-demo'],
            [
                'name' => 'Acme People',
                'status' => 'active',
                'locale' => 'es',
                'timezone' => 'Europe/Madrid',
                'employee_license_limit' => 25,
                'data' => [],
            ]
        );

        $northwindDepartments = $this->createDepartments($northwind, [
            'Direccion',
            'Recursos Humanos',
        ]);

        $acmeDepartments = $this->createDepartments($acme, [
            'Operaciones',
            'Administracion',
        ]);

        $superAdmin = User::query()->firstOrCreate(
            ['email' => 'admin@hrflow.local'],
            [
                'tenant_id' => $northwind->getKey(),
                'name' => 'Admin HRFlow',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $northwindCompanyAdmin = User::query()->firstOrCreate(
            ['email' => 'ana.gomez@northwind.local'],
            [
                'tenant_id' => $northwind->getKey(),
                'name' => 'Ana Gomez',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $northwindHr = User::query()->firstOrCreate(
            ['email' => 'luis.martin@northwind.local'],
            [
                'tenant_id' => $northwind->getKey(),
                'name' => 'Luis Martin',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $northwindManager = User::query()->firstOrCreate(
            ['email' => 'maria.santos@northwind.local'],
            [
                'tenant_id' => $northwind->getKey(),
                'name' => 'Maria Santos',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $northwindEmployee = User::query()->firstOrCreate(
            ['email' => 'javier.ramos@northwind.local'],
            [
                'tenant_id' => $northwind->getKey(),
                'name' => 'Javier Ramos',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $acmeCompanyAdmin = User::query()->firstOrCreate(
            ['email' => 'sofia.fernandez@acme.local'],
            [
                'tenant_id' => $acme->getKey(),
                'name' => 'Sofia Fernandez',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $acmeHr = User::query()->firstOrCreate(
            ['email' => 'carlos.ortega@acme.local'],
            [
                'tenant_id' => $acme->getKey(),
                'name' => 'Carlos Ortega',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $acmeManager = User::query()->firstOrCreate(
            ['email' => 'nuria.lopez@acme.local'],
            [
                'tenant_id' => $acme->getKey(),
                'name' => 'Nuria Lopez',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $superAdmin->syncRoles(['super-admin']);
        $northwindCompanyAdmin->syncRoles(['company-admin']);
        $northwindHr->syncRoles(['hr']);
        $northwindManager->syncRoles(['department-manager']);
        $northwindEmployee->syncRoles(['employee']);
        $acmeCompanyAdmin->syncRoles(['company-admin']);
        $acmeHr->syncRoles(['hr']);
        $acmeManager->syncRoles(['department-manager']);

        $this->hydrateUser($northwindCompanyAdmin, $northwindDepartments[0]->getKey(), 'NWD-001', 'Directora General');
        $this->hydrateUser($northwindHr, $northwindDepartments[1]->getKey(), 'NWD-002', 'Tecnico de RRHH');
        $this->hydrateUser($northwindManager, $northwindDepartments[1]->getKey(), 'NWD-003', 'Responsable de RRHH');
        $this->hydrateUser($northwindEmployee, $northwindDepartments[1]->getKey(), 'NWD-004', 'Empleado');

        $this->hydrateUser($acmeCompanyAdmin, $acmeDepartments[0]->getKey(), 'ACM-001', 'Directora de Operaciones');
        $this->hydrateUser($acmeHr, $acmeDepartments[1]->getKey(), 'ACM-002', 'Tecnico de Personas');
        $this->hydrateUser($acmeManager, $acmeDepartments[1]->getKey(), 'ACM-003', 'Responsable de Administracion');

        $northwindDepartments[0]->update(['manager_user_id' => $northwindCompanyAdmin->getKey()]);
        $acmeDepartments[0]->update(['manager_user_id' => $acmeCompanyAdmin->getKey()]);
    }

    /**
     * @param  array<int, string>  $names
     * @return array<int, Department>
     */
    private function createDepartments(Tenant $tenant, array $names): array
    {
        return collect($names)
            ->map(fn (string $name): Department => Department::query()->firstOrCreate(
                [
                    'tenant_id' => $tenant->getKey(),
                    'name' => $name,
                ],
                [
                    'manager_user_id' => null,
                ]
            ))
            ->all();
    }

    private function hydrateUser(User $user, int $departmentId, string $employeeCode, string $jobTitle): void
    {
        $user->forceFill([
            'department_id' => $departmentId,
            'employee_code' => $employeeCode,
            'hire_date' => now()->subMonths(fake()->numberBetween(6, 36))->toDateString(),
            'employment_status' => 'active',
            'job_title' => $jobTitle,
        ])->save();
    }
}
