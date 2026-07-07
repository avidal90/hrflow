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

    protected static ?string $modelLabel = 'fichaje';

    protected static ?string $pluralModelLabel = 'fichajes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|\UnitEnum|null $navigationGroup = 'Control de tiempo';

    protected static ?int $navigationSort = 2;

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

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->can('viewAny', TimeEntry::class);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['tenant', 'user', 'user.department']);
        $user = Auth::user();

        if (! $user instanceof User) {
            return $query->whereRaw('1 = 0');
        }

        $model = $query->getModel();

        if (! $model instanceof TimeEntry) {
            return $query->whereRaw('1 = 0');
        }

        return $model->scopeVisibleToUser($query, $user);
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
