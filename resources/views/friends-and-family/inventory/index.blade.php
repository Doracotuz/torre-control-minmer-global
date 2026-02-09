@php
    $areas = [];
    if(Auth::user()->isSuperAdmin()) {
        $areas = \App\Models\Area::orderBy('name')->get();
    }

    $currentWarehouseId = request('warehouse_id'); 
    $currentWarehouseName = $warehouses->where('id', $currentWarehouseId)->first()->description ?? 'Global';
    
    $currentQualityId = request('quality_id');
    $currentQualityName = $qualities->where('id', $currentQualityId)->first()->name ?? 'Mixta / Global';
    
    $jsAllWarehouses = (isset($allWarehouses) && count($allWarehouses) > 0) ? $allWarehouses : $warehouses;
    $jsAllAreas = isset($allAreas) ? $allAreas : [];
@endphp

<x-app-layout>
    <x-slot name="header"></x-slot>
    <div x-data="inventoryManager()" 
         x-init="init(@js($products), @js($jsAllWarehouses), @js($jsAllAreas), @js($qualities))" 
         class="font-sans text-slate-600">

        <x-slot name="header">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h2 class="font-black text-3xl text-[#2c3856] leading-tight font-[Montserrat]">
                        <i class="fas fa-boxes mr-3 text-[#ff9c00]"></i> Inventario
                    </h2>
                    <p class="text-base text-slate-500 font-medium mt-1">Gestión integral de stock y movimientos</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-4">
                    @if(Auth::user()->hasFfPermission('inventory.log'))
                    <a href="{{ route('ff.inventory.log') }}" 
                    class="inline-flex items-center px-6 py-3 bg-white text-[#2c3856] border-2 border-slate-200 rounded-2xl text-sm font-bold uppercase tracking-wider shadow-sm hover:bg-slate-50 hover:border-[#ff9c00] hover:text-[#ff9c00] transition-all duration-300 transform hover:-translate-y-0.5">
                        <i class="fas fa-history mr-2"></i> Historial
                    </a>
                    @endif

                    <a href="{{ route('ff.dashboard.index') }}" 
                    class="inline-flex items-center px-6 py-3 bg-[#2c3856] text-white rounded-2xl text-sm font-bold uppercase tracking-wider shadow-lg hover:bg-[#1e273d] hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Dashboard
                    </a>
                </div>
            </div>
        </x-slot>

        <div class="py-8 bg-[#E8ECF7] min-h-screen">
            
            <div class="mb-10 grid grid-cols-1 xl:grid-cols-12 gap-6 items-stretch">

                <div class="xl:col-span-5 flex flex-col md:flex-row gap-4 h-full">
                    
                    @if(Auth::user()->hasFfPermission('inventory.backorders'))
                    <a href="{{ route('ff.inventory.backorders') }}" 
                    class="relative overflow-hidden w-full md:w-3/5 rounded-[2rem] bg-gradient-to-br from-[#2c3856] to-[#1e273d] p-6 text-white shadow-xl shadow-blue-900/20 group hover:shadow-2xl hover:shadow-blue-900/30 transition-all duration-300 transform hover:-translate-y-1">
                        
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/5 rounded-full blur-2xl group-hover:bg-[#ff9c00]/20 transition-colors duration-500"></div>
                        
                        <div class="relative z-10 flex flex-col h-full justify-between min-h-[140px]">
                            <div class="flex justify-between items-start">
                                <div class="bg-white/10 p-3 rounded-2xl backdrop-blur-sm border border-white/5 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-box-open text-2xl text-[#ff9c00]"></i>
                                </div>
                                <div class="bg-[#ff9c00] text-[#2c3856] text-[10px] font-black px-2 py-1 rounded-lg uppercase tracking-wider">
                                    Prioridad
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-white/60 text-xs font-bold uppercase tracking-widest mb-1">Gestión de Pedidos</h3>
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl font-black tracking-tight group-hover:text-[#ff9c00] transition-colors">Surtir Backorders</span>
                                    <i class="fas fa-arrow-right opacity-0 -translate-x-2 group-hover:translate-x-0 group-hover:opacity-100 transition-all duration-300 text-[#ff9c00]"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                    @endif

                    <a href="{{ route('ff.inventory.backorder_relations') }}" 
                    class="relative w-full md:w-2/5 rounded-[2rem] bg-white p-6 shadow-sm border border-slate-100 group hover:border-rose-200 transition-all duration-300 flex flex-col justify-between hover:shadow-lg hover:shadow-rose-500/10">
                        <div class="bg-rose-50 w-12 h-12 rounded-2xl flex items-center justify-center text-rose-500 mb-4 group-hover:rotate-12 transition-transform duration-300">
                            <i class="fas fa-file-invoice-dollar text-xl"></i>
                        </div>
                        <div>
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Finanzas</p>
                            <p class="text-lg font-black text-slate-700 leading-tight mt-1 group-hover:text-rose-600 transition-colors">Pasivos y<br>Deuda</p>
                        </div>
                    </a>
                </div>

                <div class="xl:col-span-7 grid grid-cols-1 md:grid-cols-3 gap-4 h-full">

                    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col justify-center relative overflow-hidden group">
                        <div class="absolute -right-6 -bottom-6 text-slate-50 opacity-50 group-hover:opacity-100 group-hover:scale-110 transition-all duration-500">
                            <i class="fas fa-barcode text-8xl"></i>
                        </div>
                        <p class="text-slate-400 text-xs font-black uppercase tracking-widest z-10">Total SKUs</p>
                        <div class="flex items-baseline gap-1 mt-2 z-10">
                            <span class="text-4xl font-black text-[#2c3856]" x-text="filteredProducts.length">0</span>
                            <span class="text-xs font-bold text-slate-400">visibles</span>
                        </div>
                        <div class="w-full bg-slate-100 h-1.5 mt-4 rounded-full overflow-hidden z-10">
                            <div class="bg-blue-500 h-full rounded-full w-2/3 opacity-80 group-hover:w-full transition-all duration-1000"></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col justify-center relative overflow-hidden group">
                        <div class="absolute -right-6 -bottom-6 text-slate-50 opacity-50 group-hover:opacity-100 group-hover:scale-110 transition-all duration-500">
                            <i class="fas fa-cubes text-8xl"></i>
                        </div>
                        <p class="text-slate-400 text-xs font-black uppercase tracking-widest z-10">Inventario Físico</p>
                        <div class="flex items-baseline gap-1 mt-2 z-10">
                            <span class="text-4xl font-black text-[#2c3856]" x-text="totalStock.toLocaleString()">0</span>
                            <span class="text-xs font-bold text-slate-400">pzas</span>
                        </div>
                        <div class="w-full bg-slate-100 h-1.5 mt-4 rounded-full overflow-hidden z-10">
                            <div class="bg-emerald-500 h-full rounded-full w-1/2 opacity-80 group-hover:w-3/4 transition-all duration-1000"></div>
                        </div>
                    </div>

                    <div class="bg-[#2c3856] rounded-[2rem] p-6 shadow-xl shadow-slate-400/20 flex flex-col justify-center relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-tr from-[#2c3856] to-[#3a4b70] opacity-100"></div>
                        <div class="absolute top-0 right-0 w-32 h-32 bg-[#ff9c00] rounded-full blur-[60px] opacity-10 group-hover:opacity-20 transition-opacity duration-500"></div>
                        
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-1">
                                <p class="text-white/60 text-xs font-bold uppercase tracking-widest">Valor Total</p>
                                <i class="fas fa-chart-line text-[#ff9c00] animate-pulse"></i>
                            </div>
                            
                            <div class="mt-2">
                                <span class="text-3xl lg:text-2xl xl:text-3xl font-black text-white font-mono tracking-tight" x-text="formatMoney(totalValue)">$0.00</span>
                            </div>
                            
                            <div class="mt-4 flex items-center gap-2 text-[10px] text-white/40 bg-white/5 rounded-lg px-2 py-1 w-fit">
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
                                {{ $currentWarehouseName }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                    class="bg-emerald-100 border-l-8 border-emerald-500 text-emerald-800 px-6 py-5 rounded-2xl mb-8 shadow-md flex items-center justify-between" role="alert">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-check-circle text-2xl"></i>
                        <span class="font-bold text-lg">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 transition-colors"><i class="fas fa-times text-xl"></i></button>
                </div>
            @endif

            @if (session('import_errors'))
                <div class="bg-red-50 border-l-8 border-red-500 text-red-800 px-8 py-6 rounded-2xl mb-8 shadow-md">
                    <div class="flex items-center font-black text-xl mb-3">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        {{ session('error_summary', 'Errores en la importación') }}
                    </div>
                    <ul class="list-disc list-inside text-sm space-y-2 opacity-90 pl-2 font-medium">
                        @foreach (session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-[2rem] shadow-xl border border-slate-100 overflow-hidden">
                
                <div class="p-8 border-b border-slate-100 flex flex-col lg:flex-row gap-6 justify-between items-center bg-white">
                    
                    <div class="relative w-full lg:w-1/6 group">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-300 text-lg group-focus-within:text-[#ff9c00] transition-colors duration-300"></i>
                        </div>
                        <input type="text" x-model="filter" 
                            class="block w-full pl-12 pr-5 py-4 bg-slate-50 border-2 border-transparent text-slate-700 rounded-2xl focus:ring-0 focus:border-[#ff9c00] focus:bg-white transition-all duration-300 placeholder-slate-400 font-bold text-xs shadow-inner" 
                            placeholder="Buscar por SKU, nombre, UPC...">
                    </div>

                    <div class="flex flex-wrap gap-3 w-full lg:w-auto justify-end items-center">
                        
                        @if(Auth::user()->isSuperAdmin())
                        <div class="relative">
                            <select name="area_id" onchange="const params = new URLSearchParams(window.location.search); params.set('area_id', this.value); window.location.search = params.toString();" 
                                    class="pl-4 pr-10 py-3 bg-white border-2 border-slate-200 rounded-xl text-sm font-bold text-slate-600 focus:ring-0 focus:border-[#ff9c00] cursor-pointer hover:border-slate-300 transition-all uppercase tracking-wide shadow-sm appearance-none">
                                <option value="">Todas las Áreas</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400"><i class="fas fa-chevron-down text-xs"></i></div>
                        </div>
                        @endif

                        <div class="relative">
                            <select name="warehouse_id" onchange="const params = new URLSearchParams(window.location.search); params.set('warehouse_id', this.value); window.location.search = params.toString();"
                                    class="pl-4 pr-10 py-3 bg-white border-2 border-slate-200 rounded-xl text-sm font-bold text-slate-600 focus:ring-0 focus:border-[#ff9c00] cursor-pointer hover:border-slate-300 transition-all uppercase tracking-wide shadow-sm appearance-none">
                                <option value="">Stock Global</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->description }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400"><i class="fas fa-warehouse text-xs"></i></div>
                        </div>

                        <div class="relative">
                            <select name="quality_id" onchange="const params = new URLSearchParams(window.location.search); params.set('quality_id', this.value); window.location.search = params.toString();"
                                    class="pl-4 pr-10 py-3 bg-white border-2 border-slate-200 rounded-xl text-sm font-bold text-slate-600 focus:ring-0 focus:border-[#ff9c00] cursor-pointer hover:border-slate-300 transition-all uppercase tracking-wide shadow-sm appearance-none">
                                <option value="">Todas las Calidades</option>
                                @foreach($qualities as $q)
                                    <option value="{{ $q->id }}" {{ request('quality_id') == $q->id ? 'selected' : '' }}>{{ $q->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400"><i class="fas fa-medal text-xs"></i></div>
                        </div>

                        <select x-model="filterBrand" class="pl-4 pr-10 py-3 bg-white border-2 border-slate-200 rounded-xl text-sm font-bold text-slate-600 focus:ring-0 focus:border-[#ff9c00] cursor-pointer hover:border-slate-300 transition-all uppercase tracking-wide shadow-sm">
                            <option value="">Todas las Marcas</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand }}">{{ $brand }}</option>
                            @endforeach
                        </select>

                        <select x-model="filterType" class="pl-4 pr-10 py-3 bg-white border-2 border-slate-200 rounded-xl text-sm font-bold text-slate-600 focus:ring-0 focus:border-[#ff9c00] cursor-pointer hover:border-slate-300 transition-all uppercase tracking-wide shadow-sm">
                            <option value="">Todos los Tipos</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>

                        <label class="flex items-center gap-2 px-4 py-3 bg-white border-2 border-slate-200 rounded-xl cursor-pointer hover:border-slate-300 transition-all select-none shadow-sm h-[50px]">
                            <input type="checkbox" x-model="filterStock" class="w-5 h-5 text-[#ff9c00] border-gray-300 rounded focus:ring-[#ff9c00] focus:ring-offset-0">
                            <span class="text-sm font-bold text-slate-600 uppercase tracking-wide">Solo con Stock</span>
                        </label>                        
                        
                        <div class="h-10 w-px bg-slate-200 mx-2 hidden md:block"></div>

                        @if(Auth::user()->hasFfPermission('inventory.import'))
                        <button @click="openImportModal()" 
                                class="w-12 h-12 flex items-center justify-center text-slate-400 hover:text-[#2c3856] hover:bg-slate-100 rounded-xl transition-all border border-transparent hover:border-slate-200" 
                                title="Importar CSV">
                            <i class="fas fa-file-upload text-xl"></i>
                        </button>
                        @endif
                        
                        @if(Auth::user()->hasFfPermission('inventory.export'))
                        <button @click="exportFilteredCsv()" 
                                class="w-12 h-12 flex items-center justify-center text-slate-400 hover:text-[#2c3856] hover:bg-slate-100 rounded-xl transition-all border border-transparent hover:border-slate-200" 
                                title="Exportar CSV">
                            <i class="fas fa-file-download text-xl"></i>
                        </button>
                        @endif

                        <button @click="resetFilters()" x-show="filter || filterBrand || filterType || filterStock || '{{ request('quality_id') }}'" x-transition 
                                class="ml-2 px-4 py-2 text-rose-500 bg-rose-50 hover:bg-rose-100 rounded-xl text-xs font-black transition-all shadow-sm border border-rose-100" 
                                title="Limpiar Filtros">
                            <i class="fas fa-times mr-1"></i> LIMPIAR
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest font-[Montserrat]">Producto</th>
                                <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest font-[Montserrat]">Almacén</th>
                                <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest font-[Montserrat]">Calidad</th>
                                <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest font-[Montserrat]">Categoría</th>
                                <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-right font-[Montserrat]">Precio</th>
                                <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-center font-[Montserrat]">Stock Actual</th>
                                @if(Auth::user()->isSuperAdmin() || Auth::user()->hasFfPermission('inventory.move'))
                                <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-right font-[Montserrat]">Acciones Rápidas</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <template x-for="row in paginatedProducts" :key="row.row_key">
                                <tr class="hover:bg-blue-50/30 transition-colors duration-200 group">
                                    
                                    <td class="px-8 py-5">
                                        <div class="flex items-center">
                                            <div class="h-16 w-16 flex-shrink-0 rounded-xl border border-slate-100 overflow-hidden p-2 bg-white shadow-sm group-hover:scale-110 transition-transform duration-300">
                                                <img class="h-full w-full object-contain mix-blend-multiply" :src="row.photo_url" :alt="row.sku">
                                            </div>
                                            <div class="ml-6">
                                                <div class="text-base font-bold text-[#2c3856]" x-text="row.description"></div>
                                                <div class="flex items-center gap-3 mt-1.5">
                                                    <div class="text-xs text-slate-500 font-mono bg-slate-100 inline-block px-2 py-1 rounded-md border border-slate-200 font-bold" x-text="row.sku"></div>
                                                    <div x-show="row.upc" class="text-xs text-slate-400 font-mono" x-text="'UPC: ' + row.upc"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-8 py-5">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border border-slate-200" 
                                            :class="'{{ $currentWarehouseId }}' ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-gray-50 text-gray-500'">
                                            <i class="fas fa-warehouse mr-1.5 text-[10px]"></i>
                                            {{ $currentWarehouseName }}
                                        </span>
                                    </td>

                                    <td class="px-8 py-5">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border" 
                                            :class="row.display_quality !== 'Estándar' && row.display_quality !== 'Sin Stock' ? 'bg-purple-50 text-purple-700 border-purple-100' : 'bg-slate-50 text-slate-500 border-slate-200'">
                                            <i class="fas fa-medal mr-1.5 text-[10px]"></i>
                                            <span x-text="row.display_quality"></span>
                                        </span>
                                    </td>

                                    <td class="px-8 py-5">
                                        <div class="flex flex-col gap-1.5">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 w-fit uppercase tracking-wide border border-slate-200" 
                                                x-text="row.brand || 'N/A'"></span>
                                            <span class="text-xs text-slate-400 font-medium pl-1" x-text="row.type"></span>
                                        </div>
                                    </td>

                                    <td class="px-8 py-5 text-right">
                                        <div class="text-base font-black text-[#2c3856] font-mono tracking-tight" x-text="formatMoney(row.unit_price)"></div>
                                    </td>

                                    <td class="px-8 py-5 text-center">
                                        <div class="inline-flex items-center justify-center px-5 py-2 rounded-xl text-sm font-black shadow-sm border transition-all duration-300 min-w-[80px]"
                                            :class="{
                                                'bg-emerald-50 text-emerald-700 border-emerald-200': row.display_stock > 5,
                                                'bg-amber-50 text-amber-700 border-amber-200': row.display_stock > 0 && row.display_stock <= 5,
                                                'bg-red-50 text-red-700 border-red-200': row.display_stock <= 0
                                            }">
                                            <span x-text="row.display_stock"></span>
                                            <span class="text-[10px] ml-1.5 opacity-70 uppercase">pzas</span>
                                        </div>
                                    </td>

                                    @if(Auth::user()->isSuperAdmin() || Auth::user()->hasFfPermission('inventory.move'))
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex justify-end gap-3 opacity-80 group-hover:opacity-100 transition-opacity duration-200">
                                            <button @click="openModal(row, 'add', row.display_quality_id)" 
                                                    class="h-10 w-10 flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 hover:bg-emerald-500 hover:text-white hover:border-emerald-500 transition-all shadow-sm transform hover:-translate-y-1" 
                                                    title="Registrar Entrada">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button @click="openModal(row, 'remove', row.display_quality_id)" 
                                                    class="h-10 w-10 flex items-center justify-center rounded-xl bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-500 hover:text-white hover:border-rose-500 transition-all shadow-sm transform hover:-translate-y-1" 
                                                    title="Registrar Salida">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                            </template>
                            
                            <template x-if="flattenedRows.length === 0">
                                <tr>
                                    <td colspan="7" class="px-8 py-24 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6 shadow-sm border border-slate-100">
                                                <i class="fas fa-search text-slate-300 text-4xl"></i>
                                            </div>
                                            <h3 class="text-xl font-bold text-[#2c3856] mb-2">No encontramos coincidencias</h3>
                                            <p class="text-sm text-slate-400">Intenta ajustar los filtros o el término de búsqueda.</p>
                                            <button @click="resetFilters()" class="mt-6 text-[#ff9c00] text-sm font-bold hover:underline uppercase tracking-wide">
                                                Limpiar todos los filtros
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                
                <div class="bg-slate-50 px-8 py-4 border-t border-slate-200 flex items-center justify-between text-xs font-black text-slate-400 uppercase tracking-widest">
                    <span x-text="`Mostrando ${filteredProducts.length} productos`"></span>
                    <span x-text="`Stock Visible: ${totalStock.toLocaleString()} unidades`"></span>
                </div>
            </div>
        </div>

        <div x-show="isModalOpen" 
            class="fixed inset-0 z-50 overflow-y-auto" 
            style="display: none;" x-cloak>
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:block sm:p-0">
                
                <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-[#2c3856] opacity-70 backdrop-blur-md"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="isModalOpen" 
                    @click.outside="closeModal()"
                    x-transition:enter="ease-out duration-300" 
                    class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-100">
                    
                    <form @submit.prevent="submitMovement">
                        <div class="bg-white px-8 pt-8 pb-6 relative">
                            <button type="button" @click="closeModal()" class="absolute top-6 right-6 w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100 transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                            
                            <div class="flex flex-col items-center text-center mb-8">
                                <div class="h-20 w-20 rounded-2xl flex items-center justify-center shadow-md mb-5 transition-colors duration-300 border-4 border-white ring-4"
                                    :class="form.type === 'add' ? 'bg-emerald-50 text-emerald-500 ring-emerald-50' : 'bg-rose-50 text-rose-500 ring-rose-50'">
                                    <i class="fas fa-2x" :class="form.type === 'add' ? 'fa-plus' : 'fa-minus'"></i>
                                </div>
                                <h3 class="text-2xl font-black text-[#2c3856]" x-text="form.type === 'add' ? 'Entrada de Inventario' : 'Salida de Inventario'"></h3>
                                <p class="text-sm font-bold text-slate-400 mt-2 uppercase tracking-wide px-4" x-text="form.product_name"></p>
                            </div>

                            <div class="space-y-6">
                                
                                @if(Auth::user()->isSuperAdmin())
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Área (Cliente)</label>
                                    <div class="relative">
                                        <select x-model="form.area_id" 
                                                @change="updateResources()"
                                                class="block w-full px-5 py-4 bg-slate-50 border-2 border-transparent focus:border-[#2c3856] rounded-2xl text-slate-700 focus:bg-white transition-all text-base font-bold appearance-none cursor-pointer">
                                            <template x-for="area in allAreas" :key="area.id">
                                                <option :value="area.id" x-text="area.name"></option>
                                            </template>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-5 pointer-events-none text-slate-400">
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Almacén Afectado</label>
                                    <div class="relative">
                                        <select x-model="form.warehouse_id" required 
                                                class="block w-full px-5 py-4 bg-slate-50 border-2 border-transparent focus:border-[#2c3856] rounded-2xl text-slate-700 focus:bg-white transition-all text-base font-bold appearance-none cursor-pointer">
                                            <option value="">Seleccione un Almacén</option>
                                            <template x-for="wh in availableWarehouses" :key="wh.id">
                                                <option :value="wh.id" x-text="wh.description + ' (' + wh.code + ')'"></option>
                                            </template>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-5 pointer-events-none text-slate-400">
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </div>
                                    </div>
                                    <div x-show="form.warehouse_id" class="mt-2 text-right">
                                        <span class="text-xs font-bold text-[#2c3856] bg-blue-50 px-2 py-1 rounded-lg">
                                            Existencia actual: <span x-text="currentWarehouseStock"></span>
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Calidad</label>
                                    <div class="relative">
                                        <select x-model="form.ff_quality_id"
                                                class="block w-full px-5 py-4 bg-slate-50 border-2 border-transparent focus:border-[#2c3856] rounded-2xl text-slate-700 focus:bg-white transition-all text-base font-bold appearance-none cursor-pointer">
                                            <option value="">Estándar / Sin Especificar</option>
                                            <template x-for="quality in availableQualities" :key="quality.id">
                                                <option :value="quality.id" x-text="quality.name"></option>
                                            </template>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-5 pointer-events-none text-slate-400">
                                            <i class="fas fa-medal text-xs"></i>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Cantidad a mover</label>
                                    <input type="number" min="1" x-model.number="form.quantity_raw" required 
                                        class="block w-full text-center py-4 bg-slate-50 border-2 border-transparent focus:border-[#2c3856] rounded-2xl text-[#2c3856] focus:bg-white transition-all font-black text-3xl placeholder-slate-300" 
                                        placeholder="0">
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Motivo / Referencia</label>
                                    <input type="text" x-model="form.reason" required 
                                        class="block w-full px-5 py-4 bg-slate-50 border-2 border-transparent focus:border-[#2c3856] rounded-2xl text-slate-700 focus:bg-white transition-all text-base font-medium" 
                                        placeholder="Ej. Compra PO-123, Merma, Ajuste...">
                                </div>
                            </div>

                            <div x-show="errorMessage" x-transition class="mt-6 p-4 rounded-xl bg-rose-50 text-rose-600 text-sm font-bold border border-rose-100 flex items-center justify-center">
                                <i class="fas fa-exclamation-circle mr-2 text-lg"></i>
                                <span x-text="errorMessage"></span>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-8 py-6 flex gap-4 border-t border-slate-100">
                            <button type="button" @click="closeModal()" class="flex-1 py-3.5 rounded-xl border-2 border-slate-200 bg-white text-sm font-bold text-slate-600 hover:bg-slate-100 transition-all uppercase tracking-wide">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    :disabled="isSaving"
                                    class="flex-1 py-3.5 rounded-xl border-2 border-transparent shadow-xl text-sm font-bold text-white transition-all transform hover:-translate-y-1 uppercase flex items-center justify-center tracking-wide"
                                    :class="form.type === 'add' ? 'bg-[#2c3856] hover:bg-[#1e273d]' : 'bg-rose-500 hover:bg-rose-600'">
                                <span x-text="isSaving ? 'Procesando...' : 'Confirmar'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="isImportModalOpen" 
            class="fixed inset-0 z-50 overflow-y-auto" 
            style="display: none;" x-cloak>
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:block sm:p-0">
                
                <div x-show="isImportModalOpen" x-transition.opacity class="fixed inset-0 bg-[#2c3856] opacity-70 backdrop-blur-md" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="isImportModalOpen" 
                    @click.outside="closeImportModal()"
                    x-transition:enter="ease-out duration-300"
                    class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-slate-100">
                    
                    <form action="{{ route('ff.inventory.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        @if(Auth::user()->isSuperAdmin())
                            <div class="bg-blue-50/50 px-8 py-4 border-b border-slate-100">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">
                                    ¿A qué Área (Cliente) pertenece esta carga?
                                </label>
                                <div class="relative">
                                    <select name="area_id" x-model="filterArea"
                                            class="block w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl text-slate-700 focus:border-[#ff9c00] focus:ring-0 transition-all font-bold appearance-none cursor-pointer">
                                        <option value="">-- Detectar Automáticamente --</option>
                                        <template x-for="area in allAreas" :key="area.id">
                                            <option :value="area.id" x-text="area.name"></option>
                                        </template>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                                <p class="text-[10px] text-blue-600 mt-2 font-medium">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Esto validará que los productos y almacenes del CSV pertenezcan al cliente seleccionado.
                                </p>
                            </div>
                        @endif
                        <div class="bg-white px-8 pt-6 pb-6 relative">
                            <button type="button" @click="closeImportModal()" class="absolute top-6 right-6 w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                <i class="fas fa-times"></i>
                            </button>

                            <div class="flex items-center gap-4 mb-8">
                                <div class="bg-blue-50 p-3 rounded-2xl text-blue-600 border border-blue-100">
                                    <i class="fas fa-file-csv fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-black text-[#2c3856]">Importación Masiva</h3>
                                    <p class="text-slate-400 text-sm font-medium">Carga movimientos desde CSV</p>
                                </div>
                            </div>
                            
                            <div class="mt-2">
                                <div class="flex items-center justify-center w-full">
                                    <label for="movements_file" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-slate-300 rounded-3xl cursor-pointer bg-slate-50 hover:bg-white hover:border-[#ff9c00] transition-all group relative overflow-hidden">
                                        <div class="absolute inset-0 bg-[#ff9c00] opacity-0 group-hover:opacity-5 transition-opacity duration-300"></div>
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6 relative z-10">
                                            <i class="fas fa-cloud-upload-alt text-4xl text-slate-300 group-hover:text-[#ff9c00] mb-3 transition-colors transform group-hover:-translate-y-1 duration-300"></i>
                                            <p class="mb-2 text-sm text-slate-600 font-bold group-hover:text-[#2c3856]">Clic para seleccionar archivo</p>
                                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wide">Formato CSV requerido</p>
                                        </div>
                                        <input id="movements_file" name="movements_file" type="file" class="hidden" required accept=".csv,.txt" />
                                    </label>
                                </div>
                                
                                <div class="mt-6 flex items-center justify-between bg-blue-50/50 p-4 rounded-xl border border-blue-50">
                                    <div class="text-xs text-blue-800 font-bold uppercase tracking-wide">
                                        ¿Necesitas el formato base?
                                    </div>
                                    <button type="button" @click="downloadFilteredTemplate()" class="text-sm font-black text-[#2c3856] hover:text-[#ff9c00] flex items-center gap-2 transition-colors">
                                        <i class="fas fa-download"></i> Descargar Plantilla
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-8 py-6 flex flex-row-reverse gap-4 border-t border-slate-100">
                            <button type="submit" class="flex-1 py-3.5 rounded-xl bg-[#ff9c00] text-white text-sm font-bold uppercase hover:bg-orange-600 shadow-lg shadow-orange-500/20 transition-all transform hover:-translate-y-1 tracking-wide">
                                Procesar Archivo
                            </button>
                            <button type="button" @click="closeImportModal()" class="flex-1 py-3.5 rounded-xl border-2 border-slate-200 bg-white text-sm font-bold text-slate-600 hover:bg-slate-100 hover:border-slate-300 uppercase transition-all tracking-wide">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        function inventoryManager() {
            return {
                products: [],
                allWarehouses: [],
                allAreas: [],
                allQualities: [],
                availableWarehouses: [],
                availableQualities: [],
                
                filter: '',
                filterBrand: '',
                filterType: '',
                filterArea: '',
                filterStock: false,
                
                currentPage: 1,
                itemsPerPage: 50,

                isModalOpen: false,
                isImportModalOpen: false,
                isSaving: false,
                errorMessage: '',
                
                form: {
                    product_id: null,
                    product_name: '',
                    type: 'add',
                    quantity_raw: '',
                    reason: '',
                    warehouse_id: '',
                    ff_quality_id: '',
                    area_id: '',
                    stocks: {} 
                },

                init(productsData, warehousesData, areasData, qualitiesData) {
                    this.products = Array.isArray(productsData) ? productsData.map(p => ({
                        ...p,
                        movements_sum_quantity: p.movements_sum_quantity ? parseInt(p.movements_sum_quantity, 10) : 0,
                        unit_price: parseFloat(p.unit_price) || 0
                    })) : [];

                    this.allWarehouses = warehousesData || [];
                    this.allAreas = areasData || [];
                    this.allQualities = qualitiesData || [];
                    
                    this.availableWarehouses = this.allWarehouses;
                    this.availableQualities = this.allQualities;

                    const params = new URLSearchParams(window.location.search);
                    this.filterArea = params.get('area_id') || '';
                    this.filterBrand = params.get('brand') || '';
                    this.filterType = params.get('type') || '';

                    this.$watch('filter', () => this.currentPage = 1);
                    this.$watch('filterBrand', () => this.currentPage = 1);
                    this.$watch('filterType', () => this.currentPage = 1);
                    this.$watch('filterStock', () => this.currentPage = 1);
                },

                get filteredProducts() {
                    const search = this.filter.toLowerCase();
                    return this.products.filter(p => {
                        if (this.filterBrand && p.brand !== this.filterBrand) return false;
                        if (this.filterType && p.type !== this.filterType) return false;
                        if (this.filterArea && p.area_id != this.filterArea) return false;
                        
                        if (this.filterStock && (p.movements_sum_quantity || 0) <= 0) return false;

                        if (search) {
                            return p.sku.toLowerCase().includes(search) || 
                                p.description.toLowerCase().includes(search) ||
                                (p.upc && p.upc.toLowerCase().includes(search));
                        }
                        return true;
                    });
                },

                get flattenedRows() {
                    const rows = [];
                    
                    this.filteredProducts.forEach(p => {
                        if (p.quality_stocks && p.quality_stocks.length > 0) {
                            let variants = p.quality_stocks.filter(q => q.qty !== 0);
                            
                            if (variants.length > 0) {
                                variants.forEach(q => {
                                    rows.push({
                                        ...p, 
                                        display_quality: q.name,
                                        display_quality_id: q.id,
                                        display_stock: q.qty,
                                        row_key: `${p.id}_${q.id}`
                                    });
                                });
                            } else if (!this.filterStock) {
                                rows.push({
                                    ...p,
                                    display_quality: 'Sin Stock',
                                    display_quality_id: '',
                                    display_stock: 0,
                                    row_key: `${p.id}_empty`
                                });
                            }
                        } else {
                            if (!this.filterStock || (p.movements_sum_quantity > 0)) {
                                rows.push({
                                    ...p,
                                    display_quality: 'Estándar',
                                    display_quality_id: '',
                                    display_stock: p.movements_sum_quantity,
                                    row_key: `${p.id}_std`
                                });
                            }
                        }
                    });
                    
                    return rows;
                },

                get paginatedProducts() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    const end = start + this.itemsPerPage;
                    return this.flattenedRows.slice(start, end);
                },

                get totalPages() {
                    return Math.ceil(this.flattenedRows.length / this.itemsPerPage);
                },

                changePage(page) {
                    if (page >= 1 && page <= this.totalPages) {
                        this.currentPage = page;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                getPageRange() {
                    let current = this.currentPage;
                    let last = this.totalPages;
                    let delta = 2;
                    let left = current - delta;
                    let right = current + delta + 1;
                    let range = [];
                    let rangeWithDots = [];
                    let l;

                    for (let i = 1; i <= last; i++) {
                        if (i == 1 || i == last || i >= left && i < right) {
                            range.push(i);
                        }
                    }

                    for (let i of range) {
                        if (l) {
                            if (i - l === 2) {
                                rangeWithDots.push(l + 1);
                            } else if (i - l !== 1) {
                                rangeWithDots.push('...');
                            }
                        }
                        rangeWithDots.push(i);
                        l = i;
                    }
                    return rangeWithDots;
                },

                get totalStock() {
                    return this.filteredProducts.reduce((acc, p) => acc + (p.movements_sum_quantity || 0), 0);
                },

                get totalValue() {
                    return this.filteredProducts.reduce((acc, p) => acc + ((p.unit_price || 0) * (Math.max(0, p.movements_sum_quantity))), 0);
                },

                get currentWarehouseStock() {
                    if (!this.form.warehouse_id || !this.form.stocks) return 0;
                    return this.form.stocks[this.form.warehouse_id] || 0;
                },

                formatMoney(amount) {
                    return '$' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                },

                openModal(product, type, qualityId = '') {
                    this.isModalOpen = true;
                    this.errorMessage = '';
                    
                    this.form.product_id = product.id;
                    this.form.product_name = product.description;
                    this.form.type = type;
                    this.form.quantity_raw = ''; 
                    this.form.reason = '';
                    this.form.ff_quality_id = qualityId || '';
                    this.form.stocks = product.warehouse_stocks || {};
                    
                    this.form.area_id = product.area_id; 
                    
                    this.updateResources();

                    const urlParams = new URLSearchParams(window.location.search);
                    const currentFilterWh = urlParams.get('warehouse_id');

                    if (currentFilterWh && this.availableWarehouses.some(w => w.id == currentFilterWh)) {
                        this.form.warehouse_id = currentFilterWh;
                    } else {
                        this.form.warehouse_id = '';
                    }

                    this.$nextTick(() => {
                        const input = document.querySelector('input[type="number"]');
                        if(input) input.focus();
                    });
                },

                updateResources() {
                    if (this.form.area_id) {
                        this.availableWarehouses = this.allWarehouses.filter(w => w.area_id == this.form.area_id);
                        this.availableQualities = this.allQualities.filter(q => q.area_id == this.form.area_id);
                    } else {
                        this.availableWarehouses = this.allWarehouses;
                        this.availableQualities = this.allQualities;
                    }
                    if (!this.availableWarehouses.some(w => w.id == this.form.warehouse_id)) {
                        this.form.warehouse_id = '';
                    }
                },
                
                closeModal() {
                    this.isModalOpen = false;
                },

                openImportModal() { this.isImportModalOpen = true; },
                closeImportModal() { this.isImportModalOpen = false; },
                
                resetFilters() {
                    this.filter = '';
                    this.filterBrand = '';
                    this.filterType = '';
                    this.filterStock = false;
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('warehouse_id') || urlParams.has('area_id') || urlParams.has('quality_id')) {
                        window.location.href = "{{ route('ff.inventory.index') }}";
                    }
                },

                async submitMovement() {
                    if (this.isSaving) return;
                    if (!this.form.quantity_raw || this.form.quantity_raw <= 0) {
                        this.errorMessage = "La cantidad debe ser mayor a 0.";
                        return;
                    }
                    if (!this.form.warehouse_id) {
                        this.errorMessage = "Debe seleccionar un almacén.";
                        return;
                    }
                    
                    this.isSaving = true;
                    this.errorMessage = '';

                    const finalQuantity = this.form.type === 'add' ? this.form.quantity_raw : -this.form.quantity_raw;

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        const response = await fetch("{{ route('ff.inventory.storeMovement') }}", {
                            method: 'POST',
                            body: JSON.stringify({
                                product_id: this.form.product_id,
                                quantity: finalQuantity,
                                reason: this.form.reason,
                                warehouse_id: this.form.warehouse_id,
                                area_id: this.form.area_id,
                                ff_quality_id: this.form.ff_quality_id
                            }),
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                        });

                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Error desconocido');

                        window.location.reload();

                    } catch (error) {
                        this.errorMessage = error.message || 'Error de conexión.';
                        this.isSaving = false;
                    }
                },

                exportFilteredCsv() {
                    const baseUrl = "{{ route('ff.inventory.exportCsv') }}";
                    const params = new URLSearchParams(window.location.search);
                    if (this.filter) params.append('search', this.filter);
                    if (this.filterBrand) params.append('brand', this.filterBrand);
                    if (this.filterType) params.append('type', this.filterType);
                    if (this.filterArea) params.append('area_id', this.filterArea);
                    window.location.href = `${baseUrl}?${params.toString()}`;
                },
                downloadFilteredTemplate() {
                    const baseUrl = "{{ route('ff.inventory.movementsTemplate') }}";
                    const params = new URLSearchParams(window.location.search);
                    if (this.filter) params.append('search', this.filter);
                    if (this.filterBrand) params.append('brand', this.filterBrand);
                    if (this.filterType) params.append('type', this.filterType);
                    if (this.filterArea) params.append('area_id', this.filterArea);
                    window.location.href = `${baseUrl}?${params.toString()}`;
                }
            }
        }
    </script>
</x-app-layout>