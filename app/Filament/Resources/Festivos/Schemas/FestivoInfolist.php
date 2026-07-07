<?php

namespace App\Filament\Resources\Festivos\Schemas;

use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class FestivoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del festivo')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('tenant.name')
                            ->label('Empresa')
                            ->visible(fn (): bool => self::currentUserIsSuperAdmin()),
                        TextEntry::make('date')
                            ->label('Fecha festiva')
                            ->date('d/m/Y'),
                        TextEntry::make('created_at')
                            ->label('Creado')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Actualizado')
                            ->dateTime()
                            ->placeholder('-'),
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
