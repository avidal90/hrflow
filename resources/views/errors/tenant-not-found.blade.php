<x-layouts.public title="Portal no disponible · HRFlow">
    <div class="flex min-h-[70vh] items-center justify-center px-4">
        <div class="w-full max-w-md text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
            </div>

            <h1 class="mt-6 text-2xl font-semibold text-slate-900">Portal no disponible</h1>
            <p class="mt-3 text-base text-slate-500">
                La dirección que has introducido no corresponde a ningún portal activo de HRFlow.
                Es posible que el código de empresa sea incorrecto o que el portal haya sido desactivado.
            </p>

            <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('public.access') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Buscar mi empresa
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
                <a href="{{ route('public.home') }}"
                   class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Volver al inicio
                </a>
            </div>
        </div>
    </div>
</x-layouts.public>
