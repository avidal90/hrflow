<?php

namespace App\Livewire\Portal;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property Collection<int, DatabaseNotification> $notifications
 */
class NotificationBell extends Component
{
    public bool $open = false;

    #[Computed]
    public function unreadCount(): int
    {
        return auth()->user()?->unreadNotifications()->count() ?? 0;
    }

    /** @return Collection<int, DatabaseNotification> */
    #[Computed]
    public function notifications(): Collection
    {
        $user = auth()->user();

        if ($user === null) {
            return new Collection;
        }

        return $user->notifications()
            ->latest()
            ->limit(10)
            ->get();
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;

        if ($this->open) {
            $visibleIds = $this->notifications->pluck('id');
            auth()->user()?->unreadNotifications()->whereIn('id', $visibleIds)->update(['read_at' => now()]);
            unset($this->unreadCount);
        }
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function render(): View
    {
        return view('livewire.portal.notification-bell');
    }
}
