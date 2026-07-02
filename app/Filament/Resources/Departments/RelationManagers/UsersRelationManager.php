<?php

namespace App\Filament\Resources\Departments\RelationManagers;

use App\Models\Department;
use App\Models\User;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Miembros';

    public function isReadOnly(): bool
    {
        if (is_subclass_of($this->getPageClass(), ViewRecord::class)) {
            return false;
        }

        return parent::isReadOnly();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('roles'))
            ->columns([
                TextColumn::make('employee_code')
                    ->label('Código')
                    ->placeholder('-')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('job_title')
                    ->label('Puesto')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('role_label')
                    ->label('Rol')
                    ->state(fn (User $record): string => $record->primaryRoleLabel())
                    ->badge(),
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Añadir miembro')
                    ->recordSelectOptionsQuery(fn (Builder $query): Builder => $query
                        ->whereNull('department_id')
                        ->where('tenant_id', $this->getOwnerRecord()->tenant_id)
                        ->whereDoesntHave('roles', fn (Builder $q) => $q->where('name', 'super-admin'))
                    )
                    ->visible(fn (): bool => $this->canManageMembers()),
            ])
            ->recordActions([
                DissociateAction::make()
                    ->label('Quitar del departamento')
                    ->modalHeading('Quitar miembro del departamento')
                    ->modalDescription('El usuario dejará de pertenecer a este departamento. Esta acción no elimina al usuario.')
                    ->visible(fn (): bool => $this->canManageMembers()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make()
                        ->label('Quitar del departamento')
                        ->visible(fn (): bool => $this->canManageMembers()),
                ]),
            ])
            ->emptyStateHeading('Sin miembros')
            ->emptyStateDescription('Este departamento no tiene empleados asignados.');
    }

    private function canManageMembers(): bool
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        /** @var Department $department */
        $department = $this->getOwnerRecord();

        return $user->can('manageMembers', $department);
    }
}
