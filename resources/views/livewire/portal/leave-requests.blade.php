<div>
    @if ($submitted)
        <div class="mb-6 flex items-start gap-3 rounded-2xl bg-emerald-50 px-5 py-4 ring-1 ring-emerald-200">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <div>
                <p class="text-sm font-semibold text-emerald-800">Solicitud enviada correctamente</p>
                <p class="mt-0.5 text-xs text-emerald-700">Tu solicitud ha quedado registrada en estado <strong>pendiente</strong>. El responsable de tu departamento sera notificado para revisarla.</p>
            </div>
            <button wire:click="$set('submitted', false)" class="ml-auto text-emerald-400 hover:text-emerald-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_2fr]">
        <div class="flex flex-col gap-5">
            <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Vacaciones disponibles</p>
                <p class="mt-2 text-4xl font-semibold tabular-nums text-slate-900">{{ $this->remainingVacationDays }}</p>
                <p class="mt-1 text-xs text-slate-500">de {{ $vacationDays }} dias anuales</p>
                @if ($requestType === 'vacation' && $this->requestedDays > 0)
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>Dias solicitados</span>
                            <span class="font-semibold {{ $this->requestedDays > $this->remainingVacationDays ? 'text-red-600' : 'text-slate-900' }}">{{ $this->requestedDays }}</span>
                        </div>
                        <div class="mt-1.5 flex items-center justify-between text-xs text-slate-500">
                            <span>Saldo resultante</span>
                            @php($balance = $this->remainingVacationDays - $this->requestedDays)
                            <span class="font-semibold {{ $balance < 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $balance }}</span>
                        </div>
                        @if ($this->requestedDays > $this->remainingVacationDays)
                            <p class="mt-3 text-xs font-medium text-red-600">No tienes suficientes dias disponibles.</p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-900">Nueva solicitud</h2>
                <p class="mt-0.5 text-xs text-slate-500">Rellena el formulario y envia tu solicitud.</p>
                <form wire:submit="submit" class="mt-5 flex flex-col gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Tipo de solicitud <span class="text-red-500">*</span></label>
                        <select wire:model.live="requestType" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20">
                            <option value="">Selecciona un tipo&hellip;</option>
                            <option value="vacation">Vacaciones</option>
                            <option value="paid_leave">Permiso retribuido</option>
                        </select>
                        @error('requestType') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Fecha inicio <span class="text-red-500">*</span></label>
                            <input type="date" wire:model.live="startDate" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20">
                            @error('startDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Fecha fin <span class="text-xs font-normal text-slate-400">(inclusive)</span> <span class="text-red-500">*</span></label>
                            <input type="date" wire:model.live="endDate" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20">
                            @error('endDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Comentario <span class="text-xs font-normal text-slate-400">(opcional)</span></label>
                        <textarea wire:model="reason" rows="3" maxlength="500" placeholder="Añade una nota o motivo si lo deseas..."
                            class="mt-1.5 w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20"></textarea>
                        @error('reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        @if ($requestType === 'vacation' && $this->requestedDays > $this->remainingVacationDays) disabled @endif
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="submit">Enviar solicitud</span>
                        <span wire:loading wire:target="submit">Enviando...</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Mis solicitudes</h2>
                <p class="mt-0.5 text-xs text-slate-500">Historial de solicitudes enviadas</p>
            </div>
            @if ($leaveRequests->isEmpty())
                <div class="px-5 py-12 text-center">
                    <p class="text-sm text-slate-500">Todavia no has enviado ninguna solicitud.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wider text-slate-400">
                                <th class="px-5 py-3 text-left">Tipo</th>
                                <th class="px-5 py-3 text-center">Inicio</th>
                                <th class="px-5 py-3 text-center">Fin</th>
                                <th class="px-5 py-3 text-center">Estado</th>
                                <th class="px-5 py-3 text-left">Comentario</th>
                                <th class="px-5 py-3 text-center">Enviada</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($leaveRequests as $request)
                                <tr class="transition hover:bg-slate-50/60">
                                    <td class="px-5 py-4 font-medium text-slate-900">{{ $request->request_type->label() }}</td>
                                    <td class="px-5 py-4 text-center tabular-nums text-slate-600">{{ $request->start_date->translatedFormat('d M Y') }}</td>
                                    <td class="px-5 py-4 text-center tabular-nums text-slate-600">{{ $request->end_date->translatedFormat('d M Y') }}</td>
                                    <td class="px-5 py-4 text-center">
                                        @php($status = $request->status)
                                        @if ($status === \App\Enums\LeaveRequestStatus::Approved)
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">Aprobada</span>
                                        @elseif ($status === \App\Enums\LeaveRequestStatus::Rejected)
                                            <span class="inline-flex rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-red-200">Rechazada</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="max-w-xs px-5 py-4 text-xs text-slate-500">
                                        {{ $request->reason ? \Illuminate\Support\Str::limit($request->reason, 60) : chr(0x2014) }}
                                    </td>
                                    <td class="px-5 py-4 text-center text-xs tabular-nums text-slate-500">{{ $request->created_at->translatedFormat('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($leaveRequests->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4">
                        {{ $leaveRequests->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
