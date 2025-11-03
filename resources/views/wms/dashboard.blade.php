<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-2xl text-[#2c3856] leading-tight">
                    Centro de Mando WMS
                </h2>
                <p class="text-gray-600 text-sm mt-1">Visión interactiva de las operaciones del almacén.</p>
            </div>
        </div>
    </x-slot>

    {{-- Estilos para la vitalidad --}}
    <style>
        .fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .tab-button.active {
            border-color: #ff9c00;
            background-color: #fff;
            color: #ff9c00;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
    </style>

    <div class="py-6" x-data="wmsDashboard()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            <div class="bg-white p-5 rounded-lg shadow-lg mb-6 fade-in-up" style="animation-delay: 100ms;">
                <div x-data="lpnSearch('{{ route('wms.api.find-lpn') }}')">
                    <h3 class="text-lg font-bold text-[#2c3856] mb-3">Consulta Rápida de LPN</h3>
                    <div class="flex items-center space-x-3 relative">
                        <input type="text" 
                               x-model="lpn"
                               @keydown.enter.prevent="findLpn"
                               @input.debounce.500ms="findLpn"
                               class="block w-full md:w-1/3 border-gray-300 rounded-md shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00]"
                               placeholder="Escanear o teclear LPN...">
                        <button @click="findLpn" class="px-5 py-2 bg-[#2c3856] text-white rounded-md hover:bg-[#1f2940] transition" :disabled="loading">
                            <span x-show="!loading">Buscar</span>
                            <span x-show="loading">Buscando...</span>
                        </button>
                    </div>

                    {{-- Pop-up de Resultados de LPN --}}
                    <div x-show="error" x-transition class="mt-3 p-3 bg-red-100 text-red-700 rounded-md" x-text="error"></div>
                    
                    <div x-show="pallet" x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform scale-95" 
                         x-transition:enter-end="opacity-100 transform scale-100"
                         class="absolute z-10 mt-2 w-full md:w-2/3 lg:w-1/2 bg-white border border-gray-200 rounded-lg shadow-2xl p-5" 
                         @click.away="pallet = null">
                        
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-xl font-bold text-[#ff9c00]" x-text="pallet.lpn"></h4>
                                <p class="text-sm text-gray-500">
                                    En <strong class="text-gray-700" x-text="pallet.location.code"></strong> 
                                    (Tipo: <span x-text="pallet.location.translated_type"></span>)
                                </p>
                            </div>
                            <button @click="pallet = null" class="text-gray-400 hover:text-gray-600">&times;</button>
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
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                {{-- Columna Principal de Flujos --}}
                <div class="lg:col-span-3 space-y-6">
                    <div x-data="{ tab: 'resumen' }" class="bg-white rounded-lg shadow-lg p-5 fade-in-up" style="animation-delay: 200ms;">
                        <div class="border-b border-gray-200 mb-4">
                            <nav class="flex space-x-2 -mb-px">
                                <button @click="tab = 'resumen'" :class="{ 'active': tab === 'resumen' }" class="tab-button py-3 px-4 font-semibold text-gray-500 border-b-2 border-transparent hover:text-[#ff9c00] hover:border-gray-300 transition">
                                    Resumen
                                </button>
                                <button @click="tab = 'entradas'" :class="{ 'active': tab === 'entradas' }" class="tab-button py-3 px-4 font-semibold text-gray-500 border-b-2 border-transparent hover:text-[#ff9c00] hover:border-gray-300 transition">
                                    Entradas
                                </button>
                                <button @click="tab = 'salidas'" :class="{ 'active': tab === 'salidas' }" class="tab-button py-3 px-4 font-semibold text-gray-500 border-b-2 border-transparent hover:text-[#ff9c00] hover:border-gray-300 transition">
                                    Salidas
                                </button>
                                <button @click="tab = 'gestion'" :class="{ 'active': tab === 'gestion' }" class="tab-button py-3 px-4 font-semibold text-gray-500 border-b-2 border-transparent hover:text-[#ff9c00] hover:border-gray-300 transition">
                                    Gestión
                                </button>
                            </nav>
                        </div>

                        <div>
                            {{-- Pestaña: Resumen (KPIs con Vitalidad) --}}
                            <div x-show="tab === 'resumen'" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                                @foreach($kpis as $kpi)
                                <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 flex items-center space-x-4">
                                    <div class="flex-shrink-0 p-3 rounded-full bg-[#ff9c00] bg-opacity-10">
                                        <svg class="w-6 h-6 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $kpi['icon'] }}"></path></svg>
                                    </div>
                                    <div x-data="{ count: 0 }" x-init="animateCountTo(count, {{ $kpi['value'] }})">
                                        <p class="text-gray-500 text-sm">{{ $kpi['label'] }}</p>
                                        <p class="text-3xl font-bold text-[#2c3856]">
                                            <span x-text="count.toLocaleString('es-MX', { maximumFractionDigits: 0 })"></span>
                                            @if($kpi['format'] === 'percent')%@endif
                                        </p>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            {{-- Pestaña: Entradas --}}
                            <div x-show="tab === 'entradas'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-bold text-[#2c3856] mb-3">En Muelle (Receiving)</h4>
                                    <ul class="space-y-3 max-h-96 overflow-y-auto">
                                        @forelse($receivingPOs as $po)
                                            <li class="p-3 bg-blue-50 border border-blue-200 rounded-lg hover:shadow-md transition">
                                                <a href="{{ route('wms.receiving.show', $po) }}" class="block">
                                                    <div class="flex justify-between items-center">
                                                        <span class="font-bold text-blue-800">{{ $po->po_number }}</span>
                                                        <span class="text-xs text-blue-600">{{ $po->latestArrival->arrival_time->diffForHumans() ?? 'N/A' }}</span>
                                                    </div>
                                                    <p class="text-sm text-gray-600">{{ $po->latestArrival->driver_name ?? 'Sin operador' }}</p>
                                                </a>
                                            </li>
                                        @empty
                                            <li class="text-gray-500 text-sm">No hay órdenes en recepción.</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-700 mb-3">Próximas (Pendientes)</h4>
                                    <ul class="space-y-3 max-h-96 overflow-y-auto">
                                        @forelse($pendingPOs as $po)
                                            <li class="p-3 bg-gray-50 border border-gray-200 rounded-lg hover:shadow-md transition">
                                                <a href="{{ route('wms.purchase-orders.show', $po) }}" class="block">
                                                    <div class="flex justify-between items-center">
                                                        <span class="font-bold text-gray-800">{{ $po->po_number }}</span>
                                                        <span class="text-xs font-medium {{ $po->expected_date->isToday() ? 'text-red-600' : 'text-gray-500' }}">{{ $po->expected_date->format('d-M-Y') }}</span>
                                                    </div>
                                                    <p class="text-sm text-gray-600">Container: {{ $po->container_number ?? 'N/A' }}</p>
                                                </a>
                                            </li>
                                        @empty
                                            <li class="text-gray-500 text-sm">No hay órdenes pendientes.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>

                            {{-- Pestaña: Salidas --}}
                            <div x-show="tab === 'salidas'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-bold text-[#2c3856] mb-3">En Surtido (Picking)</h4>
                                    <ul class="space-y-3 max-h-96 overflow-y-auto">
                                        @forelse($pickingSOs as $so)
                                            <li class="p-3 bg-green-50 border border-green-200 rounded-lg hover:shadow-md transition">
                                                <a href="{{ route('wms.picking.show', $so->pickList) }}" class="block">
                                                    <div class="flex justify-between items-center">
                                                        <span class="font-bold text-green-800">{{ $so->so_number }}</span>
                                                        <span class="text-xs text-green-600">{{ $so->pickList->status ?? 'N/A' }}</span>
                                                    </div>
                                                    <p class="text-sm text-gray-600">Cliente: {{ $so->customer_name }}</p>
                                                </a>
                                            </li>
                                        @empty
                                            <li class="text-gray-500 text-sm">No hay órdenes en surtido.</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-700 mb-3">Pendientes de Surtir</h4>
                                    <ul class="space-y-3 max-h-96 overflow-y-auto">
                                        @forelse($pendingSOs as $so)
                                            <li class="p-3 bg-gray-50 border border-gray-200 rounded-lg hover:shadow-md transition">
                                                <a href="{{ route('wms.sales-orders.show', $so) }}" class="block">
                                                    <div class="flex justify-between items-center">
                                                        <span class="font-bold text-gray-800">{{ $so->so_number }}</span>
                                                        <span class="text-xs font-medium {{ $so->order_date->isToday() ? 'text-red-600' : 'text-gray-500' }}">{{ $so->order_date->format('d-M-Y') }}</span>
                                                    </div>
                                                    <p class="text-sm text-gray-600">Cliente: {{ $so->customer_name }}</p>
                                                </a>
                                            </li>
                                        @empty
                                            <li class="text-gray-500 text-sm">No hay órdenes pendientes.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>

                            {{-- Pestaña: Gestión (El Mosaico) --}}
                            <div x-show="tab === 'gestion'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                                <x-wms-tile-link :href="route('wms.reports.index')" icon="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" color="purple">
                                    Reportes y Analítica
                                </x-wms-tile-link>
                                <x-wms-tile-link :href="route('wms.inventory.index')" icon="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" color="yellow">
                                    Inventario (LPNs)
                                </x-wms-tile-link>
                                <x-wms-tile-link :href="route('wms.physical-counts.index')" icon="M9 7h6m0 0v6m0-6L9 13" color="gray">
                                    Conteos Físicos
                                </x-wms-tile-link>
                                <x-wms-tile-link :href="route('wms.products.index')" icon="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" color="red">
                                    Catálogo de Productos
                                </x-wms-tile-link>
                                <x-wms-tile-link :href="route('wms.locations.index')" icon="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM12 11a2 2 0 100-4 2 2 0 000 4z" color="red">
                                    Gestión de Ubicaciones
                                </x-wms-tile-link>
                                <x-wms-tile-link :href="route('wms.lpns.index')" icon="M5 5a2 2 0 012-2h10a2 2 0 012 2v1h2a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h2V5zm10 0H9v1h6V5zm-2 5h-2v6h2v-6z" color="red">
                                    Gestión de LPNs
                                </x-wms-tile-link>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Columna Lateral de Acciones --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- Tareas Pendientes --}}
                    <div class="bg-white rounded-lg shadow-lg p-5 fade-in-up" style="animation-delay: 300ms;">
                        <h3 class="text-lg font-bold text-[#2c3856] mb-4 border-b pb-2">Atención Requerida</h3>
                        <ul class="space-y-3 max-h-64 overflow-y-auto">
                             @forelse($discrepancyTasks as $task)
                                <li class="p-3 bg-red-50 border border-red-200 rounded-lg hover:shadow-md transition">
                                    <a href="{{ route('wms.physical-counts.show', $task->physical_count_session_id) }}" class="block">
                                        <div class="flex justify-between items-center">
                                            <span class="font-bold text-red-800">Discrepancia</span>
                                            <span class="text-xs text-red-600">Conteo</span>
                                        </div>
                                        <p class="text-sm text-gray-700">SKU: {{ $task->product->sku ?? 'N/A' }}</p>
                                        <p class="text-sm text-gray-500">Ubic: {{ $task->location->code ?? 'N/A' }}</p>
                                    </a>
                                </li>
                            @empty
                                <li class="text-gray-500 text-sm">No hay acciones que requieran atención.</li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- Acciones Rápidas --}}
                    <div class="bg-white rounded-lg shadow-lg p-5 fade-in-up" style="animation-delay: 400ms;">
                        <h3 class="text-lg font-bold text-[#2c3856] mb-4 border-b pb-2">Acciones Rápidas</h3>
                        <div class="space-y-3">
                            <x-wms-tile-link :href="route('wms.inventory.transfer.create')" icon="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" color="blue">
                                Iniciar Transferencia
                            </x-wms-tile-link>
                             <x-wms-tile-link :href="route('wms.inventory.split.create')" icon="M15 15l-6 6m0 0l-6-6m6 6V9a6 6 0 0112 0v3" color="blue">
                                Iniciar Split (División)
                            </x-wms-tile-link>
                            <x-wms-tile-link :href="route('wms.physical-counts.create')" icon="M9 7h6m0 0v6m0-6L9 13" color="gray">
                                Nuevo Conteo Físico
                            </x-wms-tile-link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Helper para animar contadores
        function animateCountTo(el, target) {
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

        // Alpine Component para el LPN Search
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
                        this.lpn = '';
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