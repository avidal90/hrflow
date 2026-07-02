<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Portal HRFlow' }}</title>
        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css'])
        @endif
        @livewireStyles
    </head>
    @php($activePortalUser = $portalUser ?? auth()->user()?->loadMissing('roles'))
    @php($tenantRouteParameters = ['tenant' => tenant()->getTenantKey()])
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white">
            <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">

                <a href="{{ route('portal.dashboard', $tenantRouteParameters) }}" class="flex shrink-0 items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500 text-sm font-bold text-white">HR</span>
                    <div class="hidden sm:block">
                        <p class="text-xs font-medium leading-none text-slate-400">Portal</p>
                        <p class="mt-0.5 text-sm font-semibold leading-none text-slate-900">{{ tenant()?->name ?? 'HRFlow' }}</p>
                    </div>
                </a>

                <nav class="flex flex-1 items-center gap-0.5 overflow-x-auto">
                    <a href="{{ route('portal.dashboard', $tenantRouteParameters) }}" @class([
                        'rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition',
                        'bg-slate-100 text-slate-900' => request()->routeIs('portal.dashboard'),
                        'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! request()->routeIs('portal.dashboard'),
                    ])>Dashboard</a>
                    <a href="{{ route('portal.calendar.index', $tenantRouteParameters) }}" @class([
                        'rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition',
                        'bg-slate-100 text-slate-900' => request()->routeIs('portal.calendar.*'),
                        'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! request()->routeIs('portal.calendar.*'),
                    ])>Calendario</a>
                    <a href="{{ route('portal.time-tracking.index', $tenantRouteParameters) }}" @class([
                        'rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition',
                        'bg-slate-100 text-slate-900' => request()->routeIs('portal.time-tracking.*'),
                        'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! request()->routeIs('portal.time-tracking.*'),
                    ])>Horario</a>
                    <a href="{{ route('portal.requests.index', $tenantRouteParameters) }}" @class([
                        'rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition',
                        'bg-slate-100 text-slate-900' => request()->routeIs('portal.requests.*'),
                        'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! request()->routeIs('portal.requests.*'),
                    ])>Solicitudes</a>
                    <a href="{{ route('portal.documents.index', $tenantRouteParameters) }}" @class([
                        'rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition',
                        'bg-slate-100 text-slate-900' => request()->routeIs('portal.documents.*'),
                        'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! request()->routeIs('portal.documents.*'),
                    ])>Documentos</a>
                </nav>

                <div class="flex shrink-0 items-center gap-3">
                    @if ($activePortalUser?->canAccessAdministration())
                        <a href="/admin" class="hidden rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 sm:inline-flex">
                            Zona administrativa
                        </a>
                    @endif

                    @if ($activePortalUser?->getFilamentAvatarUrl())
                        <img src="{{ $activePortalUser->getFilamentAvatarUrl() }}" alt="{{ $activePortalUser?->name }}" class="h-8 w-8 rounded-full object-cover ring-2 ring-slate-200">
                    @else
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-600">
                            {{ str($activePortalUser?->name ?? 'U')->explode(' ')->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->implode('') }}
                        </div>
                    @endif

                    <div class="hidden sm:block">
                        <p class="text-sm font-medium leading-none text-slate-900">{{ $activePortalUser?->name }}</p>
                        <p class="mt-0.5 text-xs leading-none text-slate-500">{{ $activePortalUser?->primaryRoleLabel() }}</p>
                    </div>

                    <form method="POST" action="{{ route('logout', $tenantRouteParameters) }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                            Salir
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        @livewireScripts
        @stack('scripts')
    </body>
</html>