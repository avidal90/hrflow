<?php

namespace App\Filament\Resources\Documents;

use App\Filament\Resources\Documents\Pages\CreateDocument;
use App\Filament\Resources\Documents\Pages\EditDocument;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Filament\Resources\Documents\Pages\ViewDocument;
use App\Filament\Resources\Documents\Schemas\DocumentForm;
use App\Filament\Resources\Documents\Schemas\DocumentInfolist;
use App\Filament\Resources\Documents\Tables\DocumentsTable;
use App\Models\Document;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $modelLabel = 'documento';

    protected static ?string $pluralModelLabel = 'documentos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Gestión de empresa';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Documentos';

    public static function form(Schema $schema): Schema
    {
        return DocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentsTable::configure($table);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->can('viewAny', Document::class);
    }

    /**
     * @return Builder<Model>
     */
    public static function getEloquentQuery(): Builder
    {
        /** @var Builder<Document> $query */
        $query = parent::getEloquentQuery()->with(['tenant', 'user', 'uploadedBy']);
        $user = Auth::user();

        if (! $user instanceof User) {
            /** @var Builder<Model> $emptyQuery */
            $emptyQuery = $query->whereRaw('1 = 0');

            return $emptyQuery;
        }

        $model = $query->getModel();

        if (! $model instanceof Document) {
            /** @var Builder<Model> $emptyQuery */
            $emptyQuery = $query->whereRaw('1 = 0');

            return $emptyQuery;
        }

        /** @var Builder<Model> $visibleQuery */
        $visibleQuery = $model->scopeVisibleToUser($query, $user);

        return $visibleQuery;
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
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'view' => ViewDocument::route('/{record}'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
