<?php

namespace App\Filament\Resources\LeaveRequests\Schemas;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Models\Employee;
use App\Models\Tenant;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LeaveRequestForm
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
                Select::make('employee_id')
                    ->label('Empleado')
                    ->relationship(
                        name: 'employee',
                        titleAttribute: 'employee_code',
                        modifyQueryUsing: function (Builder $query, Get $get): void {
                            $tenantId = $get('tenant_id');

                            if (filled($tenantId)) {
                                $query->where('tenant_id', $tenantId);
                            }

                            $query->orderBy('first_name')->orderBy('last_name');
                        },
                    )
                    ->getOptionLabelFromRecordUsing(fn (Model $record): string => $record instanceof Employee
                        ? sprintf('%s %s (%s)', $record->first_name, $record->last_name, $record->employee_code)
                        : (string) $record->getKey())
                    ->searchable(['first_name', 'last_name', 'employee_code'])
                    ->preload()
                    ->required(),
                Select::make('request_type')
                    ->label('Tipo de solicitud')
                    ->options(LeaveRequestType::options())
                    ->required()
                    ->default(LeaveRequestType::Vacation->value),
                Select::make('status')
                    ->label('Estado')
                    ->options(LeaveRequestStatus::options())
                    ->required()
                    ->default(LeaveRequestStatus::Pending->value)
                    ->live(),
                DatePicker::make('start_date')
                    ->label('Fecha inicio')
                    ->required()
                    ->native(false),
                DatePicker::make('end_date')
                    ->label('Fecha fin')
                    ->required()
                    ->native(false)
                    ->afterOrEqual('start_date'),
                Textarea::make('reason')
                    ->label('Motivo')
                    ->required()
                    ->rows(3)
                    ->maxLength(2000)
                    ->columnSpanFull(),
                Select::make('resolved_by_user_id')
                    ->label('Responsable que resuelve')
                    ->relationship(
                        name: 'resolvedBy',
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
                        ? sprintf('%s (%s)', $record->name, $record->email)
                        : (string) $record->getKey())
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->nullable(),
                DateTimePicker::make('resolved_at')
                    ->label('Fecha resolucion')
                    ->seconds(false)
                    ->nullable(),
                Textarea::make('manager_comment')
                    ->label('Comentario del responsable')
                    ->rows(3)
                    ->maxLength(2000)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    private static function currentUserIsSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user instanceof \App\Models\User && $user->isSuperAdmin();
    }
}
