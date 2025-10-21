<div x-data x-init="$watch('$store.selection.ids', (newIds) => {
    const pageIds = {{ $locations->pluck('id')->toJson() }};
    const allOnPageSelected = pageIds.length > 0 && pageIds.every(id => newIds.includes(id));
    $store.selection.showSelectAll = allOnPageSelected && {{ $locations->hasMorePages() ? 'true' : 'false' }};
})">

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mapa del Almacén (Ubicaciones)
            </h2>
            <div class="flex items-center space-x-2">
                <form action="{{ route('wms.locations.print-labels') }}" method="POST" target="_blank">
                    @csrf
                    <template x-for="id in $store.selection.ids" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                    <button type="submit" :disabled="$store.selection.ids.length === 0" class="px-4 py-2 bg-gray-700 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-800 disabled:opacity-50">
                        <i class="fas fa-print mr-2"></i> Imprimir (<span x-text="$store.selection.ids.length"></span>)
                    </button>
                    <a href="{{ route('wms.locations.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-indigo-700">Añadir Ubicación</a>
                    <a href="{{ route('wms.locations.export-csv', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-green-700">
                        <i class="fas fa-file-excel mr-2"></i> Exportar a CSV
                    </a>                    
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Mensajes --}}
            @if (session('success'))<div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert"><p>{{ session('success') }}</p></div>@endif
            @if (session('error'))<div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert"><p>{{ session('error') }}</p></div>@endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @php
                    $total = $kpis['total_locations'] > 0 ? $kpis['total_locations'] : 1;
                    $kpi_cards = [
                        ['label' => 'Ubicaciones Totales', 'value' => $kpis['total_locations'], 'percentage' => 100, 'icon' => 'fa-warehouse', 'color' => 'indigo'],
                        ['label' => 'Almacenamiento (Storage)', 'value' => $kpis['storage'], 'percentage' => ($kpis['storage'] / $total) * 100, 'icon' => 'fa-boxes', 'color' => 'blue'],
                        ['label' => 'Picking', 'value' => $kpis['picking'], 'percentage' => ($kpis['picking'] / $total) * 100, 'icon' => 'fa-hand-pointer', 'color' => 'sky'],
                        ['label' => 'Recepción (Receiving)', 'value' => $kpis['receiving'], 'percentage' => ($kpis['receiving'] / $total) * 100, 'icon' => 'fa-truck-loading', 'color' => 'emerald'],
                        ['label' => 'Embarque (Shipping)', 'value' => $kpis['shipping'], 'percentage' => ($kpis['shipping'] / $total) * 100, 'icon' => 'fa-truck-moving', 'color' => 'orange'],
                        ['label' => 'Control de Calidad', 'value' => $kpis['quality_control'], 'percentage' => ($kpis['quality_control'] / $total) * 100, 'icon' => 'fa-check-double', 'color' => 'rose'],
                    ];
                @endphp

                @foreach($kpi_cards as $card)
                    @if($card['value'] > 0 || $card['label'] === 'Ubicaciones Totales')
                    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 flex items-center justify-between transition-transform hover:scale-105">
                        <div>
                            <span class="text-sm font-medium text-gray-500">{{ $card['label'] }}</span>
                            <p class="text-3xl font-bold text-gray-800">{{ number_format($card['value']) }}</p>
                            <p class="text-xs text-gray-400">{{ number_format($card['percentage'], 1) }}% del total</p>
                        </div>
                        <div class="bg-{{ $card['color'] }}-100 text-{{ $card['color'] }}-600 p-4 rounded-full">
                            <i class="fas {{ $card['icon'] }} fa-lg"></i>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>

            <div class="mb-8 p-6 bg-white border rounded-lg shadow-sm">
                <h3 class="font-semibold text-lg mb-3 text-gray-800">Importación Masiva</h3>
                <div class="flex flex-col sm:flex-row items-start sm:items-center sm:space-x-4">
                    <a href="{{ route('wms.locations.template') }}" class="text-sm font-medium text-indigo-600 hover:underline mb-4 sm:mb-0">
                        <i class="fas fa-download mr-1"></i> Descargar Plantilla CSV
                    </a>
                    <form action="{{ route('wms.locations.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center">
                        @csrf
                        <input type="file" name="file" accept=".csv" required class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-md ml-2 shadow-sm hover:bg-black">Importar</button>
                    </form>
                </div>
            </div>            

            <div class="mb-8 p-6 bg-white border rounded-lg shadow-sm">
                <form id="filters-form" action="{{ route('wms.locations.index') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 items-end">
                        <div><label class="text-xs text-gray-500">Almacén</label><select name="warehouse_id" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm text-sm"><option value="">Todos</option>@foreach($filters['warehouses'] as $warehouse)<option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>@endforeach</select></div>
                        <div><label class="text-xs text-gray-500">Pasillo</label><select name="aisle" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm text-sm"><option value="">Todos</option>@foreach($filters['aisles'] as $aisle)<option value="{{ $aisle }}" @selected(request('aisle') == $aisle)>{{ $aisle }}</option>@endforeach</select></div>
                        <div><label class="text-xs text-gray-500">Rack</label><select name="rack" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm text-sm"><option value="">Todos</option>@foreach($filters['racks'] as $rack)<option value="{{ $rack }}" @selected(request('rack') == $rack)>{{ $rack }}</option>@endforeach</select></div>
                        <div><label class="text-xs text-gray-500">Nivel</label><select name="shelf" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm text-sm"><option value="">Todos</option>@foreach($filters['shelves'] as $shelf)<option value="{{ $shelf }}" @selected(request('shelf') == $shelf)>{{ $shelf }}</option>@endforeach</select></div>
                        <div><label class="text-xs text-gray-500">Tipo</label><select name="type" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm text-sm"><option value="">Todos</option>@foreach(['storage' => 'Almacenamiento', 'picking' => 'Picking', 'receiving' => 'Recepción', 'shipping' => 'Embarque', 'quality_control' => 'Control de Calidad'] as $value => $label)<option value="{{ $value }}" @selected(request('type') == $value)>{{ $label }}</option>@endforeach</select></div>
                    </div>
                </form>
            </div>

            <div x-show="$store.selection.showSelectAll" x-transition class="mb-4 p-3 bg-blue-100 text-blue-800 rounded-md text-sm font-semibold flex items-center justify-between">
                <span><span x-text="$store.selection.ids.length"></span> ubicaciones seleccionadas en esta página.</span>
                <button @click="$store.selection.fetchAllFilteredIds()" class="hover:underline font-bold">Seleccionar todas las <span x-text="$store.selection.totalFiltered"></span> coincidencias</button>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-4"><input type="checkbox" @click="$store.selection.togglePage({{ $locations->pluck('id')->toJson() }})" :checked="$store.selection.isPageSelected({{ $locations->pluck('id')->toJson() }})" class="rounded border-gray-300"></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ubicación Completa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Almacén</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Sec. Picking</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($locations as $location)
                                <tr class="hover:bg-gray-50">
                                    <td class="p-4"><input type="checkbox" value="{{ $location->id }}" x-model="$store.selection.ids" class="rounded border-gray-300"></td>
                                    <td class="px-6 py-4 font-mono text-sm">{{ $location->code }}</td>
                                    <td class="px-6 py-4 font-semibold">{{ "{$location->aisle}-{$location->rack}-{$location->shelf}-{$location->bin}" }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $location->warehouse->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ $location->translated_type }}</span></td>
                                    <td class="px-6 py-4 text-center font-bold">{{ $location->pick_sequence ?? '-' }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium"><a href="{{ route('wms.locations.edit', $location) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No se encontraron ubicaciones con los filtros aplicados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">{{ $locations->links() }}</div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('selection', {
                ids: [],
                totalFiltered: {{ $locations->total() }},
                showSelectAll: false,

                // Se quita el init() y $watch de aquí
                
                togglePage(pageIds) {
                    if (this.isPageSelected(pageIds)) { this.ids = this.ids.filter(id => !pageIds.includes(id)); } 
                    else { this.ids = [...new Set([...this.ids, ...pageIds])]; }
                },
                
                isPageSelected(pageIds) {
                    if (!pageIds || pageIds.length === 0) return false;
                    return pageIds.every(id => this.ids.includes(id));
                },

                async fetchAllFilteredIds() {
                    const queryParams = new URLSearchParams(window.location.search);
                    const response = await fetch(`{{ route('wms.locations.fetch-filtered-ids') }}?${queryParams.toString()}`);
                    const allIds = await response.json();
                    this.ids = allIds;
                    this.showSelectAll = false;
                    alert(`Se han seleccionado todas las ${allIds.length} ubicaciones que coinciden con el filtro.`);
                }
            });
        });
    </script>
</x-app-layout>
</div>