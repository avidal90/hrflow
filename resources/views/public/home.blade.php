<x-layouts.public title="HRFlow · Plataforma de Recursos Humanos">
    {{-- Hero --}}
    <section
        class="border-b border-slate-100 bg-gradient-to-b from-slate-50 to-white px-4 pb-20 pt-16 text-center sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <span
                class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-amber-700 ring-1 ring-amber-200">
                Laravel · Filament · Livewire
            </span>
            <h1 class="mt-8 text-5xl font-semibold tracking-tight text-slate-900 sm:text-6xl">
                Gestiona tu equipo<br>
                <span class="text-amber-500">con HRFlow</span>
            </h1>
            <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-slate-600">
                Plataforma SaaS de Recursos Humanos con backoffice en Filament y portal del empleado en Blade y
                Livewire. El panel interno y el portal mantienen accesos separados para que los perfiles administrativos
                también puedan fichar o pedir vacaciones como empleados.
            </p>
            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('public.access') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Cómo entrar al portal
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
                <a href="{{ route('login') }}"
                    class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Panel de administración
                </a>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mb-10 text-center">
            <h2 class="text-2xl font-semibold text-slate-900">Todo lo que necesita tu equipo</h2>
            <p class="mt-2 text-sm text-slate-500">Cuatro módulos principales accesibles desde el portal del empleado,
                con salto al backoffice para quien tenga permisos.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <article class="rounded-2xl bg-white p-6 ring-1 ring-slate-200 shadow-sm">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-sm font-bold text-blue-700">
                    C</div>
                <h3 class="mt-4 font-semibold text-slate-900">Calendario</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">Festivos, turnos asignados y ausencias en una vista
                    laboral unificada.</p>
            </article>
            <article class="rounded-2xl bg-slate-900 p-6">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-sm font-bold text-white">
                    H</div>
                <h3 class="mt-4 font-semibold text-white">Control horario</h3>
                <p class="mt-2 text-sm leading-6 text-slate-300">Entrada, pausas y salida con historial de jornada
                    integrado.</p>
            </article>
            <article class="rounded-2xl bg-white p-6 ring-1 ring-slate-200 shadow-sm">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-sm font-bold text-amber-700">
                    S</div>
                <h3 class="mt-4 font-semibold text-slate-900">Solicitudes</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">Vacaciones y ausencias con flujo de aprobación y
                    estados claros.</p>
            </article>
            <article class="rounded-2xl bg-amber-500 p-6">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 text-sm font-bold text-white">
                    D</div>
                <h3 class="mt-4 font-semibold text-white">Documentación</h3>
                <p class="mt-2 text-sm leading-6 text-white/80">Expediente personal con acceso protegido por tenant y
                    rol.</p>
            </article>
        </div>
    </section>

    {{-- Portal note --}}
    <section class="border-t border-slate-200 bg-slate-900 px-4 py-12 text-center sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Accesos separados</p>
            <p class="mt-4 text-lg font-semibold text-white">El portal y la administración conviven sin mezclar sus
                entradas.</p>
            <p class="mt-3 text-sm leading-6 text-slate-400">
                Los empleados acceden a su portal mediante el código de empresa y los perfiles con permisos internos
                usan el login administrativo. Si un usuario tiene ambos contextos, puede entrar al portal y saltar desde
                allí al backoffice.
            </p>
        </div>
    </section>
</x-layouts.public>
