<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $selectedRoleName = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedRoleName = $this->extractSelectedRoleName($data);

        unset($data['role_name']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $roleName = $this->selectedRoleName ?? 'employee';

        $this->record->syncRoles([$roleName]);
    }

    private function extractSelectedRoleName(array $data): string
    {
        if (! User::canManageRoleAssignments(Auth::user())) {
            throw ValidationException::withMessages([
                'role_name' => 'No puedes asignar roles desde esta pantalla.',
            ]);
        }

        $roleName = $data['role_name'] ?? 'employee';
        $availableRoles = User::assignableRoleOptionsFor(Auth::user());

        if (array_key_exists($roleName, $availableRoles)) {
            return $roleName;
        }

        throw ValidationException::withMessages([
            'role_name' => 'No puedes asignar ese rol.',
        ]);
    }
}
