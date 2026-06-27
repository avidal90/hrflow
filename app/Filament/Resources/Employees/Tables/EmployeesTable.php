<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->visible(fn (): bool => self::currentUserIsSuperAdmin())
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable(),
                TextColumn::make('department.name')
                    ->label('Departamento')
                    ->searchable(),
                TextColumn::make('employee_code')
                    ->label('Codigo')
                    ->searchable(),
                TextColumn::make('first_name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label('Apellidos')
                    ->searchable(),
                TextColumn::make('hire_date')
                    ->label('Fecha de alta')
                    ->date()
                    ->sortable(),
                TextColumn::make('employment_status')
                    ->label('Estado laboral')
                    ->badge()
                    ->searchable(),
                TextColumn::make('job_title')
                    ->label('Puesto')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('last_name')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function currentUserIsSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user instanceof \App\Models\User && $user->isSuperAdmin();
    }
}
