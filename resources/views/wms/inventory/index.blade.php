<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-bold text-3xl text-gray-800 leading-tight tracking-tight">
                Dashboard de Inventario
            </h2>
            <div class="flex items-center space-x-2">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-blue-700 flex items-center">
                        <i class="fas fa-cogs mr-2"></i> Acciones <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg z-10 border" style="display: none;">
                        <a href="{{ route('wms.inventory.transfer.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-random fa-fw mr-2 text-gray-400"></i>Realizar Transferencia</a>
                        <a href="{{ route('wms.inventory.split.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-cut fa-fw mr-2 text-gray-400"></i>Hacer Split de Tarima</a>
                        <a href="{{ route('wms.inventory.pallet-info.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-search fa-fw mr-2 text-gray-400"></i>Consultar LPN</a>
                        <a href="{{ route('wms.inventory.adjustments.log') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-t"><i class="fas fa-history fa-fw mr-2 text-gray-400"></i>Registro de Ajustes</a>
                    </div>
                </div>
                <a href="{{ route('wms.inventory.export-csv', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-green-700">
                    <i class="fas fa-file-excel mr-2"></i> Exportar a CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="inventoryPage('{{ session('open_adjustment_modal_for_item') }}')">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-lg border"><div><span class="text-sm font-medium text-gray-500">Tarimas Totales</span><p class="text-3xl font-bold text-gray-800">{{ number_format($kpis['total_pallets']) }}</p></div></div>
                <div class="bg-white p-6 rounded-2xl shadow-lg border"><div><span class="text-sm font-medium text-gray-500">Unidades Totales</span><p class="text-3xl font-bold text-gray-800">{{ number_format($kpis['total_units']) }}</p></div></div>
                <div class="bg-white p-6 rounded-2xl shadow-lg border"><div><span class="text-sm font-medium text-gray-500">SKUs Únicos</span><p class="text-3xl font-bold text-gray-800">{{ number_format($kpis['total_skus']) }}</p></div></div>
                <div class="bg-white p-6 rounded-2xl shadow-lg border"><div><span class="text-sm font-medium text-gray-500">Ubic. Disponibles</span><p class="text-3xl font-bold text-gray-800">{{ number_format($kpis['available_locations']) }}</p></div></div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg border mb-8">
                <form id="filters-form" action="{{ route('wms.inventory.index') }}" method="GET">
                    <div class="flex flex-wrap gap-4 items-end">
                        <div class="flex-grow min-w-[150px]"><label class="text-xs text-gray-500">LPN</label><input type="text" name="lpn" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm text-sm" value="{{ request('lpn') }}"></div>
                        <div class="flex-grow min-w-[150px]"><label class="text-xs text-gray-500">Orden (PO)</label><input type="text" name="po_number" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm text-sm" value="{{ request('po_number') }}"></div>
                        <div class="flex-grow min-w-[150px]"><label class="text-xs text-gray-500">SKU</label><input type="text" name="sku" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm text-sm" value="{{ request('sku') }}"></div>
                        <div class="flex-grow min-w-[150px]"><label class="text-xs text-gray-500">Pedimento A4</label><input type="text" name="pedimento_a4" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm text-sm" value="{{ request('pedimento_a4') }}"></div>
                        <div class="flex-grow min-w-[150px]"><label class="text-xs text-gray-500">Ubicación</label><input type="text" name="location" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm text-sm" value="{{ request('location') }}" placeholder="Código o A-01-.."></div>
                        <div class="flex-grow min-w-[150px]"><label class="text-xs text-gray-500">Calidad</label><select name="quality_id" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm text-sm"><option value="">Toda Calidad</option>@foreach($qualities as $quality)<option value="{{ $quality->id }}" @selected(request('quality_id') == $quality->id)>{{ $quality->name }}</option>@endforeach</select></div>
                        <div class="flex-grow min-w-[150px]"><label class="text-xs text-gray-500">Desde</label><input type="date" name="start_date" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm text-sm" value="{{ request('start_date') }}"></div>
                        <div class="flex-grow min-w-[150px]"><label class="text-xs text-gray-500">Hasta</label><input type="date" name="end_date" onchange="this.form.submit()" class="w-full rounded-md border-gray-300 shadow-sm text-sm" value="{{ request('end_date') }}"></div>
                        <div class="flex items-center space-x-2 pt-4"><a href="{{ route('wms.inventory.index') }}" class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-center" title="Limpiar filtros">Limpiar</a></div>
                    </div>
                </form>
            </div>
            <div class="bg-white overflow-hidden rounded-2xl shadow-lg border">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">LPN / Ubicación</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Origen (PO) / Pedimento</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Contenido</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">Cantidad</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">Comprometido</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Recibido</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($pallets as $pallet)
                                @foreach ($pallet->items as $item)
                                <tr class="hover:bg-gray-50">
                                    @if ($loop->first)<td class="px-4 py-4 align-top" rowspan="{{ $pallet->items->count() }}"><p class="font-mono font-bold text-indigo-600">{{ $pallet->lpn }}</p><p class="text-sm text-gray-800"><i class="fas fa-map-marker-alt text-red-500 mr-1"></i> {{ $pallet->location ? "{$pallet->location->aisle}-{$pallet->location->rack}-{$pallet->location->shelf}-{$pallet->location->bin}" : 'N/A' }}</p></td><td class="px-4 py-4 align-top" rowspan="{{ $pallet->items->count() }}"><p class="font-mono">{{ $pallet->purchaseOrder->po_number ?? 'N/A' }}</p><p class="text-xs text-gray-500 font-mono">{{ $pallet->purchaseOrder->pedimento_a4 ?? 'N/A' }}</p></td>@endif
                                    <td class="px-4 py-4"><p class="font-semibold text-gray-900 text-sm">{{ $item->product->name ?? 'N/A' }}</p><p class="text-xs text-gray-500"><span class="font-mono">{{ $item->product->sku ?? '' }}</span> | {{ $item->quality->name ?? '' }}</p></td>
                                    <td class="px-4 py-4 text-center text-lg font-bold text-gray-900">{{ $item->quantity }}</td>
                                    <td class="px-2 py-4 text-center whitespace-nowrap text-sm text-red-600">
                                        @php
                                            $key = $item->product_id . '-' . $item->quality_id . '-' . $pallet->location_id;
                                        @endphp
                                        {{ $committedStock[$key] ?? 0 }}
                                    </td>                     
                                    @if ($loop->first)<td class="px-4 py-4 text-sm align-top" rowspan="{{ $pallet->items->count() }}"><p>{{ $pallet->user->name ?? 'N/A' }}</p><p class="text-gray-500 text-xs">{{ $pallet->updated_at->format('d/m/Y') }}</p></td><td class="px-4 py-4 text-center align-top" rowspan="{{ $pallet->items->count() }}"><button @click="openModal({{ json_encode($pallet) }})" class="text-indigo-600 hover:text-indigo-900" title="Ver Detalle Completo"><i class="fas fa-eye fa-lg"></i></button></td>@endif
                                </tr>
                                @endforeach
                            @empty
                                <tr><td colspan="6" class="text-center text-gray-500 py-16"><p>No se encontraron tarimas con los filtros aplicados.</p></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t">{{ $pallets->links() }}</div>
            </div>
        </div>

        <div x-show="modalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50" style="display: none;">
            <div @click.away="closeModal()" class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
                <template x-if="selectedPallet">
                    <div class="flex flex-col h-full">
                        <div class="p-6 border-b flex justify-between items-center"><h2 class="text-2xl font-bold text-gray-800">Detalle de LPN: <span class="font-mono text-indigo-600" x-text="selectedPallet.lpn"></span></h2><button @click="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button></div>
                        <div class="p-6 overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div><h3 class="font-bold text-lg text-gray-700 border-b pb-2">Información del Arribo</h3><dl class="mt-2 text-sm grid grid-cols-2 gap-x-4 gap-y-2"><dt class="font-semibold text-gray-500">Orden de Compra:</dt><dd x-text="selectedPallet.purchase_order?.po_number || 'N/A'"></dd><dt class="font-semibold text-gray-500">Contenedor:</dt><dd x-text="selectedPallet.purchase_order?.container_number || 'N/A'"></dd><dt class="font-semibold text-gray-500">Pedimento A4:</dt><dd class="font-mono" x-text="selectedPallet.purchase_order?.pedimento_a4 || 'N/A'"></dd><dt class="font-semibold text-gray-500">Pedimento G1:</dt><dd class="font-mono" x-text="selectedPallet.purchase_order?.pedimento_g1 || 'N/A'"></dd><dt class="font-semibold text-gray-500">Operador:</dt><dd x-text="selectedPallet.purchase_order?.operator_name || 'N/A'"></dd></dl></div>
                                <div><h3 class="font-bold text-lg text-gray-700 border-b pb-2">Información de la Tarima</h3><dl class="mt-2 text-sm grid grid-cols-2 gap-x-4 gap-y-2"><dt class="font-semibold text-gray-500">Ubicación Actual:</dt><dd x-text="selectedPallet.location ? `${selectedPallet.location.aisle}-${selectedPallet.location.rack}-${selectedPallet.location.shelf}-${selectedPallet.location.bin}` : 'N/A'"></dd><dt class="font-semibold text-gray-500">Última Interacción:</dt><dd class="font-bold" x-text="selectedPallet.last_action || 'N/A'"></dd><dt class="font-semibold text-gray-500">Realizada por:</dt><dd x-text="selectedPallet.user?.name || 'N/A'"></dd><dt class="font-semibold text-gray-500">Fecha Interacción:</dt><dd x-text="new Date(selectedPallet.updated_at).toLocaleString()"></dd></dl></div>
                            </div>
                            <div class="mt-6"><h3 class="font-bold text-lg text-gray-700 border-b pb-2">Contenido de la Tarima</h3><ul class="mt-2 space-y-3"><template x-for="item in selectedPallet.items" :key="item.id"><li class="p-3 bg-gray-50 rounded-md"><div class="flex justify-between items-center"><div><p class="font-semibold text-gray-900" x-text="item.product.name"></p><p class="text-xs text-gray-500"><span class="font-mono" x-text="item.product.sku"></span> | <strong class="text-indigo-700" x-text="item.quality.name"></strong></p></div><div class="flex items-center gap-4"><p class="font-bold text-xl text-gray-800" x-text="`x${item.quantity}`"></p>@if(Auth::user()->isSuperAdmin())<button @click="openAdjustmentModal(item)" class="px-2 py-1 bg-yellow-500 text-white rounded-md text-xs font-semibold hover:bg-yellow-600">Ajustar</button>@endif</div></div></li></template></ul></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="adjustmentModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-60" style="display: none;">
            <div @click.away="closeAdjustmentModal()" @click.stop class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
                <template x-if="itemToAdjust">
                    <form :action="`/wms/inventory/pallet-items/${itemToAdjust.id}/adjust`" method="POST">
                        @csrf
                        @if($errors->any() && session('open_adjustment_modal_for_item'))
                            <div class="m-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <strong class="font-bold">Error de validación:</strong>
                                <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                            </div>
                        @endif
                        <div class="p-6"><h2 class="text-xl font-bold text-gray-800">Ajustar Cantidad</h2><p class="text-sm text-gray-600 mt-2">Estás ajustando: <strong x-text="itemToAdjust.product.name"></strong> (<span class="font-mono" x-text="itemToAdjust.product.sku"></span>)</p><p class="text-sm text-gray-600">Cantidad Actual: <strong x-text="itemToAdjust.quantity"></strong></p><div class="mt-4"><label for="new_quantity" class="block font-medium">Nueva Cantidad</label><input type="number" name="new_quantity" id="new_quantity" min="0" :value="itemToAdjust.quantity" class="w-full rounded-md border-gray-300 mt-1" required></div><div class="mt-4"><label for="reason" class="block font-medium">Motivo del Ajuste</label><textarea name="reason" id="reason" rows="3" class="w-full rounded-md border-gray-300 mt-1" required placeholder="Ej: Conteo cíclico, producto dañado, etc."></textarea></div></div>
                        <div class="px-6 py-4 bg-gray-50 flex justify-end gap-4 rounded-b-2xl"><button type="button" @click="closeAdjustmentModal()" class="px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg">Cancelar</button><button type="submit" class="px-4 py-2 bg-yellow-600 text-white font-semibold rounded-lg">Guardar Ajuste</button></div>
                    </form>
                </template>
            </div>
        </div>
    </div>
    
    <script>
        function inventoryPage(failedItemId = null) {
            return {
                modalOpen: false, selectedPallet: null,
                adjustmentModalOpen: false, itemToAdjust: null,
                palletsOnPage: @json($pallets->items()),

                init() {
                    if (failedItemId) {
                        let targetPallet = null;
                        let targetItem = null;
                        for (const pallet of this.palletsOnPage) {
                            if (pallet && pallet.items) {
                                const foundItem = pallet.items.find(item => item.id == failedItemId);
                                if (foundItem) {
                                    targetPallet = pallet;
                                    targetItem = foundItem;
                                    break;
                                }
                            }
                        }
                        if (targetPallet && targetItem) {
                            this.openModal(targetPallet);
                            this.openAdjustmentModal(targetItem);
                        }
                    }
                },
                openModal(pallet) { this.selectedPallet = pallet; this.modalOpen = true; },
                closeModal() { this.modalOpen = false; this.selectedPallet = null; },
                openAdjustmentModal(item) { this.itemToAdjust = item; this.adjustmentModalOpen = true; },
                closeAdjustmentModal() { this.adjustmentModalOpen = false; this.itemToAdjust = null; },
            }
        }
        document.addEventListener('alpine:init', () => { Alpine.data('inventoryPage', inventoryPage); });
    </script>
</x-app-layout>