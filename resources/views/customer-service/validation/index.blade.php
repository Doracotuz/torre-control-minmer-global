<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-[#2c3856] leading-tight">Módulo de Validación de UPC</h2>
    </x-slot>

    <div x-data="{ openModal: false, selectedOrder: null }" class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white p-6 rounded-2xl shadow-xl mb-6">
                <form method="GET" action="{{ route('customer-service.validation.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <input type="text" name="search" placeholder="Buscar por SO..." value="{{ request('search') }}" class="rounded-md border-gray-300 shadow-sm">
                    <select name="customer_name" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">Todos los Clientes</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer }}" {{ request('customer_name') == $customer ? 'selected' : '' }}>{{ $customer }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="rounded-md border-gray-300 shadow-sm">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="rounded-md border-gray-300 shadow-sm">
                    <button type="submit" class="px-4 py-2 bg-[#2c3856] text-white rounded-md">Filtrar</button>
                </form>
                <div class="mt-4 flex items-center gap-4">
                    <a href="{{ route('customer-service.validation.template') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold">Descargar Plantilla</a>
                    <button @click="document.getElementById('importModal').classList.remove('hidden')" class="px-4 py-2 bg-teal-600 text-white rounded-md text-sm font-semibold">Importar CSV</button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">SO</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Cliente</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Fecha Creación</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Items</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($orders as $order)
                            <tr>
                                <td class="py-3 px-4">{{ $order->so_number }}</td>
                                <td class="py-3 px-4">{{ $order->customer_name }}</td>
                                <td class="py-3 px-4">{{ $order->creation_date->format('d/m/Y') }}</td>
                                <td class="py-3 px-4">{{ $order->details->count() }}</td>
                                <td class="py-3 px-4">
                                    <button @click="openModal = true; selectedOrder = {{ json_encode($order) }}" class="px-3 py-1 bg-[#ff9c00] text-white rounded-md text-xs font-semibold">Validar UPCs</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 px-4 text-center text-gray-500">No hay pedidos pendientes de validación.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        </div>

        <!-- Modal de Validación -->
        <div x-show="openModal" @keydown.escape.window="openModal = false" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4" style="display: none;">
            <div @click.away="openModal = false" class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-3xl">
                <form action="{{ route('customer-service.validation.store') }}" method="POST">
                    @csrf
                    <h3 class="text-xl font-bold text-[#2c3856] mb-4">Validar UPCs para SO: <span x-text="selectedOrder?.so_number"></span></h3>
                    <div class="max-h-[60vh] overflow-y-auto pr-4">
                        <table class="min-w-full">
                            <thead class="border-b sticky top-0 bg-white">
                                <tr>
                                    <th class="text-left py-2 px-3 text-sm font-semibold text-gray-600">SKU</th>
                                    <th class="text-left py-2 px-3 text-sm font-semibold text-gray-600">Descripción</th>
                                    <th class="text-left py-2 px-3 text-sm font-semibold text-gray-600">UPC</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="selectedOrder">
                                    <template x-for="detail in selectedOrder.details" :key="detail.id">
                                        <tr class="border-b">
                                            <td class="py-2 px-3" x-text="detail.sku"></td>
                                            <td class="py-2 px-3" x-text="detail.product?.description || 'N/A'"></td>
                                            <td class="py-2 px-3">
                                                <input type="text" :name="`upcs[${detail.id}]`" :value="detail.upc?.upc || ''" placeholder="Ingresar UPC o dejar vacío para usar SKU" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6 flex justify-end gap-4">
                        <button type="button" @click="openModal = false" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-[#ff9c00] text-white rounded-md">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Importación -->
    <div id="importModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md">
            <h3 class="text-xl font-bold text-[#2c3856] mb-4">Importar Archivo de UPCs</h3>
            <form action="{{ route('customer-service.validation.importCsv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="csv_file" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <div class="mt-6 flex justify-end gap-4">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-md">Procesar Archivo</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
