<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tenant.name')
                    ->label('Empresa'),
                TextEntry::make('user.name')
                    ->label('Usuario')
                    ->placeholder('-'),
                TextEntry::make('department.name')
                    ->label('Departamento'),
                TextEntry::make('employee_code')
                    ->label('Codigo'),
                TextEntry::make('first_name')
                    ->label('Nombre'),
                TextEntry::make('last_name')
                    ->label('Apellidos'),
                TextEntry::make('hire_date')
                    ->label('Fecha de alta')
                    ->date(),
                TextEntry::make('employment_status')
                    ->label('Estado laboral'),
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
