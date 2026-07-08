<?php

namespace Database\Seeders;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Tenant;
use App\Models\TimeEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoTenantsSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'Hr@Flow2026!';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if ($this->demoDataAlreadySeeded()) {
            return;
        }

        $principalTenant = Tenant::ensurePrincipalTenant();

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

        $northwindDepartments = $this->createDepartments($northwind, ['Direccion', 'Recursos Humanos']);
        $acmeDepartments = $this->createDepartments($acme, ['Operaciones', 'Administracion']);

        // ─── Super Admin ────────────────────────────────────────────────────────────
        $superAdmin = User::query()->firstOrCreate(
            ['email' => 'admin@hrflow.local'],
            [
                'tenant_id' => $principalTenant->getKey(),
                'name' => 'Admin HRFlow',
                'password' => self::DEMO_PASSWORD,
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->forceFill(['tenant_id' => $principalTenant->getKey()])->save();
        $superAdmin->syncRoles(['super-admin']);

        // ─── Northwind HR ────────────────────────────────────────────────────────────
        $northwindAdmin = $this->createUser('ana.gomez@northwind.local', 'Ana Gómez', $northwind, [
            'phone_personal' => '+34 611 234 567',
            'phone_company' => '+34 91 234 56 78',
            'birth_date' => '1980-05-22',
            'national_id' => '12345678A',
            'social_security_number' => '280123456789',
            'birth_country' => 'España',
            'address' => 'Calle Mayor 15, 2ºA, Madrid',
        ]);

        $northwindHr = $this->createUser('luis.martin@northwind.local', 'Luis Martín', $northwind, [
            'phone_personal' => '+34 622 345 678',
            'phone_company' => '+34 91 234 56 79',
            'birth_date' => '1988-11-14',
            'national_id' => '23456789B',
            'social_security_number' => '280234567890',
            'birth_country' => 'España',
            'address' => 'Avenida de la Paz 8, 3ºB, Madrid',
        ]);

        $northwindManager = $this->createUser('maria.santos@northwind.local', 'María Santos', $northwind, [
            'phone_personal' => '+34 633 456 789',
            'phone_company' => '+34 91 234 56 80',
            'birth_date' => '1985-03-28',
            'national_id' => '34567890C',
            'social_security_number' => '280345678901',
            'birth_country' => 'España',
            'address' => 'Calle Serrano 45, 1ºD, Madrid',
        ]);

        $northwindEmployee = $this->createUser('javier.ramos@northwind.local', 'Javier Ramos', $northwind, [
            'phone_personal' => '+34 644 567 890',
            'phone_company' => '+34 91 234 56 81',
            'birth_date' => '1995-07-09',
            'national_id' => '45678901D',
            'social_security_number' => '280456789012',
            'birth_country' => 'España',
            'address' => 'Calle Fuencarral 112, 4ºC, Madrid',
        ]);

        // ─── Acme People ─────────────────────────────────────────────────────────────
        $acmeAdmin = $this->createUser('sofia.fernandez@acme.local', 'Sofía Fernández', $acme, [
            'phone_personal' => '+34 655 678 901',
            'phone_company' => '+34 93 345 67 89',
            'birth_date' => '1979-08-17',
            'national_id' => '56789012E',
            'social_security_number' => '080567890123',
            'birth_country' => 'España',
            'address' => 'Paseo de Gracia 88, 5ºA, Barcelona',
        ]);

        $acmeHr = $this->createUser('carlos.ortega@acme.local', 'Carlos Ortega', $acme, [
            'phone_personal' => '+34 666 789 012',
            'phone_company' => '+34 93 345 67 90',
            'birth_date' => '1990-12-03',
            'national_id' => '67890123F',
            'social_security_number' => '080678901234',
            'birth_country' => 'España',
            'address' => 'Carrer de Balmes 54, 2ºB, Barcelona',
        ]);

        $acmeManager = $this->createUser('nuria.lopez@acme.local', 'Nuria López', $acme, [
            'phone_personal' => '+34 677 890 123',
            'phone_company' => '+34 93 345 67 91',
            'birth_date' => '1987-02-20',
            'national_id' => '78901234G',
            'social_security_number' => '080789012345',
            'birth_country' => 'España',
            'address' => "Carrer d'Aragó 320, 3ºC, Barcelona",
        ]);

        $acmeEmployee = $this->createUser('pablo.herrero@acme.local', 'Pablo Herrero', $acme, [
            'phone_personal' => '+34 688 901 234',
            'phone_company' => '+34 93 345 67 92',
            'birth_date' => '1998-04-15',
            'national_id' => '89012345H',
            'social_security_number' => '080890123456',
            'birth_country' => 'España',
            'address' => 'Carrer de Provença 210, 1ºA, Barcelona',
        ]);

        // ─── Roles ───────────────────────────────────────────────────────────────────
        $northwindAdmin->syncRoles(['company-admin']);
        $northwindHr->syncRoles(['hr']);
        $northwindManager->syncRoles(['department-manager']);
        $northwindEmployee->syncRoles(['employee']);
        $acmeAdmin->syncRoles(['company-admin']);
        $acmeHr->syncRoles(['hr']);
        $acmeManager->syncRoles(['department-manager']);
        $acmeEmployee->syncRoles(['employee']);

        // ─── Perfiles laborales ──────────────────────────────────────────────────────
        $this->hydrateUser($northwindAdmin, $northwindDepartments[0]->getKey(), 'NWD-001', 'Directora General', '2023-01-10', 23);
        $this->hydrateUser($northwindHr, $northwindDepartments[1]->getKey(), 'NWD-002', 'Técnico de RRHH', '2023-06-01', 22);
        $this->hydrateUser($northwindManager, $northwindDepartments[1]->getKey(), 'NWD-003', 'Responsable de RRHH', '2022-09-15', 22);
        $this->hydrateUser($northwindEmployee, $northwindDepartments[1]->getKey(), 'NWD-004', 'Administrativo de RRHH', '2024-03-01', 22);

        $this->hydrateUser($acmeAdmin, $acmeDepartments[0]->getKey(), 'ACM-001', 'Directora de Operaciones', '2022-04-01', 23);
        $this->hydrateUser($acmeHr, $acmeDepartments[1]->getKey(), 'ACM-002', 'Técnico de Personas', '2023-02-14', 22);
        $this->hydrateUser($acmeManager, $acmeDepartments[1]->getKey(), 'ACM-003', 'Responsable de Administración', '2022-11-01', 22);
        $this->hydrateUser($acmeEmployee, $acmeDepartments[1]->getKey(), 'ACM-004', 'Auxiliar Administrativo', '2024-06-03', 22);

        // ─── Responsables de departamento ────────────────────────────────────────────
        $northwindDepartments[0]->update(['manager_user_id' => $northwindAdmin->getKey()]);
        $northwindDepartments[1]->update(['manager_user_id' => $northwindManager->getKey()]);
        $acmeDepartments[0]->update(['manager_user_id' => $acmeAdmin->getKey()]);
        $acmeDepartments[1]->update(['manager_user_id' => $acmeManager->getKey()]);

        // ─── Solicitudes de ausencia (empleados) ─────────────────────────────────────
        $this->seedLeaveRequests($northwindEmployee, $northwind, $northwindManager);
        $this->seedLeaveRequests($acmeEmployee, $acme, $acmeManager);

        // ─── Registros horarios pasados (empleados) ──────────────────────────────────
        $this->seedTimeEntries($northwindEmployee, $northwind, '09:00:00', '17:30:00');
        $this->seedTimeEntries($acmeEmployee, $acme, '08:30:00', '17:00:00');
    }

    private function demoDataAlreadySeeded(): bool
    {
        $demoTenantIds = ['northwind-demo', 'acme-demo'];

        $demoUserEmails = [
            'ana.gomez@northwind.local',
            'luis.martin@northwind.local',
            'maria.santos@northwind.local',
            'javier.ramos@northwind.local',
            'sofia.fernandez@acme.local',
            'carlos.ortega@acme.local',
            'nuria.lopez@acme.local',
            'pablo.herrero@acme.local',
        ];

        $tenantsExist = Tenant::query()
            ->whereIn('id', $demoTenantIds)
            ->count() === count($demoTenantIds);

        $usersExist = User::query()
            ->whereIn('email', $demoUserEmails)
            ->count() === count($demoUserEmails);

        return $tenantsExist && $usersExist;
    }

    /**
     * @param  array<int, string>  $names
     * @return array<int, Department>
     */
    private function createDepartments(Tenant $tenant, array $names): array
    {
        return collect($names)
            ->map(fn (string $name): Department => Department::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'name' => $name],
                ['manager_user_id' => null]
            ))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $personalData
     */
    private function createUser(string $email, string $name, Tenant $tenant, array $personalData): User
    {
        return User::query()->firstOrCreate(
            ['email' => $email],
            array_merge(
                [
                    'tenant_id' => $tenant->getKey(),
                    'name' => $name,
                    'password' => self::DEMO_PASSWORD,
                    'email_verified_at' => now(),
                ],
                $personalData
            )
        );
    }

    private function hydrateUser(
        User $user,
        int $departmentId,
        string $employeeCode,
        string $jobTitle,
        string $hireDate,
        int $annualVacationDays
    ): void {
        $user->forceFill([
            'department_id' => $departmentId,
            'employee_code' => $employeeCode,
            'hire_date' => $hireDate,
            'employment_status' => 'active',
            'job_title' => $jobTitle,
            'annual_vacation_days' => $annualVacationDays,
        ])->save();
    }

    private function seedLeaveRequests(User $employee, Tenant $tenant, User $manager): void
    {
        $tenantId = $tenant->getKey();
        $userId = $employee->getKey();
        $managerId = $manager->getKey();

        // Vacaciones de invierno — aprobadas
        LeaveRequest::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'user_id' => $userId, 'start_date' => '2026-01-05'],
            [
                'end_date' => '2026-01-10',
                'request_type' => LeaveRequestType::Vacation->value,
                'reason' => 'Vacaciones de invierno. Viaje familiar programado.',
                'status' => LeaveRequestStatus::Approved->value,
                'resolved_by_user_id' => $managerId,
                'resolved_at' => '2025-12-19 10:00:00',
                'manager_comment' => 'Aprobado. Que disfrutes las vacaciones.',
            ]
        );

        // Permiso retribuido — aprobado
        LeaveRequest::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'user_id' => $userId, 'start_date' => '2026-03-19'],
            [
                'end_date' => '2026-03-21',
                'request_type' => LeaveRequestType::PaidLeave->value,
                'reason' => 'Asuntos personales de carácter familiar urgente.',
                'status' => LeaveRequestStatus::Approved->value,
                'resolved_by_user_id' => $managerId,
                'resolved_at' => '2026-03-10 11:30:00',
                'manager_comment' => 'Aprobado.',
            ]
        );

        // Vacaciones de verano previas — aprobadas
        LeaveRequest::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'user_id' => $userId, 'start_date' => '2026-06-23'],
            [
                'end_date' => '2026-06-27',
                'request_type' => LeaveRequestType::Vacation->value,
                'reason' => 'Viaje programado con anticipación.',
                'status' => LeaveRequestStatus::Approved->value,
                'resolved_by_user_id' => $managerId,
                'resolved_at' => '2026-06-05 09:15:00',
                'manager_comment' => 'Sin incidencias, aprobado.',
            ]
        );

        // Vacaciones de verano — pendiente de aprobación
        LeaveRequest::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'user_id' => $userId, 'start_date' => '2026-08-04'],
            [
                'end_date' => '2026-08-15',
                'request_type' => LeaveRequestType::Vacation->value,
                'reason' => 'Vacaciones anuales de verano.',
                'status' => LeaveRequestStatus::Pending->value,
                'resolved_by_user_id' => null,
                'resolved_at' => null,
                'manager_comment' => null,
            ]
        );
    }

    private function seedTimeEntries(
        User $employee,
        Tenant $tenant,
        string $defaultCheckIn,
        string $defaultCheckOut
    ): void {
        $workDays = $this->recentWorkDays(15);

        $variants = [
            [$defaultCheckIn, $defaultCheckOut],
            [$this->shiftTime($defaultCheckIn, -10), $this->shiftTime($defaultCheckOut, -15)],
            [$this->shiftTime($defaultCheckIn, 5), $this->shiftTime($defaultCheckOut, 30)],
            [$this->shiftTime($defaultCheckIn, -5), $defaultCheckOut],
            [$defaultCheckIn, $this->shiftTime($defaultCheckOut, -30)],
        ];

        foreach ($workDays as $index => $date) {
            $pair = $variants[$index % count($variants)];

            TimeEntry::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'user_id' => $employee->getKey(), 'work_date' => $date],
                [
                    'check_in_time' => $pair[0],
                    'check_out_time' => $pair[1],
                ]
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private function recentWorkDays(int $count): array
    {
        $days = [];
        $date = Carbon::yesterday();

        while (count($days) < $count) {
            if (! $date->isWeekend()) {
                $days[] = $date->toDateString();
            }
            $date->subDay();
        }

        return $days;
    }

    private function shiftTime(string $time, int $minutes): string
    {
        return Carbon::createFromFormat('H:i:s', $time)
            ->addMinutes($minutes)
            ->format('H:i:s');
    }
}
