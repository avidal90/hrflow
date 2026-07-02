<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('avatar_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),
                TextEntry::make('tenant.name')
                    ->label('Empresa'),
                TextEntry::make('name')
                    ->label('Nombre'),
                TextEntry::make('email')
                    ->label('Email'),
                TextEntry::make('department.name')
                    ->label('Departamento'),
                TextEntry::make('role_name')
                    ->label('Rol')
                    ->state(fn (User $record): string => $record->primaryRoleLabel()),
                TextEntry::make('employee_code')
                    ->label('Codigo'),
                TextEntry::make('hire_date')
                    ->label('Fecha de alta')
                    ->date(),
                TextEntry::make('employment_status')
                    ->label('Estado laboral'),
                TextEntry::make('annual_vacation_days')
                    ->label('Dias de vacaciones asignados'),
                TextEntry::make('job_title')
                    ->label('Puesto')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ])
            ->columns(2);
    }
}
