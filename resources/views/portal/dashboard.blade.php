@php($tenantRouteParameters = ['tenant' => tenant()->getTenantKey()])

<x-layouts.portal :portal-user="$portalUser" :title="'Dashboard | ' . (tenant()?->name ?? 'HRFlow')">

    {{-- Welcome row --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Hola, {{ str($portalUser->name)->explode(' ')->first() }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ now()->translatedFormat('l j \d\e F \d\e Y') }}
                @if ($portalUser->job_title)
                    &middot; {{ $portalUser->job_title }}
                @endif
                @if ($portalUser->department?->name)
                    &middot; {{ $portalUser->department->name }}
                @endif
            </p>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Desde aqui puedes fichar, consultar tu calendario y gestionar tus solicitudes personales dentro del portal de tu empresa.
            </p>
            @if ($portalUser->canAccessAdministration())
                <p class="mt-1 text-sm leading-6 text-slate-500">
                    Tu perfil tambien puede entrar en la zona administrativa. Encontraras el acceso directo en la cabecera.
                </p>
            @endif
        </div>
        <a href="{{ route('portal.time-tracking.index', $tenantRouteParameters) }}" class="inline-flex items-center gap-1.5 self-start rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-400 sm:self-auto">
            Control horario
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </a>
    </div>

    {{-- Profile + Today strip --}}
    <div class="mb-6 grid gap-4 lg:grid-cols-[1fr_auto]">
        <article class="rounded-2xl bg-slate-900 p-6 text-white">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Perfil laboral</p>
            <div class="mt-4 flex items-center gap-4">
                @if ($portalUser->getFilamentAvatarUrl())
                    <img src="{{ $portalUser->getFilamentAvatarUrl() }}" alt="{{ $portalUser->name }}" class="h-14 w-14 rounded-2xl object-cover ring-2 ring-white/20">
                @else
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-lg font-bold text-white">
                        {{ str($portalUser->name)->explode(' ')->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') }}
                    </div>
                @endif
                <div>
                    <p class="text-xl font-semibold text-white">{{ $portalUser->name }}</p>
                    <p class="mt-0.5 text-sm text-slate-400">{{ $portalUser->primaryRoleLabel() }}</p>
                </div>
            </div>
            <div class="mt-6 grid grid-cols-3 gap-3">
                <div class="rounded-xl bg-white/8 p-3">
                    <p class="text-xs font-medium text-slate-400">Empresa</p>
                    <p class="mt-1.5 text-sm font-semibold text-white">{{ $portalUser->tenant?->name ?: '-' }}</p>
                </div>
                <div class="rounded-xl bg-white/8 p-3">
                    <p class="text-xs font-medium text-slate-400">Codigo</p>
                    <p class="mt-1.5 text-sm font-semibold text-white">{{ $portalUser->employee_code ?: '-' }}</p>
                </div>
                <div class="rounded-xl bg-white/8 p-3">
                    <p class="text-xs font-medium text-slate-400">Vacaciones</p>
                    <p class="mt-1.5 text-sm font-semibold text-white">{{ $portalUser->annual_vacation_days }} dias totales</p>
                    <p class="mt-1 text-xs text-slate-300">{{ $usedVacationDays }} consumidos · {{ $remainingVacationDays }} disponibles</p>
                </div>
            </div>
        </article>

        <article class="flex min-w-0 flex-col justify-between rounded-2xl bg-white p-6 ring-1 ring-slate-200 shadow-sm lg:min-w-64">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Hoy</p>
                @if ($todayOffReason && ! $todayTimeEntry)
                    <p class="mt-3 text-base font-semibold text-slate-900">Día libre</p>
                    <p class="mt-1 text-sm text-slate-500">
                        @if ($todayOffReason === 'leave')
                            Ausencia aprobada
                        @elseif ($todayOffReason === 'festivo')
                            Festivo
                        @elseif ($todayOffReason === 'weekend')
                            Fin de semana
                        @endif
                    </p>
                @else
                    <p class="mt-3 text-4xl font-semibold tabular-nums text-slate-900">
                        {{ $todayTimeEntry?->check_in_time ? substr((string) $todayTimeEntry->check_in_time, 0, 5) : '--:--' }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        @if ($todayTimeEntry && $todayTimeEntry->status?->value === 'incomplete')
                            Entrada registrada
                        @elseif ($todayTimeEntry)
                            Jornada completa
                        @else
                            Sin fichaje
                        @endif
                    </p>
                @endif
            </div>
            <div class="mt-4">
                @if ($todayOffReason && ! $todayTimeEntry)
                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                        ¡Disfruta del día!
                    </span>
                @elseif ($todayTimeEntry && $todayTimeEntry->status?->value === 'incomplete')
                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                        Pendiente de salida
                    </span>
                @elseif ($todayTimeEntry)
                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                        Completo
                    </span>
                @else
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">
                        Sin registro
                    </span>
                @endif
            </div>
        </article>
    </div>

    {{-- Stats row --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <article class="rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Solicitudes</p>
                @if ($pendingLeaveRequestsCount > 0)
                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                        {{ $pendingLeaveRequestsCount }} pend.
                    </span>
                @endif
            </div>
            <p class="mt-3 text-3xl font-semibold tabular-nums text-slate-900">{{ $totalLeaveRequestsCount }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $totalLeaveRequestsCount }} registrada{{ $totalLeaveRequestsCount !== 1 ? 's' : '' }}</p>
        </article>

        <article class="rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Proxima ausencia</p>
                @if ($nextApprovedLeaveRequest)
                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-200">Aprobada</span>
                @endif
            </div>
            <p class="mt-3 text-3xl font-semibold tabular-nums text-slate-900">
                {{ $calendarDateLabel }}
            </p>
            <p class="mt-1 text-xs text-slate-500">{{ $calendarDescription }}</p>
        </article>

        <article class="rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Documentos</p>
            <p class="mt-3 text-3xl font-semibold tabular-nums text-slate-900">{{ $visibleDocumentsCount }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $documentDescription }}</p>
        </article>
    </div>

    {{-- Nav cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-portal.nav-card
            eyebrow="Acceso directo"
            title="Calendario"
            :description="$calendarDescription"
            :href="route('portal.calendar.index', $tenantRouteParameters)"
        />
        <x-portal.nav-card
            eyebrow="Acceso directo"
            title="Control horario"
            :description="$timeTrackingDescription"
            :href="route('portal.time-tracking.index', $tenantRouteParameters)"
        />
        <x-portal.nav-card
            eyebrow="Acceso directo"
            title="Solicitudes"
            :description="$requestsDescription"
            :href="route('portal.requests.index', $tenantRouteParameters)"
        />
        <x-portal.nav-card
            eyebrow="Acceso directo"
            title="Documentacion"
            :description="$documentDescription"
            :href="route('portal.documents.index', $tenantRouteParameters)"
        />
    </div>
</x-layouts.portal>