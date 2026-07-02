<?php

namespace App\Filament\Resources\LeaveRequests\Tables;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Models\Department;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LeaveRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->visible(fn (): bool => self::currentUserIsSuperAdmin())
                    ->searchable(),
                TextColumn::make('user.employee_code')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('request_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (LeaveRequestType|string $state): string => ($state instanceof LeaveRequestType ? $state : LeaveRequestType::from($state))->label())
                    ->badge(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (LeaveRequestStatus|string $state): string => ($state instanceof LeaveRequestStatus ? $state : LeaveRequestStatus::from($state))->label())
                    ->badge(),
                TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date()
                    ->sortable(),
                TextColumn::make('resolvedBy.name')
                    ->label('Resuelto por')
                    ->placeholder('-')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('tenant_id')
                    ->label('Empresa')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (): bool => self::currentUserIsSuperAdmin()),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(LeaveRequestStatus::options()),
                SelectFilter::make('request_type')
                    ->label('Tipo')
                    ->options(LeaveRequestType::options()),
                SelectFilter::make('department_id')
                    ->label('Departamento')
                    ->options(fn (): array => self::departmentOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        $departmentId = $data['value'] ?? null;

                        if (blank($departmentId)) {
                            return $query;
                        }

                        return $query->whereHas('user', function (Builder $userQuery) use ($departmentId): void {
                            $userQuery->where('department_id', $departmentId);
                        });
                    }),
                TrashedFilter::make(),
            ])
            ->defaultSort('start_date', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
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
    private static function departmentOptions(): array
    {
        $user = self::currentUser();

        if (! $user instanceof User) {
            return [];
        }

        return Department::query()
            ->visibleTo($user)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
