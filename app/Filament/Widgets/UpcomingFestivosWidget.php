<?php

namespace App\Filament\Widgets;

use App\Models\Festivo;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;

class UpcomingFestivosWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Próximos días festivos';

    public function table(Table $table): Table
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        return $table
            ->query(
                Festivo::query()
                    ->where('tenant_id', $currentUser->tenant_id)
                    ->whereDate('date', '>=', today())
                    ->orderBy('date')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('date')
                    ->label('Fecha')
                    ->formatStateUsing(fn ($state): string => ucfirst($state->translatedFormat('l, j \d\e F'))),
                TextColumn::make('days_until')
                    ->label('En')
                    ->state(function (Festivo $record): string {
                        $days = (int) today()->diffInDays($record->date);

                        return match (true) {
                            $days === 0 => 'Hoy',
                            $days === 1 => 'En 1 día',
                            default => "En {$days} días",
                        };
                    }),
            ])
            ->paginated(false);
    }
}
