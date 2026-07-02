@props([
    'description',
    'eyebrow',
    'href',
    'title',
])

<a href="{{ $href }}" class="group flex flex-col rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm transition hover:shadow-md hover:ring-slate-300">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ $eyebrow }}</p>
            <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ $title }}</h3>
        </div>
        <span class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-500 transition group-hover:bg-amber-500 group-hover:text-white">
            {{ str($title)->substr(0, 1)->upper() }}
        </span>
    </div>
    <p class="mt-3 flex-1 text-sm leading-6 text-slate-600">{{ $description }}</p>
    <div class="mt-5 flex items-center gap-1 border-t border-slate-100 pt-4 text-xs font-semibold text-slate-400 transition group-hover:text-amber-600">
        <span>Ir al modulo</span>
        <svg class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
    </div>
</a>