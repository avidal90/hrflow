<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionsByRole = [
            'super-admin' => [
                'tenant.view',
                'tenant.update',
                'department.view',
                'department.create',
                'department.update',
                'department.delete',
                'employee.view',
                'employee.create',
                'employee.update',
                'employee.delete',
                'turno.view',
                'turno.create',
                'turno.update',
                'turno.delete',
                'time-entry.view',
                'time-entry.create',
                'time-entry.update',
                'time-entry.delete',
                'leave-request.view',
                'leave-request.create',
                'leave-request.update',
                'leave-request.delete',
                'document.view',
                'document.create',
                'document.update',
                'document.delete',
            ],
            'company-admin' => [
                'tenant.view',
                'tenant.update',
                'department.view',
                'department.create',
                'department.update',
                'department.delete',
                'employee.view',
                'employee.create',
                'employee.update',
                'employee.delete',
                'turno.view',
                'turno.create',
                'turno.update',
                'turno.delete',
                'time-entry.view',
                'time-entry.create',
                'time-entry.update',
                'time-entry.delete',
                'leave-request.view',
                'leave-request.create',
                'leave-request.update',
                'leave-request.delete',
                'document.view',
                'document.create',
                'document.update',
                'document.delete',
            ],
            'hr' => [
                'tenant.view',
                'department.view',
                'department.create',
                'department.update',
                'employee.view',
                'employee.create',
                'employee.update',
                'turno.view',
                'turno.create',
                'turno.update',
                'time-entry.view',
                'time-entry.create',
                'time-entry.update',
                'leave-request.view',
                'leave-request.create',
                'leave-request.update',
                'document.view',
                'document.create',
                'document.update',
            ],
            'department-manager' => [
                'department.view',
                'department.update',
                'employee.view',
                'employee.update',
                'turno.view',
                'time-entry.view',
                'leave-request.view',
                'leave-request.update',
                'document.view',
            ],
            'employee' => [
                'turno.view',
                'time-entry.view',
                'time-entry.create',
                'time-entry.update',
                'leave-request.view',
                'leave-request.create',
                'leave-request.update',
                'document.view',
            ],
        ];

        $roleNames = array_keys($permissionsByRole);
        $permissionNames = collect($permissionsByRole)->flatten()->unique()->values();

        $rolesAlreadySeeded = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $roleNames)
            ->count() === count($roleNames);

        $permissionsAlreadySeeded = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $permissionNames->all())
            ->count() === $permissionNames->count();

        if ($rolesAlreadySeeded && $permissionsAlreadySeeded) {
            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect($permissionsByRole)
            ->flatten()
            ->unique()
            ->mapWithKeys(fn (string $permission): array => [
                $permission => Permission::findOrCreate($permission, 'web'),
            ]);

        foreach ($permissionsByRole as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions(collect($rolePermissions)->map(fn (string $permission) => $permissions[$permission]));
        }

        $superAdmin = User::query()
            ->where('email', 'admin@hrflow.local')
            ->first();

        if ($superAdmin === null) {
            $superAdmin = User::query()
                ->withCount('roles')
                ->orderByDesc('roles_count')
                ->orderBy('id')
                ->first();
        }

        if ($superAdmin instanceof User) {
            $superAdmin->forceFill([
                'tenant_id' => Tenant::ensurePrincipalTenant()->getKey(),
            ])->save();
            $superAdmin->assignRole('super-admin');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
