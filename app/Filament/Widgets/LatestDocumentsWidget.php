<?php

namespace App\Filament\Widgets;

use App\Enums\DocumentFolder;
use App\Models\Document;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;

class LatestDocumentsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Últimos documentos subidos';

    public function table(Table $table): Table
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        return $table
            ->query(
                Document::query()
                    ->with(['user', 'uploadedBy'])
                    ->where('tenant_id', $currentUser->tenant_id)
                    ->latest('uploaded_at')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Empleado')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Documento')
                    ->searchable(),
                TextColumn::make('folder')
                    ->label('Carpeta')
                    ->badge()
                    ->formatStateUsing(fn (DocumentFolder $state): string => $state->label()),
                TextColumn::make('uploadedBy.name')
                    ->label('Subido por')
                    ->placeholder('-'),
                TextColumn::make('uploaded_at')
                    ->label('Fecha')
                    ->since()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
