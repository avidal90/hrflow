@php($tenantRouteParameters = ['tenant' => tenant()->getTenantKey()])

<x-layouts.portal :title="$title . ' | Portal HRFlow'">
    <x-portal.page-header :eyebrow="$eyebrow" :title="$title" :description="$description" />

    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
        <div
            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl font-bold text-slate-400">
            {{ str($title)->substr(0, 1)->upper() }}
        </div>
        <h2 class="mt-4 text-lg font-semibold text-slate-900">Módulo en desarrollo</h2>
        <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-600">
            La estructura base está lista. Los flujos operativos se implementarán en las siguientes fases.
        </p>
        <a href="{{ route('portal.dashboard', $tenantRouteParameters) }}"
            class="mt-6 inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
            Volver al dashboard
        </a>
    </div>
</x-layouts.portal>
