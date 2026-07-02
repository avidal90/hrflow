<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'HRFlow' }}</title>
        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css'])
        @endif
    </head>
    <body class="bg-white text-slate-900 antialiased">
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur-sm">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="{{ route('public.home') }}" class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500 text-sm font-bold text-white">HR</span>
                    <span class="text-base font-semibold text-slate-900">HRFlow</span>
                </a>

                <nav class="flex items-center gap-2">
                    <a href="{{ route('public.access') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                        Portal empleado
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Administracion
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                </nav>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>
    </body>
</html>