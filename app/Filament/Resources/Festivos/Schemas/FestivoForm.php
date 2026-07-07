<?php

namespace App\Filament\Resources\Festivos\Schemas;

use App\Models\Tenant;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class FestivoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del festivo')
                    ->collapsible()
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Empresa')
                            ->options(fn (): array => Tenant::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->default(fn (): mixed => Auth::user()?->tenant_id)
                            ->disabled(fn (): bool => ! self::currentUserIsSuperAdmin())
                            ->dehydrated()
                            ->required(),
                        DatePicker::make('date')
                            ->label('Fecha festiva')
                            ->required()
                            ->native(false),
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
