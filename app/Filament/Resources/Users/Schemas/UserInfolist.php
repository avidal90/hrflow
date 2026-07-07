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
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => 'Activo',
                                'inactive' => 'Inactivo',
                                'on_leave' => 'De baja',
                                'terminated' => 'Finalizado',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'inactive' => 'danger',
                                'on_leave' => 'warning',
                                'terminated' => 'gray',
                                default => 'gray',
                            }),
                        TextEntry::make('annual_vacation_days')
                            ->label('Días de vacaciones asignados'),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }
}
