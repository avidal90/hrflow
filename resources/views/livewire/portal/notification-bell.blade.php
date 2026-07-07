<div class="relative" x-data @click.outside="$wire.close()">
    <button
        wire:click="toggle"
        class="relative flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
        aria-label="Notificaciones"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        @if ($this->unreadCount > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[10px] font-bold leading-none text-white">
                {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    @if ($open)
        <div class="absolute right-0 top-full z-50 mt-2 w-80 rounded-2xl bg-white shadow-lg ring-1 ring-slate-200">
            <div class="border-b border-slate-100 px-4 py-3">
                <p class="text-sm font-semibold text-slate-900">Notificaciones</p>
            </div>

            <ul class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                @forelse ($this->notifications as $notification)
                    @php
                        $data = $notification->data;
                        $title = $data['title'] ?? null;
                        $body  = $data['body'] ?? null;
                        $url   = $data['actions'][0]['url'] ?? null;
                        $isUnread = $notification->read_at === null;
                        $isSuccess = ($data['status'] ?? null) === 'success';
                        $isDanger  = ($data['status'] ?? null) === 'danger';
                    @endphp
                    <li>
                        @if ($url)
                            <a href="{{ $url }}" class="flex items-start gap-3 px-4 py-3 transition hover:bg-slate-50">
                        @else
                            <div class="flex items-start gap-3 px-4 py-3">
                        @endif

                            <span @class([
                                'mt-1 flex h-2 w-2 shrink-0 rounded-full',
                                'bg-amber-500' => $isUnread,
                                'bg-transparent' => ! $isUnread,
                            ])></span>

                            <div class="min-w-0 flex-1">
                                @if ($title)
                                    <p @class([
                                        'text-xs font-semibold leading-snug',
                                        'text-emerald-700' => $isSuccess,
                                        'text-red-700'     => $isDanger,
                                        'text-slate-900'   => ! $isSuccess && ! $isDanger,
                                    ])>{{ $title }}</p>
                                @endif
                                @if ($body)
                                    <p class="mt-0.5 text-xs leading-snug text-slate-500">{{ $body }}</p>
                                @endif
                                <p class="mt-1 text-[10px] text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>

                        @if ($url)
                            </a>
                        @else
                            </div>
                        @endif
                    </li>
                @empty
                    <li class="px-4 py-8 text-center">
                        <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        <p class="mt-2 text-xs text-slate-400">Sin notificaciones</p>
                    </li>
                @endforelse
            </ul>
        </div>
    @endif
</div>
