@php
    use App\Enums\DocumentFolder;
    $tenantRouteParameters = ['tenant' => $tenantKey];

    $folderMeta = [
        DocumentFolder::Payrolls->value => [
            'label'       => 'Nóminas',
            'description' => 'Recibos de salario y retenciones.',
            'color'       => 'bg-amber-50 ring-amber-200',
            'iconColor'   => 'text-amber-500',
            'icon'        => 'payrolls',
        ],
        DocumentFolder::Contracts->value => [
            'label'       => 'Contratos',
            'description' => 'Contratos de trabajo y anexos.',
            'color'       => 'bg-blue-50 ring-blue-200',
            'iconColor'   => 'text-blue-500',
            'icon'        => 'contracts',
        ],
        DocumentFolder::Policies->value => [
            'label'       => 'Normativas',
            'description' => 'Políticas internas y reglamentos.',
            'color'       => 'bg-emerald-50 ring-emerald-200',
            'iconColor'   => 'text-emerald-500',
            'icon'        => 'policies',
        ],
        DocumentFolder::Other->value => [
            'label'       => 'Otros',
            'description' => 'Documentos varios y comunicados.',
            'color'       => 'bg-slate-50 ring-slate-200',
            'iconColor'   => 'text-slate-400',
            'icon'        => 'other',
        ],
    ];
@endphp

<div>
    @if ($activeFolderEnum === null)
        {{-- Grid de categorías --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (DocumentFolder::cases() as $folder)
                @php
                    $meta  = $folderMeta[$folder->value];
                    $count = $this->folderCounts[$folder->value] ?? 0;
                @endphp
                <button
                    wire:click="selectFolder('{{ $folder->value }}')"
                    class="group flex cursor-pointer flex-col items-start gap-4 rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm transition hover:shadow-md hover:ring-slate-300 text-left"
                >
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $meta['color'] }} ring-1">
                        <svg class="h-5 w-5 {{ $meta['iconColor'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            @switch($meta['icon'])
                                @case('payrolls')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                    @break
                                @case('contracts')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    @break
                                @case('policies')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                    @break
                                @default
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                            @endswitch
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-900 group-hover:text-amber-600 transition">{{ $meta['label'] }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $meta['description'] }}</p>
                    </div>
                    <span class="mt-auto text-xs font-medium text-slate-400">
                        {{ $count }} {{ $count === 1 ? 'documento' : 'documentos' }}
                    </span>
                </button>
            @endforeach
        </div>

    @else
        {{-- Cabecera breadcrumb --}}
        @php $meta = $folderMeta[$activeFolderEnum->value]; @endphp
        <div class="mb-6 flex items-center gap-3">
            <button
                wire:click="clearFolder"
                class="flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-slate-900"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Categorías
            </button>
            <span class="text-slate-300">/</span>
            <span class="flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                <svg class="h-4 w-4 {{ $meta['iconColor'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    @switch($meta['icon'])
                        @case('payrolls')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            @break
                        @case('contracts')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            @break
                        @case('policies')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            @break
                        @default
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                    @endswitch
                </svg>
                {{ $meta['label'] }}
            </span>
        </div>

        {{-- Tabla --}}
        @if ($documents->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-2xl bg-white py-16 ring-1 ring-slate-200 shadow-sm text-center">
                <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <p class="mt-3 text-sm font-medium text-slate-500">Sin documentos en esta categoría</p>
                <p class="mt-1 text-xs text-slate-400">Aquí aparecerán los documentos que RR.HH. comparta contigo.</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm">
                <table class="w-full min-w-[640px] text-sm" style="border-collapse:collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-400" style="text-align:left;width:40%">Nombre</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-400" style="text-align:left;width:12%">Tipo</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-400" style="text-align:left;width:18%">Fecha de subida</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-400" style="text-align:left;width:12%">Tamaño</th>
                            <th class="px-4 py-3" style="width:18%"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($documents as $doc)
                            @php
                                $sizeLabel = $doc->file_size
                                    ? \Illuminate\Support\Number::fileSize($doc->file_size, precision: 1)
                                    : '—';

                                $ext = $doc->original_filename
                                    ? strtoupper(pathinfo($doc->original_filename, PATHINFO_EXTENSION))
                                    : null;

                                if (! $ext && $doc->mime_type) {
                                    $ext = match ($doc->mime_type) {
                                        'application/pdf'                                                          => 'PDF',
                                        'application/msword'                                                       => 'DOC',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
                                        'application/vnd.oasis.opendocument.text'                                 => 'ODT',
                                        'application/vnd.ms-excel'                                                => 'XLS',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'       => 'XLSX',
                                        default                                                                    => null,
                                    };
                                }

                                $extStyle = match ($ext) {
                                    'PDF'        => 'background:#fef2f2;color:#dc2626;outline:1px solid #fecaca',
                                    'DOC','DOCX' => 'background:#eff6ff;color:#2563eb;outline:1px solid #bfdbfe',
                                    'XLS','XLSX' => 'background:#f0fdf4;color:#16a34a;outline:1px solid #bbf7d0',
                                    'ODT'        => 'background:#faf5ff;color:#9333ea;outline:1px solid #e9d5ff',
                                    default      => 'background:#f8fafc;color:#64748b;outline:1px solid #e2e8f0',
                                };
                            @endphp
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3" style="vertical-align:middle">
                                    <p class="font-medium text-slate-900">{{ $doc->name }}</p>
                                    @if ($doc->description)
                                        <p class="mt-0.5 text-xs text-slate-400">{{ Str::limit($doc->description, 80) }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3" style="vertical-align:middle">
                                    @if ($ext)
                                        <span style="display:inline-flex;align-items:center;border-radius:6px;padding:2px 8px;font-size:0.7rem;font-weight:600;{{ $extStyle }}">{{ $ext }}</span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-500" style="vertical-align:middle;white-space:nowrap">
                                    {{ ($doc->uploaded_at ?? $doc->created_at)?->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-slate-400" style="vertical-align:middle;white-space:nowrap">
                                    {{ $sizeLabel }}
                                </td>
                                <td class="px-4 py-3 text-right" style="vertical-align:middle">
                                    <a
                                        href="{{ route('portal.documents.download', array_merge($tenantRouteParameters, ['document' => $doc->id])) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 ring-1 ring-slate-200 transition hover:bg-amber-50 hover:text-amber-700 hover:ring-amber-200"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                        Descargar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($documents->hasPages())
                    <div class="border-t border-slate-100 px-4 py-3">
                        {{ $documents->links() }}
                    </div>
                @endif
            </div>
        @endif
    @endif
</div>
