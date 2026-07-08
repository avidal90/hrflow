<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Acceso y perfil')
                    ->collapsible()
                    ->schema([
                        ImageEntry::make('avatar_path')
                            ->label('Foto')
                            ->state(fn (User $record): ?string => $record->getFilamentAvatarUrl())
                            ->circular()
                            ->visible(fn (User $record): bool => filled($record->avatar_path))
                            ->columnSpanFull(),
                        TextEntry::make('avatar_missing')
                            ->label('Foto')
                            ->state('Sin foto')
                            ->icon('heroicon-o-x-circle')
                            ->iconColor('gray')
                            ->visible(fn (User $record): bool => blank($record->avatar_path))
                            ->columnSpanFull(),
                        TextEntry::make('tenant.name')
                            ->label('Empresa'),
                        TextEntry::make('name')
                            ->label('Nombre'),
                        TextEntry::make('email')
                            ->label('Email'),
                    ])
                    ->columns(2),

                Section::make('Datos personales')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('phone_personal')
                            ->label('Teléfono de contacto')
                            ->placeholder('-'),
                        TextEntry::make('phone_company')
                            ->label('Teléfono de empresa')
                            ->placeholder('-'),
                        TextEntry::make('birth_date')
                            ->label('Fecha de nacimiento')
                            ->date('d/m/Y')
                            ->placeholder('-'),
                        TextEntry::make('national_id')
                            ->label('DNI / NIF')
                            ->state(fn (User $record): ?string => $record->maskedNationalId())
                            ->placeholder('-'),
                        TextEntry::make('social_security_number')
                            ->label('Número de Seguridad Social')
                            ->state(fn (User $record): ?string => $record->maskedSocialSecurityNumber())
                            ->placeholder('-'),
                        TextEntry::make('birth_country')
                            ->label('País de nacimiento')
                            ->placeholder('-'),
                        TextEntry::make('address')
                            ->label('Dirección')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Datos laborales')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('department.name')
                            ->label('Departamento')
                            ->placeholder('-'),
                        TextEntry::make('role_name')
                            ->label('Rol')
                            ->state(fn (User $record): string => $record->primaryRoleLabel()),
                        TextEntry::make('employee_code')
                            ->label('Código de empleado'),
                        TextEntry::make('job_title')
                            ->label('Puesto')
                            ->placeholder('-'),
                        TextEntry::make('hire_date')
                            ->label('Fecha de alta')
                            ->date('d/m/Y'),
                        TextEntry::make('employment_status')
                            ->label('Estado laboral')
                            ->formatStateUsing(fn (string $state): string => self::employmentStatusBadge($state))
                            ->html(),
                        TextEntry::make('annual_vacation_days')
                            ->label('Días de vacaciones asignados'),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    private static function employmentStatusBadge(string $state): string
    {
        $label = match ($state) {
            'active' => 'Activo',
            'inactive' => 'Inactivo',
            'on_leave' => 'De baja',
            'terminated' => 'Finalizado',
            default => $state,
        };

        $classes = match ($state) {
            'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'inactive' => 'bg-red-50 text-red-700 ring-red-200',
            'on_leave' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'terminated' => 'bg-slate-100 text-slate-700 ring-slate-200',
            default => 'bg-slate-100 text-slate-700 ring-slate-200',
        };

        return '<span class="inline-flex w-fit rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 '.$classes.'">'.$label.'</span>';
    }
}
