<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activa',
                        'inactive' => 'Inactiva',
                        'suspended' => 'Suspendida',
                    ])
                    ->required()
                    ->default('active'),
                TextInput::make('locale')
                    ->label('Idioma')
                    ->required()
                    ->maxLength(10)
                    ->default('es'),
                TextInput::make('timezone')
                    ->label('Zona horaria')
                    ->required()
                    ->default('Europe/Madrid'),
                TextInput::make('employee_license_limit')
                    ->label('Licencias de empleados')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->nullable()
                    ->helperText('Deja vacio este campo para licencias ilimitadas.')
                    ->default(null),
            ])
            ->columns(2);
    }
}
