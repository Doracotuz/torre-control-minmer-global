<div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Activo</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Categoría & Modelo</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estatus</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ubicación / Asignación</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            @forelse ($assets as $asset)
                <tbody x-data="{ expanded: false }" class="border-b border-gray-100 last:border-0 group hover:bg-blue-50/30 transition-colors duration-200">
                    <tr class="cursor-pointer" :class="{'bg-blue-50/50': expanded}" @click="expanded = !expanded">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-gray-100 flex items-center justify-center text-[var(--primary)] shadow-sm font-bold text-xs">
                                    {{ substr($asset->asset_tag, -3) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-[var(--primary)] font-mono">{{ $asset->asset_tag }}</div>
                                    <div class="text-xs text-gray-500">{{ $asset->serial_number }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $asset->model->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded inline-block mt-1">
                                {{ $asset->model->category->name ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $asset->status == 'En Almacén' ? 'bg-green-100 text-green-800 border-green-200' : 
                                  ($asset->status == 'Asignado' ? 'bg-blue-100 text-blue-800 border-blue-200' : 
                                  ($asset->status == 'En Reparación' ? 'bg-orange-100 text-orange-800 border-orange-200' : 'bg-gray-100 text-gray-800')) }}">
                                <span class="w-2 h-2 mr-1.5 rounded-full {{ $asset->status == 'En Almacén' ? 'bg-green-500' : ($asset->status == 'Asignado' ? 'bg-blue-500' : 'bg-orange-500') }}"></span>
                                {{ $asset->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 font-medium"><i class="fas fa-map-marker-alt text-gray-400 mr-1"></i> {{ $asset->site->name ?? 'N/A' }}</div>
                            @if($asset->currentAssignments->count() > 0)
                                <div class="text-xs text-blue-600 mt-1 font-semibold">
                                    <i class="fas fa-user mr-1"></i> {{ $asset->currentAssignments->first()->member->name }}
                                    @if($asset->currentAssignments->count() > 1) (+{{ $asset->currentAssignments->count() - 1 }}) @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('asset-management.assets.show', $asset) }}" class="p-2 text-gray-500 hover:text-[var(--primary)] hover:bg-gray-100 rounded-lg transition-all" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('asset-management.assets.edit', $asset) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Editar">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <button @click.stop="expanded = !expanded" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-chevron-down transform transition-transform duration-200" :class="{'rotate-180': expanded}"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="expanded" x-cloak x-transition class="bg-gray-50/50 border-b border-gray-100">
                        <td colspan="5" class="px-6 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div class="p-3 bg-white rounded border border-gray-200">
                                    <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Specs</h4>
                                    <p><strong>CPU:</strong> {{ $asset->cpu ?? '-' }}</p>
                                    <p><strong>RAM:</strong> {{ $asset->ram ?? '-' }}</p>
                                    <p><strong>Storage:</strong> {{ $asset->storage ?? '-' }}</p>
                                </div>
                                <div class="p-3 bg-white rounded border border-gray-200">
                                    <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Garantía & Compra</h4>
                                    <p><strong>Comprado:</strong> {{ $asset->purchase_date ? date('d/m/Y', strtotime($asset->purchase_date)) : '-' }}</p>
                                    <p><strong>Fin Garantía:</strong> {{ $asset->warranty_end_date ? date('d/m/Y', strtotime($asset->warranty_end_date)) : '-' }}</p>
                                </div>
                                <div class="p-3 bg-white rounded border border-gray-200">
                                    <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Notas</h4>
                                    <p class="text-gray-600 italic">{{ $asset->notes ?? 'Sin notas adicionales.' }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            @empty
                <tbody>
                    <tr><td colspan="5" class="p-8 text-center text-gray-500">No se encontraron activos.</td></tr>
                </tbody>
            @endforelse
        </table>
    </div>
    
    @if ($assets->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            {!! $assets->links() !!}
        </div>
    @endif
</div>