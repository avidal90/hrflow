@php($tenantRouteParameters = ['tenant' => tenant()->getTenantKey()])
@php($eventsUrl = route('portal.calendar.events', $tenantRouteParameters))

<x-layouts.portal :title="'Calendario | ' . (tenant()?->name ?? 'HRFlow')">

    <x-portal.page-header
        eyebrow="Mi calendario"
        title="Calendario laboral"
        description="Consulta tus turnos asignados, vacaciones aprobadas y festivos del año."
    />

    {{-- Leyenda --}}
    <div class="mb-5 flex flex-wrap items-center gap-5 text-xs font-medium text-slate-600">
        <span class="flex items-center gap-2">
            <span class="h-3 w-5 rounded-sm ring-1" style="background-color:#dbeafe;border-color:#93c5fd;"></span>
            Turno asignado
        </span>
        <span class="flex items-center gap-2">
            <span class="h-3 w-5 rounded-sm ring-1" style="background-color:#d1fae5;border-color:#10b981;"></span>
            Salidas programadas
        </span>
        <span class="flex items-center gap-2">
            <span class="h-3 w-5 rounded-sm ring-1" style="background-color:#fef3c7;border-color:#f59e0b;"></span>
            Festivo
        </span>
    </div>

    {{-- Contenedor del calendario --}}
    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6">
        <div id="hrflow-calendar"></div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const calendarEl = document.getElementById('hrflow-calendar');

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'es',
                    firstDay: 1,
                    height: 'auto',
                    fixedWeekCount: false,

                    headerToolbar: {
                        left:   'prev,next today',
                        center: 'title',
                        right:  'dayGridMonth,dayGridWeek',
                    },

                    buttonText: {
                        today: 'Hoy',
                        month: 'Mes',
                        week:  'Semana',
                    },

                    events: {
                        url: '{{ $eventsUrl }}',
                        failure: function () {
                            console.error('No se pudieron cargar los eventos del calendario.');
                        },
                    },

                    eventDidMount: function (info) {
                        info.el.setAttribute('title', info.event.title);
                    },
                });

                calendar.render();
            });
        </script>
    @endpush

</x-layouts.portal>
