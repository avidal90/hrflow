<?php

namespace App\Livewire\Portal;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    #[Computed]
    public function unreadCount(): int
    {
        return auth()->user()?->unreadNotifications()->count() ?? 0;
    }

    #[Computed]
    public function notifications(): Collection
    {
        $user = auth()->user();

        if ($user === null) {
            return new Collection();
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
            auth()->user()?->unreadNotifications()->update(['read_at' => now()]);
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
