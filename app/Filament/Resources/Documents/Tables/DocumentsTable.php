<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Enums\DocumentFolder;
use App\Models\Department;
use App\Models\Document;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

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
                TextColumn::make('user.employee_code')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('folder')
                    ->label('Carpeta')
                    ->formatStateUsing(fn (DocumentFolder|string $state): string => ($state instanceof DocumentFolder ? $state : DocumentFolder::from($state))->label())
                    ->badge(),
                TextColumn::make('name')
                    ->label('Nombre documento')
                    ->searchable(),
                TextColumn::make('file_size')
                    ->label('Tamano')
                    ->formatStateUsing(fn (?int $state): string => $state !== null ? Number::fileSize($state) : '-')
                    ->sortable(),
                IconColumn::make('is_visible_to_employee')
                    ->label('Visible')
                    ->boolean(),
                TextColumn::make('uploadedBy.name')
                    ->label('Subido por')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('uploaded_at')
                    ->label('Subido')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tenant_id')
                    ->label('Empresa')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (): bool => self::currentUserIsSuperAdmin()),
                SelectFilter::make('folder')
                    ->label('Carpeta')
                    ->options(DocumentFolder::options()),
                SelectFilter::make('user_id')
                    ->label('Empleado')
                    ->relationship('user', 'name', fn (Builder $query): Builder => self::scopeVisibleUsers($query))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('department_id')
                    ->label('Departamento')
                    ->options(fn (): array => self::departmentOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        $departmentId = $data['value'] ?? null;

                        if (blank($departmentId)) {
                            return $query;
                        }

                        return $query->whereHas('user', function (Builder $userQuery) use ($departmentId): void {
                            $userQuery->where('department_id', $departmentId);
                        });
                    }),
                TernaryFilter::make('is_visible_to_employee')
                    ->label('Visible para empleado'),
                TrashedFilter::make(),
            ])
            ->defaultSort('uploaded_at', 'desc')
            ->recordActions([
                Action::make('download')
                    ->label('Descargar')
                    ->authorize(fn (Document $record): bool => (bool) auth()->user()?->can('download', $record))
                    ->action(fn (Document $record) => response()->download(
                        Storage::disk($record->disk ?: Document::STORAGE_DISK)->path($record->file_path),
                        $record->original_filename ?: basename($record->file_path),
                    )),
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
        return self::currentUser()?->isSuperAdmin() ?? false;
    }

    private static function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    private static function scopeVisibleUsers(Builder $query): Builder
    {
        $user = self::currentUser();

        if (! $user instanceof User) {
            return $query->whereRaw('1 = 0');
        }

        $model = $query->getModel();

        if (! $model instanceof User) {
            return $query->whereRaw('1 = 0');
        }

        return $model->scopeVisibleTo($query, $user);
    }

    /**
     * @return array<int|string, string>
     */
    private static function departmentOptions(): array
    {
        $user = self::currentUser();

        if (! $user instanceof User) {
            return [];
        }

        return Department::query()
            ->visibleTo($user)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
