<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center">
            <div>
                <h2 class="font-semibold text-2xl text-[#2c3856] leading-tight">
                    {{ __('Centro de Mando WMS') }}
                </h2>
                <p class="text-gray-600 text-sm mt-1">Panel de control interactivo de operaciones y catálogos.</p>
            </div>
            
            <div class="flex space-x-3 mt-4 sm:mt-0">
                <a href="{{ route('wms.purchase-orders.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition duration-150 ease-in-out">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Gestionar Arribos (PO)
                </a>
                <a href="{{ route('wms.sales-orders.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition duration-150 ease-in-out">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Gestionar Salidas (SO)
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .tab-button.active {
            border-color: #ff9c00;
            background-color: #fff;
            color: #ff9c00;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <div class="py-10" x-data="{ 
        tab: 'mando',
        animateCountTo(el, target) {
            let start = 0;
            const duration = 1500;
            const step = (timestamp) => {
                if (!start) start = timestamp;
                const progress = timestamp - start;
                const percentage = Math.min(progress / duration, 1);
                el.count = Math.floor(target * percentage);
                if (progress < duration) {
                    window.requestAnimationFrame(step);
                } else {
                    el.count = target;
                }
            };
            window.requestAnimationFrame(step);
        }
    }" x-cloak>
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                <form method="GET" action="{{ route('wms.dashboard') }}" class="flex items-end space-x-4">
                    <div>
                        <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Ver Datos del Almacén</label>
                        <select name="warehouse_id" id="warehouse_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" onchange="this.form.submit()">
                            <option value="">-- Todos los Almacenes --</option>
                            
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @if($warehouseId == $warehouse->id) selected @endif>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <a href="{{ route('wms.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Limpiar
                    </a>
                </form>
            </div>            

            <div class="bg-white rounded-lg shadow-md p-2 mb-6">
                <nav class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                    <button @click="tab = 'mando'" :class="{ 'active': tab === 'mando' }" class="tab-button w-full text-center py-3 px-5 font-semibold text-gray-600 border-b-2 sm:border-b-4 border-transparent hover:text-[#ff9c00] hover:border-gray-300 transition rounded-md">
                        <i class="fas fa-tachometer-alt mr-2"></i> Centro de Mando
                    </button>
                    <button @click="tab = 'inventario'" :class="{ 'active': tab === 'inventario' }" class="tab-button w-full text-center py-3 px-5 font-semibold text-gray-600 border-b-2 sm:border-b-4 border-transparent hover:text-[#ff9c00] hover:border-gray-300 transition rounded-md">
                        <i class="fas fa-boxes mr-2"></i> Acciones de Inventario
                    </button>
                    <button @click="tab = 'catalogos'" :class="{ 'active': tab === 'catalogos' }" class="tab-button w-full text-center py-3 px-5 font-semibold text-gray-600 border-b-2 sm:border-b-4 border-transparent hover:text-[#ff9c00] hover:border-gray-300 transition rounded-md">
                        <i class="fas fa-book mr-2"></i> Catálogos
                    </button>
                    <button @click="tab = 'reportes'" :class="{ 'active': tab === 'reportes' }" class="tab-button w-full text-center py-3 px-5 font-semibold text-gray-600 border-b-2 sm:border-b-4 border-transparent hover:text-[#ff9c00] hover:border-gray-300 transition rounded-md">
                        <i class="fas fa-chart-pie mr-2"></i> Reportes (BI)
                    </button>
                </nav>
            </div>

            <div x-show="tab === 'mando'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
                    @foreach($kpis as $kpi)
                    <div class="bg-white p-5 rounded-lg shadow-lg flex items-center space-x-4 border-l-4 border-blue-500 fade-in-up">
                        <div class="flex-shrink-0 p-3 rounded-full bg-blue-100">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $kpi['icon'] }}"></path></svg>
                        </div>
                        <div x-data="{ count: 0 }" x-init="animateCountTo(count, {{ $kpi['value'] }})">
                            <p class="text-gray-600 text-sm font-medium">{{ $kpi['label'] }}</p>
                            <p class="text-3xl font-bold text-[#2c3856]">
                                <span x-text="count.toLocaleString('es-MX', { maximumFractionDigits: 0 })"></span>
                                @if($kpi['format'] === 'percent')%@endif
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-lg p-5 space-y-4 fade-in-up" style="animation-delay: 100ms;">
                        <h3 class="text-xl font-bold text-blue-800 border-b-2 border-blue-200 pb-2">Flujo de Entrada</h3>
                        
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">En Muelle (Receiving)</h4>
                            <ul class="space-y-2 max-h-48 overflow-y-auto">
                                @forelse($receivingPOs as $po)
                                    <li class="p-2 bg-blue-50 border border-blue-200 rounded-lg hover:shadow-md transition">
                                        <a href="{{ route('wms.receiving.show', $po) }}" class="block">
                                            <span class="font-bold text-blue-800">{{ $po->po_number }}</span>
                                            <p class="text-sm text-gray-600">{{ $po->latestArrival->driver_name ?? 'Sin operador' }} - <span class="text-xs">{{ $po->latestArrival->arrival_time->diffForHumans() ?? 'N/A' }}</span></p>
                                        </a>
                                    </li>
                                @empty
                                    <li class="text-gray-500 text-sm p-2">No hay órdenes en muelle.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Próximos Arribos</h4>
                            <ul class="space-y-2 max-h-48 overflow-y-auto">
                                @forelse($pendingPOs as $po)
                                    <li class="p-2 bg-gray-50 border border-gray-200 rounded-lg hover:shadow-md transition">
                                        <a href="{{ route('wms.purchase-orders.show', $po) }}" class="block">
                                            <span class="font-bold text-gray-800">{{ $po->po_number }}</span>
                                            <p class="text-sm text-gray-600">Esperado: <span class="font-medium {{ $po->expected_date->isToday() ? 'text-red-600' : 'text-gray-500' }}">{{ $po->expected_date->format('d-M-Y') }}</span></p>
                                        </a>
                                    </li>
                                @empty
                                    <li class="text-gray-500 text-sm p-2">No hay arribos pendientes.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-lg p-5 space-y-4 fade-in-up" style="animation-delay: 200ms;">
                        <h3 class="text-xl font-bold text-green-800 border-b-2 border-green-200 pb-2">Flujo de Salida</h3>
                        
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">En Surtido (Picking)</h4>
                            <ul class="space-y-2 max-h-48 overflow-y-auto">
                                @forelse($pickingSOs as $so)
                                    <li class="p-2 bg-green-50 border border-green-200 rounded-lg hover:shadow-md transition">
                                        <a href="{{ route('wms.picking.show', $so->pickList) }}" class="block">
                                            <span class="font-bold text-green-800">{{ $so->so_number }}</span>
                                            <p class="text-sm text-gray-600">Cliente: {{ $so->customer_name }}</p>
                                        </a>
                                    </li>
                                @empty
                                    <li class="text-gray-500 text-sm p-2">No hay órdenes en surtido.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Pendientes de Surtir</h4>
                            <ul class="space-y-2 max-h-48 overflow-y-auto">
                                @forelse($pendingSOs as $so)
                                    <li class="p-2 bg-gray-50 border border-gray-200 rounded-lg hover:shadow-md transition">
                                        <a href="{{ route('wms.sales-orders.show', $so) }}" class="block">
                                            <span class="font-bold text-gray-800">{{ $so->so_number }}</span>
                                            <p class="text-sm text-gray-600">Entrega: <span class="font-medium {{ $so->order_date->isToday() ? 'text-red-600' : 'text-gray-500' }}">{{ $so->order_date->format('d-M-Y') }}</span></p>
                                        </a>
                                    </li>
                                @empty
                                    <li class="text-gray-500 text-sm p-2">No hay órdenes pendientes.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-lg p-5 space-y-4 fade-in-up" style="animation-delay: 300ms;">
                        <h3 class="text-xl font-bold text-red-800 border-b-2 border-red-200 pb-2">Atención Requerida</h3>
                        
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Discrepancias de Conteo</h4>
                            <ul class="space-y-2 max-h-48 overflow-y-auto">
                                @forelse($discrepancyTasks as $task)
                                    <li class="p-2 bg-red-50 border border-red-200 rounded-lg hover:shadow-md transition">
                                        <a href="{{ route('wms.physical-counts.show', $task->physical_count_session_id) }}" class="block">
                                            <span class="font-bold text-red-800">Ubic: {{ $task->location->code ?? 'N/A' }}</span>
                                            <p class="text-sm text-gray-600">SKU: {{ $task->product->sku ?? 'N/A' }}</p>
                                        </a>
                                    </li>
                                @empty
                                    <li class="text-gray-500 text-sm p-2">¡Sin discrepancias!</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Stock No Disponible (QC/Dañado)</h4>
                            <ul class="space-y-2 max-h-48 overflow-y-auto">
                                @forelse($nonAvailableStock as $item)
                                    <li class="p-2 bg-yellow-50 border border-yellow-200 rounded-lg hover:shadow-md transition">
                                        <a href="{{ route('wms.reports.non-available-inventory') }}" class="block">
                                            <span class="font-bold text-yellow-800">LPN: {{ $item->pallet->lpn ?? 'N/A' }}</span>
                                            <p class="text-sm text-gray-600">SKU: {{ $item->product->sku ?? 'N/A' }} ({{ $item->quality->name ?? 'N/A' }})</p>
                                        </a>
                                    </li>
                                @empty
                                    <li class="text-gray-500 text-sm p-2">Sin stock no disponible.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="tab === 'inventario'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                
                <div class="bg-white p-5 rounded-lg shadow-lg mb-6 fade-in-up relative z-20" x-data="lpnSearch('{{ route('wms.api.find-lpn') }}')">
                    <h3 class="text-lg font-bold text-[#2c3856] mb-3">Consulta Rápida de LPN</h3>
                    <div class="flex items-center space-x-3 relative">
                        <input type="text" 
                               x-model="lpn"
                               @keydown.enter.prevent="findLpn"
                               class="block w-full md:w-1/3 border-gray-300 rounded-md shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00]"
                               placeholder="Escanear o teclear LPN...">
                        <button @click="findLpn" class="px-5 py-2 bg-[#2c3856] text-white rounded-md hover:bg-[#1f2940] transition" :disabled="loading">
                            <span x-show="!loading">Buscar</span>
                            <span x-show="loading">Buscando...</span>
                        </button>
                    </div>

                    <div x-show="error" x-transition class="mt-3 p-3 bg-red-100 text-red-700 rounded-md" x-text="error"></div>
                    
                    <div x-show="pallet" x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform scale-95" 
                         x-transition:enter-end="opacity-100 transform scale-100"
                         class="absolute z-10 mt-2 w-full md:w-2/3 lg:w-1/2 bg-white border border-gray-200 rounded-lg shadow-2xl p-5" 
                         @click.away="pallet = null; error = ''">
                        
                        <template x-if="pallet">
                            <div>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="text-xl font-bold text-[#ff9c00]" x-text="pallet.lpn"></h4>
                                        <p class="text-sm text-gray-500">
                                            En <strong class="text-gray-700" x-text="pallet.location.code"></strong> 
                                        </p>
                                    </div>
                                    <button @click="pallet = null; error = ''" class="text-gray-400 hover:text-gray-600">&times;</button>
                                </div>

                                <div class="mt-4 border-t pt-4">
                                    <h5 class="font-semibold mb-2">Contenido de la Tarima:</h5>
                                    <ul class="space-y-2 max-h-48 overflow-y-auto">
                                        <template x-for="item in pallet.items" :key="item.id">
                                            <li class="flex justify-between items-center p-2 bg-gray-50 rounded-md">
                                                <div>
                                                    <p class="font-medium text-gray-800" x-text="item.product.sku"></p>
                                                    <p class="text-sm text-gray-500" x-text="item.product.name"></p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-lg font-bold text-[#2c3856]" x-text="item.quantity"></p>
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800" x-text="item.quality.name"></span>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                    <div class="text-xs text-gray-400 mt-3">
                                        <p>Recibido por: <span x-text="pallet.user ? pallet.user.name : 'N/A'"></span></p>
                                        <p>PO Origen: <span x-text="pallet.purchase_order ? pallet.purchase_order.po_number : 'N/A'"></span></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <x-wms-tile-link :href="route('wms.inventory.index')" icon="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" color="blue">
                        Vista de Inventario (LPNs)
                    </x-wms-tile-link>
                    <x-wms-tile-link :href="route('wms.inventory.transfer.create')" icon="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" color="blue">
                        Transferir LPN
                    </x-wms-tile-link>
                    <x-wms-tile-link :href="route('wms.inventory.split.create')" icon="M15 15l-6 6m0 0l-6-6m6 6V9a6 6 0 0112 0v3" color="blue">
                        Dividir LPN (Split)
                    </x-wms-tile-link>
                    <x-wms-tile-link :href="route('wms.physical-counts.index')" icon="M9 7h6m0 0v6m0-6L9 13" color="gray">
                        Conteos Físicos
                    </x-wms-tile-link>
                    <x-wms-tile-link :href="route('wms.inventory.adjustments.log')" icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" color="gray">
                        Log de Ajustes
                    </x-wms-tile-link>
                    <x-wms-tile-link :href="route('wms.inventory.pallet-info.index')" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" color="gray">
                        Consulta Manual LPN
                    </x-wms-tile-link>
                </div>
            </div>

            <div x-show="tab === 'catalogos'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <x-wms-tile-link :href="route('wms.products.index')" icon="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" color="red">
                        Productos
                    </x-wms-tile-link>
                    <x-wms-tile-link :href="route('wms.locations.index')" icon="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM12 11a2 2 0 100-4 2 2 0 000 4z" color="red">
                        Ubicaciones
                    </x-wms-tile-link>
                    <x-wms-tile-link :href="route('wms.warehouses.index')" icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h6" color="red">
                        Almacenes
                    </x-wms-tile-link>
                    <x-wms-tile-link :href="route('wms.brands.index')" icon="M5 13l4 4L19 7" color="red">
                        Marcas
                    </x-wms-tile-link>
                    <x-wms-tile-link :href="route('wms.product-types.index')" icon="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" color="red">
                        Tipos de Producto
                    </x-wms-tile-link>
                    <x-wms-tile-link :href="route('wms.qualities.index')" icon="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" color="red">
                        Calidades
                    </x-wms-tile-link>
                    <x-wms-tile-link :href="route('wms.lpns.index')" icon="M5 5a2 2 0 012-2h10a2 2 0 012 2v1h2a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h2V5zm10 0H9v1h6V5zm-2 5h-2v6h2v-6z" color="red">
                        Generador de LPNs
                    </x-wms-tile-link>
                </div>
            </div>

            <div x-show="tab === 'reportes'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    
                    <a href="{{ route('wms.reports.inventory') }}" 
                       class="relative group block bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-blue-300 transition-all duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden">
                        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-32 h-32 bg-blue-100 rounded-full opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                        <div class="mb-4 inline-block p-3 bg-blue-100 rounded-lg shadow-sm border border-blue-200">
                            <svg class="w-8 h-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-blue-700 transition-colors duration-300">
                            Dashboard de Inventario
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Visión general del stock, productos top y antigüedad del inventario. 📈
                        </p>
                        <span class="absolute bottom-6 right-6 text-blue-300 group-hover:text-blue-500 group-hover:translate-x-1 transition-transform duration-300">
                            <i class="fas fa-arrow-right fa-lg"></i>
                        </span>
                    </a>

                    <a href="{{ route('wms.reports.stock-movements') }}" 
                       class="relative group block bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-indigo-300 transition-all duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden">
                        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-36 h-36 bg-indigo-100 rounded-full opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                        <div class="mb-4 inline-block p-3 bg-indigo-100 rounded-lg shadow-sm border border-indigo-200">
                            <svg class="w-8 h-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-indigo-700 transition-colors duration-300">
                            Historial de Movimientos
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Registro detallado de todas las entradas, salidas, ajustes y transferencias. 🔍
                        </p>
                        <span class="absolute bottom-6 right-6 text-indigo-300 group-hover:text-indigo-500 group-hover:translate-x-1 transition-transform duration-300">
                            <i class="fas fa-arrow-right fa-lg"></i>
                        </span>
                    </a>
                    
                    <a href="{{ route('wms.reports.inventory-aging') }}" 
                       class="relative group block bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-yellow-300 transition-all duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden">
                        <div class="absolute top-0 left-0 -mt-12 -ml-12 w-36 h-36 bg-yellow-100 rounded-full opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                        <div class="mb-4 inline-block p-3 bg-yellow-100 rounded-lg shadow-sm border border-yellow-200">
                            <svg class="w-8 h-8 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-yellow-700 transition-colors duration-300">
                            Antigüedad de Inventario
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Análisis de inventario por LPN y sus días en almacén. ⏳
                        </p>
                        <span class="absolute bottom-6 right-6 text-yellow-300 group-hover:text-yellow-500 group-hover:translate-x-1 transition-transform duration-300">
                            <i class="fas fa-arrow-right fa-lg"></i>
                        </span>
                    </a>
                    
                    <a href="{{ route('wms.reports.non-available-inventory') }}" 
                       class="relative group block bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-red-300 transition-all duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden">
                        <div class="absolute bottom-0 right-0 -mb-10 -mr-10 w-32 h-32 bg-red-100 rounded-full opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                        <div class="mb-4 inline-block p-3 bg-red-100 rounded-lg shadow-sm border border-red-200">
                            <svg class="w-8 h-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                               <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-red-700 transition-colors duration-300">
                            Inventario No Disponible
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Stock dañado, en inspección o bloqueado que requiere acción. ⚠️
                        </p>
                        <span class="absolute bottom-6 right-6 text-red-300 group-hover:text-red-500 group-hover:translate-x-1 transition-transform duration-300">
                            <i class="fas fa-arrow-right fa-lg"></i>
                        </span>
                    </a>
                    
                    <a href="{{ route('wms.reports.abc-analysis') }}" 
                       class="relative group block bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-green-300 transition-all duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden">
                        <div class="absolute top-0 right-0 -mt-12 -mr-12 w-36 h-36 bg-green-100 rounded-full opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                        <div class="mb-4 inline-block p-3 bg-green-100 rounded-lg shadow-sm border border-green-200">
                            <svg class="w-8 h-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1.125-1.5M13.5 16.5L12 15m1.5 1.5l1.125-1.5M13.5 16.5h-1.5m7.5 4.5v-1.5m0 1.5A2.25 2.25 0 0018 18.75h-2.25m-7.5 0h7.5m-7.5 0l-1.125 1.5M13.5 18.75L12 21m1.5-2.25l1.125 1.5M13.5 18.75h-1.5m-3-15.75v11.25c0 1.242.984 2.25 2.25 2.25h2.25c1.242 0 2.25-.984 2.25-2.25V3m-7.5 0h7.5" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-green-700 transition-colors duration-300">
                            Análisis ABC-XYZ
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Clasificación de productos por Valor (ABC) y Frecuencia (XYZ). 📊
                        </p>
                        <span class="absolute bottom-6 right-6 text-green-300 group-hover:text-green-500 group-hover:translate-x-1 transition-transform duration-300">
                            <i class="fas fa-arrow-right fa-lg"></i>
                        </span>
                    </a>

                    <a href="{{ route('wms.reports.slotting-heatmap') }}" 
                       class="relative group block bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-teal-300 transition-all duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden">
                        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-32 h-32 bg-teal-100 rounded-full opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                        <div class="mb-4 inline-block p-3 bg-teal-100 rounded-lg shadow-sm border border-teal-200">
                            <svg class="w-8 h-8 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-teal-700 transition-colors duration-300">
                            Mapa de Calor (Slotting)
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Visualización interactiva de la eficiencia de ubicaciones vs. productos. 🔥
                        </p>
                        <span class="absolute bottom-6 right-6 text-teal-300 group-hover:text-teal-500 group-hover:translate-x-1 transition-transform duration-300">
                            <i class="fas fa-arrow-right fa-lg"></i>
                        </span>
                    </a>

                </div>
            </div>

        </div>
    </div>

    <script>
        // Script para la Búsqueda de LPN en la Pestaña "Inventario"
        function lpnSearch(apiUrl) {
            return {
                lpn: '',
                pallet: null,
                loading: false,
                error: '',
                findLpn() {
                    if (!this.lpn.trim()) {
                        this.pallet = null;
                        this.error = '';
                        return;
                    }
                    this.loading = true;
                    this.pallet = null;
                    this.error = '';

                    fetch(`${apiUrl}?lpn=${this.lpn}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => {
                        if (response.ok) return response.json();
                        if (response.status === 404) throw new Error('LPN no encontrado.');
                        throw new Error('Error de servidor.');
                    })
                    .then(data => {
                        this.pallet = data;
                        this.lpn = ''; // Limpiar input
                    })
                    .catch(err => {
                        this.error = err.message;
                    })
                    .finally(() => {
                        this.loading = false;
                    });
                }
            }
        }
    </script>
</x-app-layout>