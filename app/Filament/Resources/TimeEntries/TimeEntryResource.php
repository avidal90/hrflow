<?php

namespace App\Filament\Resources\TimeEntries;

use App\Filament\Resources\TimeEntries\Pages\CreateTimeEntry;
use App\Filament\Resources\TimeEntries\Pages\EditTimeEntry;
use App\Filament\Resources\TimeEntries\Pages\ListTimeEntries;
use App\Filament\Resources\TimeEntries\Pages\ViewTimeEntry;
use App\Filament\Resources\TimeEntries\Schemas\TimeEntryForm;
use App\Filament\Resources\TimeEntries\Schemas\TimeEntryInfolist;
use App\Filament\Resources\TimeEntries\Tables\TimeEntriesTable;
use App\Models\TimeEntry;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TimeEntryResource extends Resource
{
    protected static ?string $model = TimeEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Fichajes';

    public static function form(Schema $schema): Schema
    {
        return TimeEntryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TimeEntryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimeEntriesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['tenant', 'employee.user', 'employee.department']);
        $user = Auth::user();

        if (! $user instanceof User) {
            return $query->whereRaw('1 = 0');
        }

        return $query->visibleToUser($user);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimeEntries::route('/'),
            'create' => CreateTimeEntry::route('/create'),
            'view' => ViewTimeEntry::route('/{record}'),
            'edit' => EditTimeEntry::route('/{record}/edit'),
        ];
    }
}
