<div>
    {{-- Tarjeta de fichaje activo --}}
    @if ($activeEntry)
        <div
            x-data="{
                startTs: {{ $checkInTimestamp }},
                elapsed: 0,
                interval: null,
                init() {
                    this.elapsed = Math.max(0, Math.floor(Date.now() / 1000) - this.startTs)
                    this.interval = setInterval(() => {
                        this.elapsed = Math.max(0, Math.floor(Date.now() / 1000) - this.startTs)
                    }, 1000)
                },
                get formatted() {
                    const h = Math.floor(this.elapsed / 3600)
                    const m = Math.floor((this.elapsed % 3600) / 60)
                    const s = this.elapsed % 60
                    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0')
                }
            }"
            class="mb-6 overflow-hidden rounded-2xl bg-slate-900 text-white shadow-sm"
        >
            <div class="flex flex-col items-start justify-between gap-6 p-6 sm:flex-row sm:items-center">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"></span>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Jornada en curso</p>
                    </div>
                    <p class="mt-3 font-mono text-5xl font-semibold tabular-nums tracking-tight" x-text="formatted">00:00:00</p>
                    <p class="mt-2 text-sm text-slate-400">
                        Entrada registrada a las <span class="font-medium text-white">{{ substr((string) $activeEntry->check_in_time, 0, 5) }}</span>
                    </p>
                </div>

                <button
                    wire:click="stopTracking"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-5 py-3 text-sm font-semibold text-white ring-1 ring-white/20 transition hover:bg-white/20 disabled:opacity-50"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 0 1 7.5 5.25h9a2.25 2.25 0 0 1 2.25 2.25v9a2.25 2.25 0 0 1-2.25 2.25h-9a2.25 2.25 0 0 1-2.25-2.25v-9Z" />
                    </svg>
                    <span wire:loading.remove wire:target="stopTracking">Finalizar jornada</span>
                    <span wire:loading wire:target="stopTracking">Guardando...</span>
                </button>
            </div>
        </div>
    @else
        <div class="mb-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-col items-start justify-between gap-6 p-6 sm:flex-row sm:items-center">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Sin jornada activa</p>
                    </div>
                    <p class="mt-3 font-mono text-5xl font-semibold tabular-nums tracking-tight text-slate-200">00:00:00</p>
                    <p class="mt-2 text-sm text-slate-500">Pulsa el boton para iniciar tu jornada laboral.</p>
                </div>

                <button
                    wire:click="startTracking"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-400 disabled:opacity-50"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                    </svg>
                    <span wire:loading.remove wire:target="startTracking">Iniciar jornada</span>
                    <span wire:loading wire:target="startTracking">Iniciando...</span>
                </button>
            </div>
        </div>
    @endif

    {{-- Historial de fichajes --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Historial de fichajes</h2>
            <p class="mt-0.5 text-xs text-slate-500">Tus ultimos registros de jornada</p>
        </div>

        @if ($recentEntries->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-sm text-slate-500">Todavia no tienes registros de jornada.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-3 text-left">Fecha</th>
                            <th class="px-5 py-3 text-center">Entrada</th>
                            <th class="px-5 py-3 text-center">Salida</th>
                            <th class="px-5 py-3 text-center">Duracion</th>
                            <th class="px-5 py-3 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($recentEntries as $entry)
                            <tr class="transition hover:bg-slate-50/60">
                                <td class="px-5 py-4 font-medium text-slate-900">
                                    {{ $entry->work_date->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-5 py-4 text-center tabular-nums text-slate-600">
                                    {{ substr((string) $entry->check_in_time, 0, 5) }}
                                </td>
                                <td class="px-5 py-4 text-center tabular-nums text-slate-600">
                                    {{ $entry->check_out_time ? substr((string) $entry->check_out_time, 0, 5) : '—' }}
                                </td>
                                <td class="px-5 py-4 text-center tabular-nums text-slate-600">
                                    @if ($entry->duration_minutes)
                                        @php($h = intdiv($entry->duration_minutes, 60))
                                        @php($m = $entry->duration_minutes % 60)
                                        {{ $h > 0 ? "{$h}h " : '' }}{{ $m > 0 ? "{$m}m" : ($h > 0 ? '' : '0m') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if ($entry->status === \App\Enums\TimeEntryStatus::Complete)
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                            Completo
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                                            En curso
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($recentEntries->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $recentEntries->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
