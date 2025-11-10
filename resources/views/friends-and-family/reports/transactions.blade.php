<x-app-layout>
    <div x-data="{ 
        showDetailModal: false, 
        currentFolio: null, 
        saleDetails: [],
        isLoading: false,
        
        fetchDetails(folio) {
            this.isLoading = true;
            this.currentFolio = folio;
            this.saleDetails = [];
            this.showDetailModal = true;
            
            const url = '{{ route('ff.reports.api.saleDetails', ['folio' => '__FOLIO__']) }}'.replace('__FOLIO__', folio);
            
            fetch(url)
            .then(response => response.json())
            .then(data => {
                this.saleDetails = data.items;
            })
            .catch(error => {
                console.error('Error al cargar los detalles:', error);
                alert('Error al cargar los detalles de la venta.');
            })
            .finally(() => {
                this.isLoading = false;
            });
        },
        
        formatCurrency(value) {
            if (typeof value === 'string') {
                value = parseFloat(value.replace(/[^0-9.-]+/g,''));
            }
            if (isNaN(value)) return '$0.00';
            return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
        },
        
        clearFilter() {
            window.location = '{{ route('ff.reports.transactions') }}';
        },
        
        clearVendedor() {
            document.getElementById('vendedor_id').value = '';
            document.getElementById('filter-form').submit();
        }
    }">
        <x-slot name="header">
            <div class="flex flex-col md:flex-row justify-between items-center"> 
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Reportes: Transacciones de Venta') }}
                </h2>
                <a href="{{ route('ff.reports.index') }}"
                class="inline-flex items-center px-6 py-2 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest bg-[#2c3856] hover:bg-[#ff9c00] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff9c00] shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 ease-in-out">
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Volver a "Reportes"
                </a>
            </div>                                                   
        </x-slot>

        <div class="py-12">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Historial de Ventas por Folio
                    </h3>

                    <form method="GET" action="{{ route('ff.reports.transactions') }}" id="filter-form" class="mb-6 p-4 border rounded-lg bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">Buscar (Folio, Cliente, Surtidor)</label>
                                <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Ej: 1200 o Juan Pérez" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="vendedor_id" class="block text-sm font-medium text-gray-700">Vendedor</label>
                                <div class="relative">
                                    <select name="vendedor_id" id="vendedor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">-- Todos --</option>
                                        @foreach ($vendedores as $vendedor)
                                            <option value="{{ $vendedor->id }}" @if ($userIdFilter == $vendedor->id) selected @endif>
                                                {{ $vendedor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($userIdFilter)
                                        <button type="button" @click="clearVendedor()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-red-500">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Fecha Desde</label>
                                <input type="datetime-local" name="start_date" id="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">Fecha Hasta</label>
                                <input type="datetime-local" name="end_date" id="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div class="mt-4 flex space-x-3 justify-end">
                            @if ($userIdFilter || $search || $startDate || $endDate)
                                <button type="button" @click="clearFilter()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <i class="fas fa-filter-slash mr-2"></i> Limpiar Filtros
                                </button>
                            @endif
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-search mr-2"></i> Aplicar Filtros
                            </button>
                        </div>
                    </form>

                    @if ($sales->isEmpty())
                        <p class="text-gray-500">No se encontraron ventas registradas con los filtros aplicados.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folio</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendedor</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ítems</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unidades Total</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Total</th>
                                        <th class="relative px-6 py-3">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($sales as $sale)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                                                #{{ $sale->folio }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $sale->created_at->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $sale->user->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $sale->client_name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">
                                                {{ $sale->total_items }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-800 font-medium">
                                                {{ number_format($sale->total_units) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-700 font-bold">
                                                {{ '$' . number_format($sale->total_value, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                
                                                <button @click="fetchDetails({{ $sale->folio }})" class="text-blue-600 hover:text-blue-900 text-xs font-semibold">
                                                    Ver Ítems
                                                </button>

                                                @php
                                                    $firstMovement = \App\Models\ffInventoryMovement::where('folio', $sale->folio)->where('quantity', '<', 0)->first();
                                                    $movementId = $firstMovement->id ?? null;
                                                @endphp

                                                @if ($movementId)
                                                    <form action="{{ route('ff.reports.reprintReceipt', $movementId) }}" method="POST" target="_blank" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-indigo-600 hover:text-indigo-900 text-xs font-semibold">
                                                            Reimprimir Recibo
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $sales->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
        
        <div x-show="showDetailModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showDetailModal" @click="showDetailModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showDetailModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-headline">
                            Detalle de Venta: Folio #<span x-text="currentFolio"></span>
                        </h3>
                        
                        <div class="mt-4">
                            <template x-if="isLoading">
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-spinner fa-spin text-xl mr-2"></i> Cargando detalles...
                                </div>
                            </template>
                            
                            <template x-if="!isLoading && saleDetails.length > 0">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cant.</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">P. Unitario</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="item in saleDetails" :key="item.sku">
                                                <tr>
                                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900" x-text="item.sku"></td>
                                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500" x-text="item.description"></td>
                                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-800" x-text="item.quantity"></td>
                                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-800" x-text="item.unit_price"></td>
                                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-green-700 font-semibold" x-text="item.total_price"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="px-4 py-2 text-right text-sm font-bold text-gray-900">Total Venta:</td>
                                                <td class="px-4 py-2 text-right text-sm font-bold text-green-700" x-text="formatCurrency(saleDetails.reduce((sum, item) => sum + parseFloat(item.total_price.replace(/[^0-9.-]+/g,'')), 0))"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </template>
                            <template x-if="!isLoading && saleDetails.length === 0">
                                <div class="text-center py-8 text-gray-500">
                                    No se encontraron ítems para este folio.
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="showDetailModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>