<?php

namespace App\Filament\Resources\Turnos\Schemas;

use App\Models\Tenant;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TurnoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tenant_id')
                    ->label('Empresa')
                    ->options(fn (): array => Tenant::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->default(fn (): mixed => Auth::user()?->tenant_id)
                    ->disabled(fn (): bool => ! self::currentUserIsSuperAdmin())
                    ->dehydrated()
                    ->live()
                    ->required(),
                TextInput::make('name')
                    ->label('Nombre del turno')
                    ->required()
                    ->maxLength(255),
                TimePicker::make('start_time')
                    ->label('Hora de inicio')
                    ->required()
                    ->seconds(false),
                TimePicker::make('end_time')
                    ->label('Hora de finalizacion')
                    ->required()
                    ->seconds(false),
                TextInput::make('break_minutes')
                    ->label('Tiempo de descanso (min)')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                TextInput::make('total_hours')
                    ->label('Total horas jornada')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Se calcula automaticamente al guardar.'),
            ])
            ->columns(2);
    }

    private static function currentUserIsSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isSuperAdmin();
    }
}
