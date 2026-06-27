<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Enums\DocumentCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->visible(fn (): bool => self::currentUserIsSuperAdmin())
                    ->searchable(),
                TextColumn::make('employee.employee_code')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.first_name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Categoria')
                    ->formatStateUsing(fn (DocumentCategory|string $state): string => ($state instanceof DocumentCategory ? $state : DocumentCategory::from($state))->label())
                    ->badge(),
                TextColumn::make('name')
                    ->label('Nombre documento')
                    ->searchable(),
                TextColumn::make('mime_type')
                    ->label('MIME')
                    ->searchable(),
                TextColumn::make('file_size')
                    ->label('Tamano')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_visible_to_employee')
                    ->label('Visible')
                    ->boolean(),
                TextColumn::make('uploaded_at')
                    ->label('Subido')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoria')
                    ->options(DocumentCategory::options()),
                TrashedFilter::make(),
            ])
            ->defaultSort('uploaded_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    private static function currentUserIsSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user instanceof \App\Models\User && $user->isSuperAdmin();
    }
}
