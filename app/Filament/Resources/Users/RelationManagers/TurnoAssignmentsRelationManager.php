<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TurnoAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'turnoAssignments';

    protected static ?string $title = 'Turnos asignados';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('turno_id')
                    ->label('Turno')
                    ->relationship(
                        name: 'turno',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query): void {
                            $query->where('tenant_id', $this->getOwnerRecord()->tenant_id)
                                ->orderBy('name');
                        },
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('valid_from')
                    ->label('Vigente desde')
                    ->helperText('Dejar vacio para que la asignacion no tenga inicio definido.'),
                DatePicker::make('valid_until')
                    ->label('Vigente hasta')
                    ->helperText('Dejar vacio para que la asignacion sea permanente.'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('turno'))
            ->columns([
                TextColumn::make('turno.name')
                    ->label('Turno')
                    ->searchable(),
                TextColumn::make('valid_from')
                    ->label('Vigente desde')
                    ->date()
                    ->placeholder('-'),
                TextColumn::make('valid_until')
                    ->label('Vigente hasta')
                    ->date()
                    ->placeholder('Permanente'),
            ])
            ->defaultSort('valid_from', 'desc')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
