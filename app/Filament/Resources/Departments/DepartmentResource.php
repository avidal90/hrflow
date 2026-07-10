<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Pages\CreateDepartment;
use App\Filament\Resources\Departments\Pages\EditDepartment;
use App\Filament\Resources\Departments\Pages\ListDepartments;
use App\Filament\Resources\Departments\Pages\ViewDepartment;
use App\Filament\Resources\Departments\RelationManagers\UsersRelationManager;
use App\Filament\Resources\Departments\Schemas\DepartmentForm;
use App\Filament\Resources\Departments\Schemas\DepartmentInfolist;
use App\Filament\Resources\Departments\Tables\DepartmentsTable;
use App\Models\Department;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $modelLabel = 'departamento';

    protected static ?string $pluralModelLabel = 'departamentos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|\UnitEnum|null $navigationGroup = 'Gestión de empresa';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Departamentos';

    public static function form(Schema $schema): Schema
    {
        return DepartmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DepartmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartmentsTable::configure($table);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->can('viewAny', Department::class);
    }

    /**
     * @return Builder<Model>
     */
    public static function getEloquentQuery(): Builder
    {
        /** @var Builder<Department> $query */
        $query = parent::getEloquentQuery()->with(['tenant', 'manager']);
        $user = Auth::user();

        if (! $user instanceof User) {
            /** @var Builder<Model> $emptyQuery */
            $emptyQuery = $query->whereRaw('1 = 0');

            return $emptyQuery;
        }

        $model = $query->getModel();

        if (! $model instanceof Department) {
            /** @var Builder<Model> $emptyQuery */
            $emptyQuery = $query->whereRaw('1 = 0');

            return $emptyQuery;
        }

        /** @var Builder<Model> $visibleQuery */
        $visibleQuery = $model->scopeVisibleTo($query, $user);

        return $visibleQuery;
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDepartments::route('/'),
            'create' => CreateDepartment::route('/create'),
            'view' => ViewDepartment::route('/{record}'),
            'edit' => EditDepartment::route('/{record}/edit'),
        ];
    }
}
