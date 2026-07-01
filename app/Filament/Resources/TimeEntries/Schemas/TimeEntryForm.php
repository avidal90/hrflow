<?php

namespace App\Filament\Resources\TimeEntries\Schemas;

use App\Enums\TimeEntryStatus;
use App\Models\Tenant;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TimeEntryForm
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
                Select::make('user_id')
                    ->label('Usuario')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, Get $get): void {
                            $tenantId = $get('tenant_id');

                            if (filled($tenantId)) {
                                $query->where('tenant_id', $tenantId);
                            }

                            $query->orderBy('name');
                        },
                    )
                    ->getOptionLabelFromRecordUsing(fn (Model $record): string => $record instanceof User
                        ? sprintf('%s (%s)', $record->name, $record->employee_code ?? $record->email)
                        : (string) $record->getKey())
                    ->searchable(['name', 'email', 'employee_code'])
                    ->preload()
                    ->required(),
                DatePicker::make('work_date')
                    ->label('Fecha')
                    ->required()
                    ->native(false)
                    ->default(now()->toDateString()),
                TimePicker::make('check_in_time')
                    ->label('Hora entrada')
                    ->required()
                    ->seconds(false),
                TimePicker::make('check_out_time')
                    ->label('Hora salida')
                    ->seconds(false)
                    ->after('check_in_time')
                    ->nullable(),
                TextInput::make('duration_minutes')
                    ->label('Duracion (min)')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Se calcula automaticamente al guardar.'),
                Select::make('status')
                    ->label('Estado')
                    ->options(TimeEntryStatus::options())
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('notes')
                    ->label('Observaciones')
                    ->maxLength(2000)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    private static function currentUserIsSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isSuperAdmin();
    }
}
