<x-layouts.portal :title="'Solicitudes | ' . (tenant()?->name ?? 'HRFlow')">

    <x-portal.page-header
        eyebrow="Solicitudes"
        title="Mis solicitudes"
        description="Gestiona tus peticiones de vacaciones y permisos."
    />

    <livewire:portal.leave-requests />

</x-layouts.portal>
