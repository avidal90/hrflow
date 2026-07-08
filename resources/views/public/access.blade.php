<x-layouts.public title="Acceso | HRFlow">
    <div class="flex min-h-[calc(100vh-4rem)] items-center bg-slate-50 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-4xl">
            <div class="mb-10">
                <span
                    class="inline-flex items-center rounded-full bg-blue-50 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-blue-700 ring-1 ring-blue-200">Acceso</span>
                <h1 class="mt-5 text-3xl font-semibold text-slate-900 sm:text-4xl">Acceso al portal del empleado</h1>
                <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">
                    El portal del empleado mantiene su propio acceso por empresa. Así, un perfil administrativo también
                    puede entrar como empleado para fichar, revisar su calendario o gestionar vacaciones sin interferir
                    con el login del backoffice.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <article class="rounded-2xl bg-white p-6 ring-1 ring-slate-200 shadow-sm">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-sm font-bold text-white">
                        1</div>
                    <h2 class="mt-4 text-lg font-semibold text-slate-900">Abre el portal de tu empresa</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Introduce el código de empresa para ir al login
                        tenant-aware del portal. En la demo puedes usar, por ejemplo, northwind-demo o acme-demo.</p>

                    <form method="POST" action="{{ route('public.portal.access') }}" class="mt-5 flex flex-col gap-4">
                        @csrf

                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-slate-700" for="tenant">Código de empresa</label>
                            <input id="tenant" type="text" name="tenant" value="{{ old('tenant') }}" required
                                placeholder="northwind-demo"
                                class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20">
                            @error('tenant')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Ir al portal
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </form>
                </article>

                <article class="rounded-2xl bg-amber-50 p-6 ring-1 ring-amber-200">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-sm font-bold text-white">
                        2</div>
                    <h2 class="mt-4 text-lg font-semibold text-slate-900">Panel interno separado</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-700">El backoffice en Filament mantiene su propio login.
                        Si tu perfil tiene permisos administrativos, podrás saltar desde el portal a la zona interna sin
                        perder esta entrada como empleado.</p>
                    <a href="{{ route('login') }}"
                        class="mt-5 inline-flex items-center gap-1.5 rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-amber-100">
                        Ir a administración
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                    <div
                        class="mt-4 rounded-xl border border-dashed border-amber-300 bg-white px-4 py-3 text-xs leading-6 text-slate-500">
                        Portal empleado: /portal/tu-tenant/login<br>
                        Administración: /login
                    </div>
                </article>
            </div>
        </div>
    </div>
</x-layouts.public>
