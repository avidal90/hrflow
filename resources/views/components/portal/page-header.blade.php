@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div @class(['mb-6', 'flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between' => trim($slot) !== ''])>
    <div>
        @if (filled($eyebrow))
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ $eyebrow }}</p>
        @endif
        <h1 class="mt-1 text-2xl font-semibold text-slate-900">{{ $title }}</h1>
        @if (filled($description))
            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $description }}</p>
        @endif
    </div>
    @if (trim($slot) !== '')
        <div class="flex flex-wrap gap-3 sm:shrink-0">
            {{ $slot }}
        </div>
    @endif
</div>