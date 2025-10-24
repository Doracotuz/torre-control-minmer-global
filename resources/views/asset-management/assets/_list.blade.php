<div class="table-container border rounded-lg overflow-hidden">
    <div class="responsive-table-header hidden md:grid md:grid-cols-7 gap-4 bg-[#2c3856] p-4 font-bold text-xs text-[#ffffff] uppercase tracking-wider">
        <div class="col-span-1">Etiqueta</div>
        <div class="col-span-1">Categoría</div>
        <div class="col-span-1">Modelo</div>
        <div class="col-span-1">Estatus</div>
        <div class="col-span-1">Asignado a</div>
        <div class="col-span-1">Ubicación</div>
        <div class="col-span-1 text-right">Acciones</div>
    </div>

    <div classD="divide-y md:divide-y-0">
        @forelse ($assets as $asset)
            <div class="hidden md:grid md:grid-cols-7 gap-4 p-4 items-center hover:bg-gray-50 transition-colors">
                <div><a href="{{ route('asset-management.assets.show', $asset) }}" class="font-mono text-[var(--color-primary)] hover:underline font-semibold">{{ $asset->asset_tag }}</a></div>
                <div class="text-gray-600">{{ $asset->model->category->name ?? 'N/A' }}</div>
                <div class="font-semibold text-gray-800">{{ $asset->model->name ?? 'N/A' }}</div>
                <div><span class="status-badge status-{{ Str::kebab($asset->status) }}">{{ $asset->status }}</span></div>
                <div class="text-gray-600">{{ $asset->currentAssignment->member->name ?? '---' }}</div>
                <div class="text-gray-600">{{ $asset->site->name ?? 'N/A' }}</div>
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('asset-management.assets.show', $asset) }}" title="Ver Detalles"><i class="fas fa-eye"></i></a>
                    <a href="{{ route('asset-management.assets.edit', $asset) }}" title="Editar Activo"><i class="fas fa-pencil-alt"></i></a>
                </div>
            </div>

            <div class="asset-card md:hidden">
                <div class="asset-card-row">
                    <span class="asset-card-label">Etiqueta</span>
                    <span class="asset-card-value"><a href="{{ route('asset-management.assets.show', $asset) }}" class="font-mono text-[var(--color-primary)] hover:underline font-semibold">{{ $asset->asset_tag }}</a></span>
                </div>
                <div class="asset-card-row">
                    <span class="asset-card-label">Estatus</span>
                    <span class="asset-card-value"><span class="status-badge status-{{ Str::kebab($asset->status) }}">{{ $asset->status }}</span></span>
                </div>
                <div class="asset-card-row">
                    <span class="asset-card-label">Modelo</span>
                    <span class="asset-card-value font-semibold">{{ $asset->model->name ?? 'N/A' }}</span>
                </div>
                <div class="asset-card-row">
                    <span class="asset-card-label">Asignado a</span>
                    <span class="asset-card-value">{{ $asset->currentAssignment->member->name ?? '---' }}</span>
                </div>
                <div class="asset-card-row">
                    <span class="asset-card-label">Ubicación</span>
                    <span class="asset-card-value">{{ $asset->site->name ?? 'N/A' }}</span>
                </div>
                <div class="flex items-center justify-end space-x-6 pt-4">
                    <a href="{{ route('asset-management.assets.show', $asset) }}" class="btn btn-secondary py-2 px-4 text-sm">Ver Detalles</a>
                    <a href="{{ route('asset-management.assets.edit', $asset) }}" class="btn btn-primary py-2 px-4 text-sm">Editar</a>
                </div>
            </div>
        @empty
            <div class="text-center p-12 col-span-full">
                <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No se encontraron activos que coincidan con los filtros.</p>
            </div>
        @endforelse
    </div>
</div>

@if ($assets->hasPages())
    <div class="p-4 bg-gray-50 border-t mt-4 rounded-b-lg">
        {!! $assets->links() !!}
    </div>
@endif