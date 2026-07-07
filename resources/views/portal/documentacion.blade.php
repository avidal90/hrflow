<x-layouts.portal :title="'Documentación | ' . (tenant()?->name ?? 'HRFlow')">

    <x-portal.page-header
        eyebrow="Documentación"
        title="Mis documentos"
        description="Consulta y descarga los documentos que RR.HH. ha compartido contigo."
    />

    <livewire:portal.documents />

</x-layouts.portal>
