<div x-data x-init="$watch('$store.selection.ids', (newIds) => {
    const pageIds = {{ $locations->pluck('id')->toJson() }};
    const allOnPageSelected = pageIds.length > 0 && pageIds.every(id => newIds.includes(id));
    $store.selection.showSelectAll = allOnPageSelected && {{ $locations->hasMorePages() ? 'true' : 'false' }};
})">
<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@700;800;900&display=swap');
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        .shadow-soft { box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.1); }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative">
        
        <div class="max-w-[1800px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 border-b border-gray-200 pb-6">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <div class="h-1 w-8 bg-[#ff9c00]"></div>
                        <p class="text-xs font-bold text-[#666666] uppercase tracking-[0.2em]">Mapa del Almacén</p>
                    </div>
                    <h1 class="text-5xl font-raleway font-black text-[#2c3856] mb-1 leading-none">
                        Gestión de <span class="text-[#ff9c00]">Ubicaciones</span>
                    </h1>
                </div>

                <div class="flex flex-wrap gap-3 mt-6 xl:mt-0">
                    <a href="{{ route('wms.dashboard') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-[#666666] font-bold rounded-full shadow-sm hover:shadow-md hover:border-[#2c3856] hover:text-[#2c3856] transition-all">
                        <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
                    </a>                    
                    <form action="{{ route('wms.locations.print-labels') }}" method="POST" target="_blank" class="flex gap-3">
                        @csrf
                        <template x-for="id in $store.selection.ids" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                        <button type="submit" :disabled="$store.selection.ids.length === 0" class="flex items-center gap-2 px-5 py-2.5 bg-[#2c3856] text-white font-bold rounded-full shadow-sm hover:shadow-md hover:bg-[#1a253a] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-print"></i> <span>Imprimir (<span x-text="$store.selection.ids.length"></span>)</span>
                        </button>
                    </form>
                    <a href="{{ route('wms.locations.template') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-[#666666] font-bold rounded-full shadow-sm hover:shadow-md hover:text-[#2c3856] transition-all">
                        <i class="fas fa-download"></i> <span>Plantilla</span>
                    </a>
                    <form action="{{ route('wms.locations.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center">
                        @csrf
                        <label class="cursor-pointer flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-[#666666] font-bold rounded-full shadow-sm hover:shadow-md hover:text-green-600 transition-all">
                            <i class="fas fa-upload"></i> <span>Importar</span>
                            <input type="file" name="file" accept=".csv" required class="hidden" onchange="this.form.submit()">
                        </label>
                    </form>
                    <a href="{{ route('wms.locations.export-csv', request()->query()) }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-[#666666] font-bold rounded-full shadow-sm hover:shadow-md hover:text-blue-600 transition-all">
                        <i class="fas fa-file-csv"></i> <span>Exportar</span>
                    </a>
                    <a href="{{ route('wms.locations.create') }}" class="flex items-center gap-2 px-6 py-2.5 bg-[#2c3856] text-white font-bold rounded-full shadow-lg shadow-[#2c3856]/20 hover:bg-[#1a253a] hover:-translate-y-0.5 transition-all">
                        <i class="fas fa-plus"></i> <span>Nueva</span>
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-xl shadow-sm font-medium flex items-center gap-3">
                    <i class="fas fa-check-circle text-xl"></i>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                 <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl shadow-sm font-medium flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                    <p>{{ session('error') }}</p>
                 </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
                @php
                    $total = $kpis['total_locations'] > 0 ? $kpis['total_locations'] : 1;
                    $cards = [
                        ['l' => 'Total', 'v' => $kpis['total_locations'], 'c' => 'gray', 'i' => 'fa-warehouse'],
                        ['l' => 'Storage', 'v' => $kpis['storage'], 'c' => 'blue', 'i' => 'fa-boxes'],
                        ['l' => 'Picking', 'v' => $kpis['picking'], 'c' => 'sky', 'i' => 'fa-hand-pointer'],
                        ['l' => 'Receiving', 'v' => $kpis['receiving'], 'c' => 'emerald', 'i' => 'fa-truck-loading'],
                        ['l' => 'Shipping', 'v' => $kpis['shipping'], 'c' => 'orange', 'i' => 'fa-truck-moving'],
                        ['l' => 'Quality', 'v' => $kpis['quality_control'], 'c' => 'rose', 'i' => 'fa-check-double'],
                    ];
                @endphp
                @foreach($cards as $c)
                <div class="bg-white p-4 rounded-2xl shadow-soft border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $c['l'] }}</p>
                        <p class="text-2xl font-black text-[#2c3856]">{{ number_format($c['v']) }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-{{ $c['c'] }}-50 flex items-center justify-center text-{{ $c['c'] }}-500">
                        <i class="fas {{ $c['i'] }}"></i>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="bg-white rounded-[2rem] shadow-soft border border-gray-100 overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <form method="GET" action="{{ route('wms.locations.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Almacén</label>
                            <select name="warehouse_id" onchange="this.form.submit()" class="w-full pl-4 pr-4 py-3 rounded-xl border-gray-200 bg-white text-[#2c3856] font-bold focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all cursor-pointer">
                                <option value="">Todos</option>
                                @foreach($filters['warehouses'] as $w)
                                    <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Pasillo</label>
                            <select name="aisle" onchange="this.form.submit()" class="w-full pl-4 pr-4 py-3 rounded-xl border-gray-200 bg-white text-[#2c3856] font-bold focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all cursor-pointer">
                                <option value="">Todos</option>
                                @foreach($filters['aisles'] as $a)
                                    <option value="{{ $a }}" {{ request('aisle') == $a ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Rack</label>
                            <select name="rack" onchange="this.form.submit()" class="w-full pl-4 pr-4 py-3 rounded-xl border-gray-200 bg-white text-[#2c3856] font-bold focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all cursor-pointer">
                                <option value="">Todos</option>
                                @foreach($filters['racks'] as $r)
                                    <option value="{{ $r }}" {{ request('rack') == $r ? 'selected' : '' }}>{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Nivel</label>
                            <select name="shelf" onchange="this.form.submit()" class="w-full pl-4 pr-4 py-3 rounded-xl border-gray-200 bg-white text-[#2c3856] font-bold focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all cursor-pointer">
                                <option value="">Todos</option>
                                @foreach($filters['shelves'] as $s)
                                    <option value="{{ $s }}" {{ request('shelf') == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Tipo</label>
                            <select name="type" onchange="this.form.submit()" class="w-full pl-4 pr-4 py-3 rounded-xl border-gray-200 bg-white text-[#2c3856] font-bold focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all cursor-pointer">
                                <option value="">Todos</option>
                                @foreach(['storage'=>'Almacenamiento','picking'=>'Picking','receiving'=>'Recepción','shipping'=>'Embarque','quality_control'=>'Control Calidad'] as $k => $v)
                                    <option value="{{ $k }}" {{ request('type') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <a href="{{ route('wms.locations.index') }}" class="flex items-center justify-center w-full py-3 bg-[#2c3856] text-white font-bold rounded-xl hover:bg-[#1f2940] transition-all shadow-md">
                                <i class="fas fa-filter mr-2"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div x-show="$store.selection.showSelectAll" style="display: none;" class="mb-6 p-4 bg-[#fff8e6] border border-[#ff9c00]/20 rounded-2xl flex items-center justify-between shadow-sm">
                <span class="text-[#b36b00] font-bold"><span x-text="$store.selection.ids.length"></span> ubicaciones seleccionadas en esta página.</span>
                <button @click="$store.selection.fetchAllFilteredIds()" class="text-[#2c3856] hover:text-[#ff9c00] font-black underline transition-colors">Seleccionar las <span x-text="$store.selection.totalFiltered"></span> coincidencias</button>
            </div>

            <div class="bg-white rounded-[2rem] shadow-soft border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-6 text-left">
                                    <input type="checkbox" @click="$store.selection.togglePage({{ $locations->pluck('id')->toJson() }})" :checked="$store.selection.isPageSelected({{ $locations->pluck('id')->toJson() }})" class="rounded border-gray-300 text-[#2c3856] focus:ring-[#ff9c00]">
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Código</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Ubicación</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Almacén</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-[#666666] uppercase tracking-wider">Secuencia</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-[#666666] uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($locations as $location)
                                <tr class="hover:bg-blue-50/30 transition-colors group">
                                    <td class="p-6">
                                        <input type="checkbox" value="{{ $location->id }}" x-model="$store.selection.ids" class="rounded border-gray-300 text-[#2c3856] focus:ring-[#ff9c00]">
                                    </td>
                                    <td class="px-6 py-4 font-mono text-sm text-[#2c3856] font-bold">{{ $location->code }}</td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('wms.locations.show', $location) }}" class="px-3 py-1 bg-gray-100 rounded-lg text-[#2c3856] font-bold border border-gray-200 hover:bg-[#2c3856] hover:text-white transition-colors">
                                            {{ "{$location->aisle}-{$location->rack}-{$location->shelf}-{$location->bin}" }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-600">{{ $location->warehouse->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                            {{ $location->translated_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-gray-400">{{ $location->pick_sequence ?? '-' }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <a href="{{ route('wms.locations.edit', $location) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-[#ff9c00] hover:border-[#ff9c00] transition-all shadow-sm">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">No se encontraron ubicaciones con los filtros aplicados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                    {{ $locations->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('selection', {
                ids: [],
                totalFiltered: {{ $locations->total() }},
                showSelectAll: false,
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
                    alert(`Se han seleccionado todas las ${allIds.length} ubicaciones.`);
                }
            });
        });
    </script>
</x-app-layout>
</div>