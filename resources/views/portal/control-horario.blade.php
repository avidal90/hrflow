<x-layouts.portal :title="'Control horario | ' . (tenant()?->name ?? 'HRFlow')">

    <x-portal.page-header
        eyebrow="Control horario"
        title="Registro de jornada"
        description="Inicia y finaliza tu jornada laboral y consulta tu historial de fichajes."
    />

    <livewire:portal.time-tracker />

</x-layouts.portal>
