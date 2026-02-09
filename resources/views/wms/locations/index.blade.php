<div x-data="{ importModalOpen: false }" x-init="$watch('$store.selection.ids', (newIds) => {
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
        
        .btn-toolbar {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0.6rem 1.2rem; border-radius: 1rem; font-weight: 700; font-size: 0.75rem;
            text-transform: uppercase; letter-spacing: 0.05em; transition: all 0.2s ease;
            white-space: nowrap; height: 42px; cursor: pointer;
        }
        .btn-white { background: white; border: 1px solid #e5e7eb; color: #666; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .btn-white:hover { border-color: #2c3856; color: #2c3856; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        
        .btn-primary { background: #2c3856; color: white; border: 1px solid #2c3856; box-shadow: 0 4px 10px rgba(44, 56, 86, 0.2); }
        .btn-primary:hover { background: #1a253a; transform: translateY(-1px); box-shadow: 0 6px 15px rgba(44, 56, 86, 0.3); }

        .btn-nexus { 
            background: #2c3856; color: white; border-radius: 1rem; font-weight: 700;
            transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(44, 56, 86, 0.2); }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative">
        
        <div class="max-w-[1800px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end mb-10 border-b border-gray-200 pb-6 gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <div class="h-1 w-8 bg-[#ff9c00]"></div>
                        <p class="text-xs font-bold text-[#666666] uppercase tracking-[0.2em]">Mapa del Almacén</p>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-raleway font-black text-[#2c3856] mb-1 leading-none">
                        Gestión de <span class="text-[#ff9c00]">Ubicaciones</span>
                    </h1>
                </div>

                <div class="flex flex-wrap gap-3 items-center w-full xl:w-auto">
                    
                    <a href="{{ route('wms.dashboard') }}" class="btn-toolbar btn-white text-gray-400 hover:text-gray-600" title="Volver al Dashboard">
                        <i class="fas fa-arrow-left"></i>
                    </a>

                    <div class="h-8 w-px bg-gray-300 mx-1 hidden md:block"></div>

                    @if(Auth::user()->hasFfPermission('wms.locations.manage'))
                    <button @click="importModalOpen = true" class="btn-toolbar btn-white text-blue-600 border-blue-100 hover:border-blue-300">
                        <i class="fas fa-file-import mr-2"></i> Importar Masivamente
                    </button>
                    @endif

                    @if(Auth::user()->hasFfPermission('wms.locations.view'))
                    <a href="{{ route('wms.locations.export-csv', request()->query()) }}" class="btn-toolbar btn-white" title="Exportar Todo">
                        <i class="fas fa-download mr-2 text-gray-500"></i> Exportar
                    </a>
                    @endif

                    <div class="h-8 w-px bg-gray-300 mx-1 hidden md:block"></div>

                    @if(Auth::user()->hasFfPermission('wms.locations.print'))
                    <form action="{{ route('wms.locations.print-labels') }}" method="POST" target="_blank" class="m-0">
                        @csrf
                        <template x-for="id in $store.selection.ids" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                        <button type="submit" :disabled="$store.selection.ids.length === 0" class="btn-toolbar btn-white disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-print mr-2 text-[#2c3856]"></i> 
                            Labels <span x-show="$store.selection.ids.length > 0" class="ml-1 bg-[#2c3856] text-white text-[9px] px-1.5 py-0.5 rounded-full" x-text="$store.selection.ids.length"></span>
                        </button>
                    </form>
                    @endif

                    @if(Auth::user()->hasFfPermission('wms.locations.manage'))
                    <a href="{{ route('wms.locations.create') }}" class="btn-toolbar btn-primary">
                        <i class="fas fa-plus mr-2"></i> Nueva Ubicación
                    </a>
                    @endif
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

            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
                @php
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
                <div class="bg-white p-4 rounded-2xl shadow-soft border border-gray-100 flex flex-col justify-between h-24 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-{{ $c['c'] }}-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10 text-{{ $c['c'] }}-500 mb-2 text-lg"><i class="fas {{ $c['i'] }}"></i></div>
                    <div class="relative z-10">
                        <p class="text-2xl font-black text-[#2c3856] leading-none">{{ number_format($c['v']) }}</p>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mt-1">{{ $c['l'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="bg-white rounded-[2rem] shadow-soft border border-gray-100 overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <form method="GET" action="{{ route('wms.locations.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Almacén</label>
                            <select name="warehouse_id" onchange="this.form.submit()" class="w-full pl-4 pr-4 py-2.5 rounded-xl border-gray-200 bg-white text-[#2c3856] font-bold focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all cursor-pointer text-sm">
                                <option value="">Todos</option>
                                <option value="">Todos</option>
                                @foreach($filters['warehouses'] as $w)
                                    <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Pasillo</label>
                            <select name="aisle" onchange="this.form.submit()" class="w-full pl-4 pr-4 py-2.5 rounded-xl border-gray-200 bg-white text-[#2c3856] font-bold focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all cursor-pointer text-sm">
                                <option value="">Todos</option>
                                @foreach($filters['aisles'] as $a)
                                    <option value="{{ $a }}" {{ request('aisle') == $a ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Rack</label>
                            <select name="rack" onchange="this.form.submit()" class="w-full pl-4 pr-4 py-2.5 rounded-xl border-gray-200 bg-white text-[#2c3856] font-bold focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all cursor-pointer text-sm">
                                <option value="">Todos</option>
                                @foreach($filters['racks'] as $r)
                                    <option value="{{ $r }}" {{ request('rack') == $r ? 'selected' : '' }}>{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Nivel</label>
                            <select name="shelf" onchange="this.form.submit()" class="w-full pl-4 pr-4 py-2.5 rounded-xl border-gray-200 bg-white text-[#2c3856] font-bold focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all cursor-pointer text-sm">
                                <option value="">Todos</option>
                                @foreach($filters['shelves'] as $s)
                                    <option value="{{ $s }}" {{ request('shelf') == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Tipo</label>
                            <select name="type" onchange="this.form.submit()" class="w-full pl-4 pr-4 py-2.5 rounded-xl border-gray-200 bg-white text-[#2c3856] font-bold focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all cursor-pointer text-sm">
                                <option value="">Todos</option>
                                @foreach(['storage'=>'Almacenamiento','picking'=>'Picking','receiving'=>'Recepción','shipping'=>'Embarque','quality_control'=>'Control Calidad'] as $k => $v)
                                    <option value="{{ $k }}" {{ request('type') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <a href="{{ route('wms.locations.index') }}" class="flex items-center justify-center w-full py-2.5 bg-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-300 transition-all text-sm">
                                <i class="fas fa-times mr-2"></i> Limpiar
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
                                        @if(Auth::user()->hasFfPermission('wms.locations.manage'))
                                        <a href="{{ route('wms.locations.edit', $location) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-[#ff9c00] hover:border-[#ff9c00] transition-all shadow-sm">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        @endif
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

        <div x-show="importModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#2c3856]/80 backdrop-blur-sm" style="display: none;" x-cloak>
            <div @click.away="importModalOpen = false" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-2xl overflow-hidden" x-show="importModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                
                <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div>
                        <h2 class="text-2xl font-raleway font-black text-[#2c3856]">Importar Ubicaciones</h2>
                        <p class="text-xs text-gray-500 mt-1">Sigue las instrucciones para cargar masivamente.</p>
                    </div>
                    <button @click="importModalOpen = false" class="w-10 h-10 rounded-full bg-white text-gray-400 hover:text-red-500 shadow-sm flex items-center justify-center transition-colors text-xl">&times;</button>
                </div>

                <div class="p-8 overflow-y-auto max-h-[70vh]">
                    
                    <div class="mb-8">
                        <h3 class="text-sm font-bold text-[#2c3856] uppercase tracking-widest mb-4">1. Descarga y Llena la Plantilla</h3>
                        <p class="text-xs text-gray-500 mb-4 leading-relaxed">
                            Descarga el archivo CSV y llénalo respetando las columnas. <br>
                            <span class="text-red-500 font-bold">Importante:</span> La columna <code>tipo</code> debe contener uno de los valores permitidos (en minúsculas).
                        </p>

                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-4">
                            <p class="text-[10px] font-bold text-blue-400 uppercase mb-2">Tipos de Ubicación Válidos</p>
                            <div class="flex flex-wrap gap-2">
                                <span class="bg-white px-2 py-1 rounded text-[10px] font-mono text-blue-700 border border-blue-200">storage</span>
                                <span class="bg-white px-2 py-1 rounded text-[10px] font-mono text-blue-700 border border-blue-200">picking</span>
                                <span class="bg-white px-2 py-1 rounded text-[10px] font-mono text-blue-700 border border-blue-200">receiving</span>
                                <span class="bg-white px-2 py-1 rounded text-[10px] font-mono text-blue-700 border border-blue-200">shipping</span>
                                <span class="bg-white px-2 py-1 rounded text-[10px] font-mono text-blue-700 border border-blue-200">quality_control</span>
                            </div>
                        </div>

                        <a href="{{ route('wms.locations.template') }}" class="btn-ghost w-full py-3 text-xs uppercase tracking-widest text-center flex items-center justify-center border-green-200 text-green-600 hover:bg-green-50">
                            <i class="fas fa-file-csv mr-2 text-lg"></i> Descargar Plantilla CSV
                        </a>
                    </div>

                    <div class="border-t border-gray-100 my-6"></div>

                    <div x-data="{ fileName: null, isDragging: false }">
                        <h3 class="text-sm font-bold text-[#2c3856] uppercase tracking-widest mb-4">2. Sube tu Archivo</h3>
                        <form action="{{ route('wms.locations.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="relative group">
                                <input type="file" name="file" accept=".csv" required 
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                       @change="fileName = $event.target.files[0].name"
                                       @dragover="isDragging = true"
                                       @dragleave="isDragging = false"
                                       @drop="isDragging = false">
                                
                                <div class="border-2 border-dashed rounded-2xl p-8 text-center transition-all duration-300"
                                     :class="isDragging || fileName ? 'border-[#ff9c00] bg-orange-50/30' : 'border-gray-300 bg-white group-hover:border-gray-400'">
                                    
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 transition-colors"
                                         :class="isDragging || fileName ? 'bg-orange-100 text-[#ff9c00]' : 'bg-gray-100 text-gray-400'">
                                        <template x-if="fileName">
                                            <i class="fas fa-file-csv text-xl"></i>
                                        </template>
                                        <template x-if="!fileName">
                                            <i class="fas fa-cloud-upload-alt text-xl"></i>
                                        </template>
                                    </div>
                                    
                                    <template x-if="!fileName">
                                        <div>
                                            <p class="text-sm font-bold text-gray-600">Haz clic o arrastra tu archivo aquí</p>
                                            <p class="text-xs text-gray-400 mt-1">Solo archivos .CSV</p>
                                        </div>
                                    </template>
                                    
                                    <template x-if="fileName">
                                        <div>
                                            <p class="text-sm font-bold text-[#2c3856]" x-text="fileName"></p>
                                            <p class="text-xs text-green-600 font-bold mt-1">¡Archivo seleccionado!</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" @click="importModalOpen = false" class="btn-ghost px-6 py-3 text-xs uppercase tracking-widest">Cancelar</button>
                                <button type="submit" class="btn-nexus px-8 py-3 text-xs uppercase tracking-widest shadow-lg shadow-[#2c3856]/20" :disabled="!fileName" :class="!fileName ? 'opacity-50 cursor-not-allowed' : ''">
                                    <i class="fas fa-upload mr-2"></i> Procesar Importación
                                </button>
                            </div>
                        </form>
                    </div>

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