<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Models\Activity;
use App\Models\Tenant;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label('Usuario')
                    ->placeholder('Sistema')
                    ->searchable(),
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->placeholder('-')
                    ->visible(fn (): bool => self::currentUserIsSuperAdmin()),
                TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'created' => 'Creación',
                        'updated' => 'Modificación',
                        'deleted' => 'Eliminación',
                        'restored' => 'Restauración',
                        default => ucfirst($state ?? '-'),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        'restored' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('subject_type')
                    ->label('Modelo')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-'),
                TextColumn::make('subject_id')
                    ->label('Registro')
                    ->formatStateUsing(fn (Activity $record): string => $record->subjectLabel()),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(60)
                    ->tooltip(fn (Activity $record): string => $record->description),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tenant_id')
                    ->label('Empresa')
                    ->options(fn (): array => Tenant::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->visible(fn (): bool => self::currentUserIsSuperAdmin()),
                SelectFilter::make('causer_id')
                    ->label('Usuario')
                    ->options(fn (): array => self::userOptions())
                    ->searchable(),
                SelectFilter::make('event')
                    ->label('Evento')
                    ->options([
                        'created' => 'Creación',
                        'updated' => 'Modificación',
                        'deleted' => 'Eliminación',
                        'restored' => 'Restauración',
                    ]),
                SelectFilter::make('subject_type')
                    ->label('Modelo')
                    ->options(fn (): array => self::subjectTypeOptions()),
                Filter::make('date_range')
                    ->label('Rango de fechas')
                    ->form([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    private static function currentUserIsSuperAdmin(): bool
    {
        return self::currentUser()?->isSuperAdmin() ?? false;
    }

    private static function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    /**
     * @return array<int|string, string>
     */
    private static function userOptions(): array
    {
        $user = self::currentUser();

        if (! $user instanceof User) {
            return [];
        }

        $query = User::query()->orderBy('name');

        if (! $user->isSuperAdmin()) {
            $query->where('tenant_id', $user->tenant_id);
        }

        return $query->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    private static function subjectTypeOptions(): array
    {
        return [
            'App\\Models\\User' => 'Usuario',
            'App\\Models\\Department' => 'Departamento',
            'App\\Models\\LeaveRequest' => 'Solicitud de ausencia',
            'App\\Models\\TimeEntry' => 'Fichaje',
            'App\\Models\\Document' => 'Documento',
            'App\\Models\\Turno' => 'Turno',
        ];
    }
}
