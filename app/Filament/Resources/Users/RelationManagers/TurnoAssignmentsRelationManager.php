<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TurnoAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'turnoAssignments';

    protected static ?string $title = 'Turnos asignados';

    public function isReadOnly(): bool
    {
        if (is_subclass_of($this->getPageClass(), ViewRecord::class)) {
            return false;
        }

        return parent::isReadOnly();
    }

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
                            $query->where('tenant_id', $this->ownerUser()->tenant_id)
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
                    ->afterOrEqual('valid_from')
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
            ->filters([
                SelectFilter::make('turno_id')
                    ->label('Turno')
                    ->relationship(
                        name: 'turno',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query): void {
                            $query->where('tenant_id', $this->ownerUser()->tenant_id)
                                ->orderBy('name');
                        },
                    )
                    ->searchable()
                    ->preload(),
                Filter::make('active_now')
                    ->label('Solo vigentes hoy')
                    ->query(fn (Builder $query): Builder => $query
                        ->where(function (Builder $rangeQuery): void {
                            $rangeQuery->whereNull('valid_from')
                                ->orWhereDate('valid_from', '<=', today());
                        })
                        ->where(function (Builder $rangeQuery): void {
                            $rangeQuery->whereNull('valid_until')
                                ->orWhereDate('valid_until', '>=', today());
                        })),
            ])
            ->defaultSort('valid_from', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Crear asignacion de turno')
                    ->mutateFormDataUsing(fn (array $data): array => $this->mutateFormData($data)),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateFormDataUsing(fn (array $data): array => $this->mutateFormData($data)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mutateFormData(array $data): array
    {
        $ownerUser = $this->ownerUser();

        $data['user_id'] = $ownerUser->getKey();
        $data['tenant_id'] = $ownerUser->tenant_id;

        return $data;
    }

    private function ownerUser(): User
    {
        $record = $this->getOwnerRecord();
        if (! $record instanceof User) {
            throw new \RuntimeException('Expected User as owner record.');
        }

        return $record;
    }
}
