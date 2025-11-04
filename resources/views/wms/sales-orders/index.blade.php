<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4"> {{-- Añadido gap-4 --}}
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Órdenes de Venta
            </h2>
            <div class="flex items-center space-x-2 mt-4 md:mt-0">
                <a href="{{ route('wms.sales-orders.export-csv', request()->query()) }}" {{-- Pasa los filtros actuales --}}
                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exportar CSV
                </a>
                <a href="{{ route('wms.sales-orders.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Crear Nueva SO
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                
                <div class="bg-white p-6 shadow-lg rounded-xl flex items-center space-x-4">
                    <div class="flex-shrink-0 p-3 bg-indigo-100 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500 truncate">Total de Órdenes</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $kpis['total'] }}</div>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-lg rounded-xl flex items-center space-x-4">
                    <div class="flex-shrink-0 p-3 bg-yellow-100 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500 truncate">Pendientes</div>
                        <div class="mt-1 text-3xl font-semibold text-yellow-600">{{ $kpis['pending'] }}</div>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-lg rounded-xl flex items-center space-x-4">
                    <div class="flex-shrink-0 p-3 bg-blue-100 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500 truncate">En Surtido</div>
                        <div class="mt-1 text-3xl font-semibold text-blue-600">{{ $kpis['picking'] }}</div>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-lg rounded-xl flex items-center space-x-4">
                    <div class="flex-shrink-0 p-3 bg-green-100 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500 truncate">Empacadas</div>
                        <div class="mt-1 text-3xl font-semibold text-green-600">{{ $kpis['packed'] }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-md mb-6">
                <form action="{{ route('wms.sales-orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-center">
                    <div>
                        <label for="warehouse_id" class="block text-xs font-medium text-gray-500 mb-1">Almacén</label>
                        <select name="warehouse_id" id="warehouse_id" class="border-gray-200 rounded-lg shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-full" onchange="this.form.submit()">
                            <option value="">-- Todos los Almacenes --</option>
                            
                            {{-- Estas variables ($warehouses y $warehouseId) las pasa el controlador actualizado --}}
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @if(request('warehouse_id') == $warehouse->id) selected @endif>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>                    
                    <div>
                        <label for="so_number" class="block text-xs font-medium text-gray-500 mb-1">SO</label>
                        <input type="text" name="so_number" placeholder="Buscar por Nº SO..." value="{{ request('so_number') }}" class="border-gray-200 rounded-lg shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="invoice_number" class="block text-xs font-medium text-gray-500 mb-1">Factura</label>
                        <input type="text" name="invoice_number" placeholder="Buscar por Nº Factura..." value="{{ request('invoice_number') }}" class="border-gray-200 rounded-lg shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="customer_name" class="block text-xs font-medium text-gray-500 mb-1">Cliente</label>
                        <input type="text" name="customer_name" placeholder="Buscar por Cliente..." value="{{ request('customer_name') }}" class="border-gray-200 rounded-lg shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="status" class="block text-xs font-medium text-gray-500 mb-1">Estatus</label>
                        <select name="status" class="border-gray-200 rounded-lg shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Todos los Estatus</option>
                            <option value="Pending" @selected(request('status') == 'Pending')>Pendiente</option>
                            <option value="Picking" @selected(request('status') == 'Picking')>En Surtido</option>
                            <option value="Packed" @selected(request('status') == 'Packed')>Empacado</option>
                            <option value="Cancelled" @selected(request('status') == 'Cancelled')>Cancelado</option>
                        </select>
                    </div>
                    <div>
                        <label for="start_date" class="block text-xs font-medium text-gray-500 mb-1">Desde:</label>
                        <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="input-filter w-full">
                    </div>
                    <div>
                        <label for="end_date" class="block text-xs font-medium text-gray-500 mb-1">Hasta:</label>
                        <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="input-filter w-full">
                    </div>                    
                    <div class="flex items-center space-x-2">
                        <button type="submit" class="w-full inline-flex justify-center px-4 py-2.5 bg-indigo-600 text-white font-medium rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                            Filtrar
                        </button>
                        <a href="{{ route('wms.sales-orders.index') }}" class="w-full text-center px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <div class="space-y-4">
                @forelse ($salesOrders as $so)
                    <a href="{{ route('wms.sales-orders.show', $so) }}" class="block bg-white p-6 rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-shadow duration-200 group">
                        <div class="flex justify-between items-start">
                            
                            <div class="flex-1">
                                <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-2">
                                    <span class="text-lg font-semibold text-indigo-700 group-hover:text-indigo-900 transition-colors">
                                        {{ $so->so_number }}
                                    </span>
                                    
                                    @php
                                        $statusLabel = match($so->status) {
                                            'Pending' => 'Pendiente',
                                            'Picking' => 'En Surtido',
                                            'Packed' => 'Empacado',
                                            'Cancelled' => 'Cancelado',
                                            default => $so->status,
                                        };
                                    @endphp
                                    <span @class([
                                        'px-3 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full mt-2 sm:mt-0',
                                        'bg-yellow-100 text-yellow-800' => $so->status == 'Pending',
                                        'bg-blue-100 text-blue-800' => $so->status == 'Picking',
                                        'bg-green-100 text-green-800' => $so->status == 'Packed',
                                        'bg-red-100 text-red-800' => $so->status == 'Cancelled',
                                        'bg-gray-100 text-gray-800' => !in_array($so->status, ['Pending', 'Picking', 'Packed', 'Cancelled']),
                                    ])>
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-700 font-medium">{{ $so->customer_name }}</p>
                                <p class="text-sm text-gray-500">{{ $so->invoice_number ?? 'Sin Factura' }}</p>

                                <div class="flex items-center flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500 mt-4">
                                    <span class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        <strong class="text-gray-700 mr-1">{{ (int)$so->lines_sum_quantity_ordered }}</strong> Unidades
                                    </span>
                                    <span class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        {{ $so->lines_count }} Items
                                    </span>
                                    <span class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $so->order_date->format('d/m/Y') }}
                                    </span>
                                    <span class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ $so->user->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="pl-4 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-300 group-hover:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>

                        </div>
                    </a>
                @empty
                    <div class="bg-white p-12 rounded-xl shadow-md text-center">
                        <div class="flex flex-col items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z" />
                            </svg>
                            <p class="mt-3 font-semibold text-lg text-gray-700">No se encontraron órdenes de venta</p>
                            <p class="text-sm text-gray-500">Intenta ajustar tus filtros o crea una nueva orden.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $salesOrders->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
<style>
    .input-filter {
        border-color: #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        font-size: 0.875rem;
        padding-top: 0.625rem;
        padding-bottom: 0.625rem;
    }
    .input-filter:focus {
        border-color: #a5b4fc;
        --tw-ring-color: rgba(165, 180, 252, 0.5);
        box-shadow: 0 0 0 3px var(--tw-ring-color);
        outline: 2px solid transparent;
        outline-offset: 2px;
    }
    .button-primary {
        display: inline-flex; justify-content: center; padding: 0.625rem 1rem; background-color: #4f46e5;
        color: white; font-weight: 500; border-radius: 0.5rem;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: background-color 150ms ease-in-out;
    }
    .button-primary:hover { background-color: #4338ca; }
    .button-secondary {
        display: inline-flex; justify-content: center; text-align: center; padding: 0.625rem 1rem; color: #374151;
        background-color: white; border: 1px solid #d1d5db; font-weight: 500; border-radius: 0.5rem;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: background-color 150ms ease-in-out;
    }
    .button-secondary:hover { background-color: #f9fafb; }
</style>    
</x-app-layout>