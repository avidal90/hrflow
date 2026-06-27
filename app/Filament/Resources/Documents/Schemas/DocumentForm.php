<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\DocumentCategory;
use App\Models\Employee;
use App\Models\Tenant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tenant_id')
                    ->label('Empresa')
                    ->options(fn (): array => Tenant::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->default(fn (): mixed => Auth::user()?->tenant_id)
                    ->disabled(fn (): bool => ! self::currentUserIsSuperAdmin())
                    ->dehydrated()
                    ->live()
                    ->required(),
                Select::make('employee_id')
                    ->label('Empleado')
                    ->relationship(
                        name: 'employee',
                        titleAttribute: 'employee_code',
                        modifyQueryUsing: function (Builder $query, Get $get): void {
                            $tenantId = $get('tenant_id');

                            if (filled($tenantId)) {
                                $query->where('tenant_id', $tenantId);
                            }

                            $query->orderBy('first_name')->orderBy('last_name');
                        },
                    )
                    ->getOptionLabelFromRecordUsing(fn (Model $record): string => $record instanceof Employee
                        ? sprintf('%s %s (%s)', $record->first_name, $record->last_name, $record->employee_code)
                        : (string) $record->getKey())
                    ->searchable(['first_name', 'last_name', 'employee_code'])
                    ->preload()
                    ->required(),
                Select::make('category')
                    ->label('Categoria')
                    ->options(DocumentCategory::options())
                    ->required(),
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('file_path')
                    ->label('Ruta de archivo')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),
                TextInput::make('mime_type')
                    ->label('Tipo MIME')
                    ->required()
                    ->maxLength(255),
                TextInput::make('file_size')
                    ->label('Tamano (bytes)')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                DateTimePicker::make('uploaded_at')
                    ->label('Fecha de subida')
                    ->required()
                    ->seconds(false)
                    ->default(now()),
                Toggle::make('is_visible_to_employee')
                    ->label('Visible para empleado')
                    ->default(false),
                Textarea::make('description')
                    ->label('Descripcion')
                    ->maxLength(2000)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    private static function currentUserIsSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user instanceof \App\Models\User && $user->isSuperAdmin();
    }
}
