<?php

namespace App\Filament\Resources\LeaveRequests\Schemas;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Models\Tenant;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                Section::make('Solicitud')
                    ->collapsible()
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Empresa')
                            ->options(fn (): array => Tenant::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->default(fn (): mixed => Auth::user()?->tenant_id)
                            ->disabled(fn (): bool => ! self::currentUserIsSuperAdmin())
                            ->dehydrated()
                            ->live()
                            ->required(),
                        Select::make('user_id')
                            ->label('Empleado')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, Get $get): void {
                                    $tenantId = $get('tenant_id');

                                    if (filled($tenantId)) {
                                        $query->where('tenant_id', $tenantId);
                                    }

                                    $actingUser = Auth::user();

                                    if ($actingUser instanceof User && $actingUser->isDepartmentManager()) {
                                        $query->where('department_id', $actingUser->department_id);
                                    }

                                    $query->orderBy('name');
                                },
                            )
                            ->getOptionLabelFromRecordUsing(fn (Model $record): string => $record instanceof User
                                ? sprintf('%s (%s)', $record->name, $record->employee_code ?? $record->email)
                                : (string) $record->getKey())
                            ->searchable(['name', 'email', 'employee_code'])
                            ->preload()
                            ->disabled(fn (): bool => ! self::canEditCoreFields())
                            ->required(),
                        Select::make('request_type')
                            ->label('Tipo de solicitud')
                            ->options(LeaveRequestType::options())
                            ->disabled(fn (): bool => ! self::canEditCoreFields())
                            ->required()
                            ->default(LeaveRequestType::Vacation->value),
                        DatePicker::make('start_date')
                            ->label('Fecha inicio')
                            ->disabled(fn (): bool => ! self::canEditCoreFields())
                            ->required()
                            ->native(false),
                        DatePicker::make('end_date')
                            ->label('Fecha fin')
                            ->disabled(fn (): bool => ! self::canEditCoreFields())
                            ->required()
                            ->native(false)
                            ->afterOrEqual('start_date'),
                        Textarea::make('reason')
                            ->label('Motivo')
                            ->disabled(fn (): bool => ! self::canEditCoreFields())
                            ->nullable()
                            ->rows(3)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Resolución')
                    ->collapsible()
                    ->schema([
                        Select::make('status')
                            ->label('Estado')
                            ->options(LeaveRequestStatus::options())
                            ->required()
                            ->default(LeaveRequestStatus::Pending->value)
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                if ($state === LeaveRequestStatus::Pending->value) {
                                    $set('resolved_by_user_id', null);
                                    $set('resolved_at', null);
                                } else {
                                    $actingUser = Auth::user();
                                    $set('resolved_by_user_id', $actingUser instanceof User ? $actingUser->getKey() : null);
                                    $set('resolved_at', now()->format('Y-m-d H:i'));
                                }
                            }),
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
                            ->disabled(fn (): bool => ! self::canEditAdminResolutionFields())
                            ->dehydrated()
                            ->nullable(),
                        DateTimePicker::make('resolved_at')
                            ->label('Fecha resolución')
                            ->disabled(fn (): bool => ! self::canEditAdminResolutionFields())
                            ->dehydrated()
                            ->seconds(false)
                            ->nullable(),
                        Textarea::make('manager_comment')
                            ->label('Comentario del responsable')
                            ->rows(3)
                            ->maxLength(2000)
                            ->columnSpanFull(),
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

    private static function canEditCoreFields(): bool
    {
        $user = Auth::user();

        return $user instanceof User && ($user->isSuperAdmin() || $user->isCompanyAdmin());
    }

    private static function canEditAdminResolutionFields(): bool
    {
        $user = Auth::user();

        return $user instanceof User && ($user->isSuperAdmin() || $user->isCompanyAdmin());
    }
}
