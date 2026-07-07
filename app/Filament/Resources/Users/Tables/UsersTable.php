<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\Department;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label('Foto')
                    ->state(fn (User $record): ?string => $record->getFilamentAvatarUrl())
                    ->circular()
                    ->defaultImageUrl(null),
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->visible(fn (): bool => self::currentUserIsSuperAdmin())
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('department.name')
                    ->label('Departamento')
                    ->searchable(),
                TextColumn::make('role_name')
                    ->label('Rol')
                    ->state(fn (User $record): string => $record->primaryRoleLabel())
                    ->badge(),
                TextColumn::make('employee_code')
                    ->label('Codigo')
                    ->searchable(),
                TextColumn::make('hire_date')
                    ->label('Fecha de alta')
                    ->date()
                    ->sortable(),
                TextColumn::make('employment_status')
                    ->label('Estado laboral')
                    ->badge()
                    ->searchable(),
                TextColumn::make('annual_vacation_days')
                    ->label('Vacaciones')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('job_title')
                    ->label('Puesto')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tenant_id')
                    ->label('Empresa')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (): bool => self::currentUserIsSuperAdmin()),
                SelectFilter::make('department_id')
                    ->label('Departamento')
                    ->options(fn (): array => self::departmentOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        $departmentId = $data['value'] ?? null;

                        if (blank($departmentId)) {
                            return $query;
                        }

                        return $query->where('department_id', $departmentId);
                    }),
                SelectFilter::make('employment_status')
                    ->label('Estado laboral')
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'on_leave' => 'De baja',
                        'terminated' => 'Finalizado',
                    ]),
                SelectFilter::make('role')
                    ->label('Rol')
                    ->options([
                        'super-admin' => 'Superadministrador',
                        'company-admin' => 'Administrador de empresa',
                        'hr' => 'Recursos humanos',
                        'department-manager' => 'Responsable de departamento',
                        'employee' => 'Empleado',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $roleName = $data['value'] ?? null;

                        if (blank($roleName)) {
                            return $query;
                        }

                        return $query->whereHas('roles', function (Builder $roleQuery) use ($roleName): void {
                            $roleQuery->where('name', $roleName);
                        });
                    }),
            ])
            ->defaultSort('name')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function currentUserIsSuperAdmin(): bool
    {
        return self::currentUser()?->isSuperAdmin() ?? false;
    }

    private static function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    /**
     * @return array<int|string, string>
     */
    private static function departmentOptions(): array
    {
        $user = self::currentUser();

        if (! $user instanceof User) {
            return [];
        }

        return Department::query()
            ->visibleTo($user)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
