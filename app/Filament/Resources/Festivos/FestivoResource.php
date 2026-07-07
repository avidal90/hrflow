<?php

namespace App\Filament\Resources\Festivos;

use App\Filament\Resources\Festivos\Pages\CreateFestivo;
use App\Filament\Resources\Festivos\Pages\EditFestivo;
use App\Filament\Resources\Festivos\Pages\ListFestivos;
use App\Filament\Resources\Festivos\Pages\ViewFestivo;
use App\Filament\Resources\Festivos\Schemas\FestivoForm;
use App\Filament\Resources\Festivos\Schemas\FestivoInfolist;
use App\Filament\Resources\Festivos\Tables\FestivosTable;
use App\Models\Festivo;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FestivoResource extends Resource
{
    protected static ?string $model = Festivo::class;

    protected static ?string $modelLabel = 'festivo';

    protected static ?string $pluralModelLabel = 'festivos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|\UnitEnum|null $navigationGroup = 'Turnos y festivos';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Festivos';

    public static function form(Schema $schema): Schema
    {
        return FestivoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FestivoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FestivosTable::configure($table);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->can('viewAny', Festivo::class);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('tenant');
        $user = Auth::user();

        if (! $user instanceof User) {
            return $query->whereRaw('1 = 0');
        }

        $model = $query->getModel();

        if (! $model instanceof Festivo) {
            return $query->whereRaw('1 = 0');
        }

        return $model->scopeVisibleTo($query, $user);
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
            'index' => ListFestivos::route('/'),
            'create' => CreateFestivo::route('/create'),
            'view' => ViewFestivo::route('/{record}'),
            'edit' => EditFestivo::route('/{record}/edit'),
        ];
    }
}
