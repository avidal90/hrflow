<?php

namespace App\Filament\Resources\Tenants\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la empresa')
                    ->collapsible()
                    ->schema([
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
                            ->disabled(fn (): bool => ! self::currentUserIsSuperAdmin())
                            ->dehydrated(fn (): bool => self::currentUserIsSuperAdmin())
                            ->helperText('Deja vacío este campo para licencias ilimitadas.')
                            ->default(null),
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
