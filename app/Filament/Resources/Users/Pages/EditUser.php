<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Support\Validation\PasswordRules;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $selectedRoleName = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetPassword')
                ->label('Restablecer contrasena')
                ->icon(Heroicon::OutlinedKey)
                ->authorize(function (): bool {
                    $record = $this->getRecord();
                    /** @var User|null $currentUser */
                    $currentUser = Auth::user();

                    return $record instanceof User
                        && ($currentUser?->can('resetPassword', $record) ?? false);
                })
                ->schema([
                    TextInput::make('password')
                        ->label('Nueva contrasena')
                        ->password()
                        ->revealable()
                        ->required()
                        ->rule(PasswordRules::user()),
                    TextInput::make('password_confirmation')
                        ->label('Confirmar contrasena')
                        ->password()
                        ->revealable()
                        ->required()
                        ->same('password')
                        ->dehydrated(false),
                ])
                ->action(function (array $data): void {
                    /** @var User $record */
                    $record = $this->getRecord();

                    $record->update([
                        'password' => $data['password'],
                    ]);

                    Notification::make()
                        ->title('Contrasena restablecida')
                        ->body('Un administrador ha actualizado tu contrasena de acceso.')
                        ->success()
                        ->sendToDatabase($record);

                    Notification::make()
                        ->title('Contrasena restablecida')
                        ->body('La contrasena del usuario se ha actualizado correctamente.')
                        ->success()
                        ->send();
                }),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedRoleName = $this->extractSelectedRoleName($data);

        unset($data['role_name']);

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->selectedRoleName === null) {
            return;
        }

        $this->record->syncRoles([$this->selectedRoleName]);
    }

    private function extractSelectedRoleName(array $data): string
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $roleName = $data['role_name'] ?? $this->record->primaryRoleName() ?? 'employee';
        $currentRoleName = $this->record->primaryRoleName();

        if (! User::canManageRoleAssignments($currentUser, $this->record)) {
            if ($roleName === $currentRoleName) {
                return (string) $currentRoleName;
            }

            throw ValidationException::withMessages([
                'role_name' => 'No puedes cambiar el rol de este usuario.',
            ]);
        }

        $availableRoles = User::assignableRoleOptionsFor($currentUser, $this->record);

        if (array_key_exists($roleName, $availableRoles)) {
            return $roleName;
        }

        throw ValidationException::withMessages([
            'role_name' => 'No puedes asignar ese rol.',
        ]);
    }
}
