<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOwnPasswordRequest;
use App\Http\Requests\UpdateProfileAvatarRequest;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        Gate::authorize('viewOwnProfile', $user);

        return view('profile.show', [
            'initials' => Str::of($user->name)
                ->explode(' ')
                ->filter()
                ->take(2)
                ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
                ->implode(''),
            'user' => $user->load(['department', 'tenant']),
        ]);
    }

    public function updateAvatar(UpdateProfileAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        Gate::authorize('updateOwnProfile', $user);

        $path = $request->file('avatar')->store(
            'avatars/'.$user->tenant_id.'/'.$user->getKey(),
            'public',
        );

        if (filled($user->avatar_path) && $user->avatar_path !== $path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->update([
            'avatar_path' => $path,
        ]);

        return back()->with('status', 'avatar-updated');
    }

    public function updatePassword(UpdateOwnPasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        Gate::authorize('updateOwnPassword', $user);

        $user->update([
            'password' => $request->validated()['password'],
        ]);

        Notification::make()
            ->title('Contrasena actualizada')
            ->body('Tu contrasena se ha actualizado correctamente.')
            ->success()
            ->sendToDatabase($user);

        return back()->with('status', 'password-updated');
    }
}
