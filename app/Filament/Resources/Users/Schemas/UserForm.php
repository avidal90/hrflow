<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Department;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Validation\PasswordRules;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Acceso y perfil')
                    ->collapsible()
                    ->schema([
                        FileUpload::make('avatar_path')
                            ->label('Foto de perfil')
                            ->avatar()
                            ->image()
                            ->imageEditor()
                            ->disk('avatars')
                            ->directory(function (Get $get, ?Model $record): string {
                                $tenantId = $get('tenant_id') ?? $record?->getAttribute('tenant_id') ?? 'shared';

                                return 'tenant/'.$tenantId;
                            })
                            ->visibility('private')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->columnSpanFull(),
                        Select::make('tenant_id')
                            ->label('Empresa')
                            ->options(fn (): array => Tenant::query()
                                ->when(! self::currentUserIsSuperAdmin(), fn (Builder $query) => $query->whereKeyNot(Tenant::principalTenantId()))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->default(fn (): mixed => Auth::user()?->tenant_id)
                            ->disabled(fn (): bool => ! self::currentUserIsSuperAdmin())
                            ->dehydrated()
                            ->live()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nombre completo')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->required()
                            ->email()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->visible(fn (string $operation): bool => $operation === 'create')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->rule(PasswordRules::user())
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Datos personales')
                    ->collapsible()
                    ->schema([
                        TextInput::make('phone_personal')
                            ->label('Teléfono de contacto')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('phone_company')
                            ->label('Teléfono de empresa')
                            ->tel()
                            ->maxLength(20),
                        DatePicker::make('birth_date')
                            ->label('Fecha de nacimiento'),
                        TextInput::make('national_id')
                            ->label('DNI / NIF')
                            ->maxLength(20),
                        TextInput::make('social_security_number')
                            ->label('Número de Seguridad Social')
                            ->maxLength(50),
                        TextInput::make('birth_country')
                            ->label('País de nacimiento')
                            ->maxLength(100),
                        TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Datos laborales')
                    ->collapsible()
                    ->schema([
                        Select::make('department_id')
                            ->label('Departamento')
                            ->relationship(
                                name: 'department',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, Get $get): void {
                                    $tenantId = $get('tenant_id');

                                    if (filled($tenantId)) {
                                        $query->where('tenant_id', $tenantId);
                                    }

                                    $query->orderBy('name');
                                },
                            )
                            ->getOptionLabelFromRecordUsing(fn (Model $record): string => $record instanceof Department
                                ? $record->name
                                : (string) $record->getKey())
                            ->searchable(['name'])
                            ->preload()
                            ->required(),
                        Select::make('role_name')
                            ->label('Rol')
                            ->options(fn (?User $record): array => User::assignableRoleOptionsFor(Auth::user(), $record))
                            ->default('employee')
                            ->required()
                            ->searchable()
                            ->disabled(fn (?User $record): bool => ! User::canManageRoleAssignments(Auth::user(), $record))
                            ->afterStateHydrated(function (Select $component, ?User $record): void {
                                if ($record instanceof User) {
                                    $component->state($record->primaryRoleName());
                                }
                            }),
                        TextInput::make('employee_code')
                            ->label('Código de empleado')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('job_title')
                            ->label('Puesto')
                            ->maxLength(255),
                        DatePicker::make('hire_date')
                            ->label('Fecha de alta')
                            ->required(),
                        Select::make('employment_status')
                            ->label('Estado laboral')
                            ->options([
                                'active' => 'Activo',
                                'inactive' => 'Inactivo',
                                'on_leave' => 'De baja',
                                'terminated' => 'Finalizado',
                            ])
                            ->required()
                            ->default('active'),
                        TextInput::make('annual_vacation_days')
                            ->label('Días de vacaciones asignados')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(365)
                            ->required()
                            ->default(23),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    private static function currentUserIsSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isSuperAdmin();
    }
}
