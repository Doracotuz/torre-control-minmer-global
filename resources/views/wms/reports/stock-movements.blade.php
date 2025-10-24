<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-y-4">
            <div class="flex items-center space-x-3">
                <h2 class="font-bold text-3xl text-gray-800 leading-tight tracking-tight">
                    Historial de Movimientos
                </h2>
            </div>

            <a href="{{ route('wms.reports.stock-movements.export', request()->query()) }}" 
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-lg shadow-md hover:shadow-lg hover:from-emerald-600 hover:to-green-700 transition-all duration-300 ease-in-out transform hover:-translate-y-px">
                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span class="text-sm font-semibold tracking-wide">Exportar a CSV</span>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-gradient-to-br from-white to-gray-50 p-6 rounded-2xl shadow-lg border border-gray-200">
                <form id="filters-form" action="{{ route('wms.reports.stock-movements') }}" method="GET">
                     <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-x-6 gap-y-4 items-end">
                        <div>
                            <label for="start_date" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Desde</label>
                            <input type="date" id="start_date" name="start_date" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3" value="{{ request('start_date') }}">
                        </div>
                        <div>
                            <label for="end_date" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Hasta</label>
                            <input type="date" id="end_date" name="end_date" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3" value="{{ request('end_date') }}">
                        </div>
                        <div>
                            <label for="sku" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">SKU</label>
                            <input type="text" id="sku" name="sku" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3" placeholder="Buscar SKU..." value="{{ request('sku') }}">
                        </div>
                        <div>
                            <label for="lpn" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">LPN</label>
                            <input type="text" id="lpn" name="lpn" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3" placeholder="Buscar LPN..." value="{{ request('lpn') }}">
                        </div>
                        <div>
                            <label for="movement_type" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tipo Movimiento</label>
                            <select id="movement_type" name="movement_type" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 appearance-none bg-white pr-8 bg-no-repeat" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.5em 1.5em;">
                                <option value="">Todos los Tipos</option>
                                @foreach($movementTypes as $type)
                                    <option value="{{ $type }}" @selected(request('movement_type') == $type)>{{ Str::title(str_replace('-', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pt-5">
                            <a href="{{ route('wms.reports.stock-movements') }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow-sm hover:bg-gray-300 text-sm font-semibold transition duration-150 ease-in-out" title="Limpiar filtros">
                                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="space-y-6">
                @forelse ($movements as $mov)
                    @php
                        $isPositive = $mov->quantity > 0;
                        $iconClass = $isPositive ? 'fa-arrow-down text-green-500' : 'fa-arrow-up text-red-500';
                        $bgColor = $isPositive ? 'bg-green-50' : ($mov->quantity < 0 ? 'bg-red-50' : 'bg-blue-50');
                        $borderColor = $isPositive ? 'border-green-200' : ($mov->quantity < 0 ? 'border-red-200' : 'border-blue-200');
                        $textColor = $isPositive ? 'text-green-700' : ($mov->quantity < 0 ? 'text-red-700' : 'text-blue-700');

                        if (Str::contains($mov->movement_type, 'AJUSTE')) {
                            $iconClass = 'fa-cogs text-yellow-600';
                            $bgColor = 'bg-yellow-50';
                            $borderColor = 'border-yellow-200';
                            $textColor = 'text-yellow-700';
                        } elseif (Str::contains($mov->movement_type, 'TRANSFER')) {
                            $iconClass = $isPositive ? 'fa-sign-in-alt text-blue-500' : 'fa-sign-out-alt text-purple-500';
                            $bgColor = $isPositive ? 'bg-blue-50' : 'bg-purple-50';
                            $borderColor = $isPositive ? 'border-blue-200' : 'border-purple-200';
                            $textColor = $isPositive ? 'text-blue-700' : 'text-purple-700';
                        }
                    @endphp

                    <div class="flex items-start space-x-4 p-5 {{ $bgColor }} rounded-xl shadow-md border {{ $borderColor }} hover:shadow-lg transition-shadow duration-300">
                        <div class="flex-shrink-0 flex flex-col items-center pt-1">
                            <span class="w-10 h-10 rounded-full flex items-center justify-center bg-white shadow border {{ $borderColor }}">
                                <i class="fas {{ $iconClass }} text-lg"></i>
                            </span>
                            <span class="mt-1.5 text-xs font-semibold {{ $textColor }} uppercase tracking-wider text-center" style="writing-mode: vertical-rl; text-orientation: mixed;">
                                {{ Str::limit(str_replace('-', ' ', $mov->movement_type), 15) }}
                            </span>
                        </div>

                        <div class="flex-grow grid grid-cols-1 md:grid-cols-5 gap-x-6 gap-y-2">
                            <div class="md:col-span-2">
                                <p class="text-sm font-bold text-gray-900 leading-tight">{{ $mov->product->name ?? 'Producto Desconocido' }}</p>
                                <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $mov->product->sku ?? 'N/A' }}</p>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-gray-700 font-mono">
                                    <span class="text-indigo-600">{{ $mov->palletItem->pallet->lpn ?? 'N/A' }}</span>
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <i class="fas fa-map-marker-alt text-red-500 mr-1 text-xs"></i>{{ $mov->location->code ?? 'N/A' }}
                                </p>
                            </div>

                            <div class="text-xs">
                                <p class="text-gray-600 font-semibold">
                                    PO: <span class="font-mono">{{ $mov->palletItem->pallet->purchaseOrder->po_number ?? 'N/A' }}</span>
                                </p>
                                <p class="text-gray-500 font-mono">
                                    Ped: {{ $mov->palletItem->pallet->purchaseOrder->pedimento_a4 ?? 'N/A' }}
                                </p>
                            </div>
                            
                            <div class="md:col-span-1 md:text-right flex flex-col justify-between items-end">
                                <span class="text-2xl font-bold {{ $textColor }}">
                                    {{ $mov->quantity > 0 ? '+' : '' }}{{ number_format($mov->quantity) }}
                                </span>
                                <div class="text-xs text-gray-500 mt-1 text-right">
                                    <p>{{ $mov->user->name ?? 'Sistema' }}</p>
                                    <p>{{ $mov->created_at->isoFormat('D MMM YYYY, h:mm a') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-20 bg-white rounded-2xl shadow-lg border border-gray-200">
                        <svg class="mx-auto h-16 w-16 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-semibold text-gray-800">No se encontraron movimientos</h3>
                        <p class="mt-1 text-sm text-gray-500">Intenta ajustar los filtros o verifica si se han registrado transacciones.</p>
                    </div>
                @endforelse
            </div>

            @if ($movements->hasPages())
                <div class="mt-10 px-6 py-4 bg-white rounded-2xl shadow-lg border border-gray-200">
                    {{ $movements->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>