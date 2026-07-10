<?php

namespace App\Models;

use App\Enums\DocumentFolder;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsTenantActivity;
use App\Policies\DocumentPolicy;
use Database\Factories\DocumentFactory;
use Filament\Actions\Action as FilamentAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id',
    'user_id',
    'uploaded_by_user_id',
    'folder',
    'name',
    'description',
    'disk',
    'file_path',
    'original_filename',
    'mime_type',
    'file_size',
    'uploaded_at',
    'is_visible_to_employee',
])]
#[UsePolicy(DocumentPolicy::class)]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use BelongsToTenant, HasFactory, LogsTenantActivity, SoftDeletes;

    public const STORAGE_DISK = 'documents';

    protected static function booted(): void
    {
        static::created(function (self $document): void {
            if (! $document->is_visible_to_employee || blank($document->tenant_id) || blank($document->user_id)) {
                return;
            }

            if ($document->uploaded_by_user_id !== null && (string) $document->uploaded_by_user_id === (string) $document->user_id) {
                return;
            }

            $employee = $document->user;

            if (! $employee instanceof User) {
                return;
            }

            $folderAttribute = $document->getAttribute('folder');
            $folder = $folderAttribute instanceof DocumentFolder
                ? $folderAttribute
                : (DocumentFolder::tryFrom((string) $folderAttribute) ?? DocumentFolder::Other);
            $folderLabel = $folder->label();

            $portalUrl = route('portal.documents.index', [
                'tenant' => $document->tenant_id,
                'carpeta' => $folder->value,
            ]);

            Notification::make()
                ->title('Nuevo documento disponible')
                ->body("Se ha subido el documento {$document->name} en la carpeta {$folderLabel}.")
                ->success()
                ->actions([
                    FilamentAction::make('view')
                        ->label('Ver documentación')
                        ->url($portalUrl),
                ])
                ->sendToDatabase($employee);
        });

        static::forceDeleted(function (self $document): void {
            self::deleteStoredFile($document->disk, $document->file_path);
        });
    }

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'is_visible_to_employee' => 'boolean',
            'folder' => DocumentFolder::class,
        ];
    }

    public static function buildStorageDirectory(string $tenantId, int|string $userId, string $folder): string
    {
        return 'tenant/'.$tenantId.'/'.$folder.'/user/'.$userId;
    }

    public static function buildStoragePath(string $tenantId, int|string $userId, string $folder, string $originalFilename): string
    {
        $extension = Str::lower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        $filename = now()->format('Ymd_His').'_'.Str::lower(Str::random(8));

        if ($extension !== '') {
            $filename .= '.'.$extension;
        }

        return self::buildStorageDirectory($tenantId, $userId, $folder).'/'.$filename;
    }

    public static function deleteStoredFile(?string $disk, ?string $path): void
    {
        if (blank($path)) {
            return;
        }

        $storageDisk = $disk ?: self::STORAGE_DISK;

        if (Storage::disk($storageDisk)->exists($path)) {
            Storage::disk($storageDisk)->delete($path);
        }
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        $query->visibleTo($user);

        if ($user->isCompanyAdmin() || $user->isHr()) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }
}
