<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Nuevo Pedido Manualmente</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('customer-service.orders.store') }}" method="POST">
                    @csrf
                    @if ($errors->any())<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><p><b>Error:</b> {{ $errors->first() }}</p></div>@endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label for="so_number" class="block text-sm font-medium text-gray-700">Número de SO</label>
                            <input type="text" name="so_number" id="so_number" value="{{ old('so_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <div>
                            <label for="purchase_order" class="block text-sm font-medium text-gray-700">Orden de Compra</label>
                            <input type="text" name="purchase_order" id="purchase_order" value="{{ old('purchase_order') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700">Razón Social</label>
                            <div x-data="{ open: false, query: '', selected: '' }" @click.away="open = false" class="relative">
                                <input type="hidden" name="customer_name" x-model="selected" required>
                                <input type="text" x-model="query" @focus="open = true" @input="open = true" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Buscar cliente..." required>
                                <ul x-show="open" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="customer in {{ json_encode($customers) }}" :key="customer.id">
                                        <li @click="selected = customer.name; query = customer.name; open = false" x-show="customer.name.toLowerCase().includes(query.toLowerCase())" class="px-4 py-2 cursor-pointer hover:bg-gray-100" x-text="customer.name"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <div>
                            <label for="creation_date" class="block text-sm font-medium text-gray-700">Fecha de Creación</label>
                            <input type="date" name="creation_date" id="creation_date" value="{{ old('creation_date', now()->toDateString()) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        
                        <div>
                            <label for="origin_warehouse" class="block text-sm font-medium text-gray-700">Almacén Origen</label>
                            <div x-data="{ open: false, query: '', selected: '' }" @click.away="open = false" class="relative">
                                <input type="hidden" name="origin_warehouse" x-model="selected" required>
                                <input type="text" x-model="query" @focus="open = true" @input="open = true" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Buscar almacén..." required>
                                <ul x-show="open" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="warehouse in {{ json_encode($warehouses) }}" :key="warehouse.id">
                                        <li @click="selected = warehouse.name; query = warehouse.name; open = false" x-show="warehouse.name.toLowerCase().includes(query.toLowerCase())" class="px-4 py-2 cursor-pointer hover:bg-gray-100" x-text="warehouse.name"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <div>
                            <label for="channel" class="block text-sm font-medium text-gray-700">Canal</label>
                            <select name="channel" id="channel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Seleccione un canal</option>
                                @foreach ($channels as $channel)
                                    <option value="{{ $channel }}" {{ old('channel') == $channel ? 'selected' : '' }}>{{ $channel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="shipping_address" class="block text-sm font-medium text-gray-700">Dirección de Envío</label>
                            <input type="text" name="shipping_address" id="shipping_address" value="{{ old('shipping_address') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                    </div>

                    <h4 class="text-lg font-semibold text-gray-800 mt-8 mb-4">Detalles de SKUs</h4>
                    <div x-data="{ 
                        details: [{ sku: '', quantity: 1, subtotal: 0 }], 
                        products: {{ json_encode($products) }},
                        calculateTotalSubtotal() {
                            let total = 0;
                            this.details.forEach(detail => {
                                total += parseFloat(detail.subtotal) || 0;
                            });
                            return total.toFixed(2);
                        }
                    }">
                        <template x-for="(detail, index) in details" :key="index">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                                <div class="md:col-span-2">
                                    <label :for="`details[${index}][sku]`" class="block text-sm font-medium text-gray-700">SKU</label>
                                    <div x-data="{ open: false, query: detail.sku }" @click.away="open = false" class="relative">
                                        <input type="hidden" :name="`details[${index}][sku]`" x-model="query" required>
                                        <input type="text" x-model="query" @focus="open = true" @input="open = true" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Buscar SKU..." required>
                                        <ul x-show="open" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                            <template x-for="product in products" :key="product.id">
                                                <li @click="query = product.sku; details[index].sku = product.sku; open = false" x-show="product.sku.toLowerCase().includes(query.toLowerCase())" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                                    <span x-text="product.sku"></span> - <span x-text="product.description"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                                <div>
                                    <label :for="`details[${index}][quantity]`" class="block text-sm font-medium text-gray-700">Cantidad</label>
                                    <input type="number" :name="`details[${index}][quantity]`" x-model.number="detail.quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="1" required>
                                </div>
                                <div>
                                    <label :for="`details[${index}][subtotal]`" class="block text-sm font-medium text-gray-700">Subtotal por SKU</label>
                                    <input type="number" step="0.01" :name="`details[${index}][subtotal]`" x-model.number="detail.subtotal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="0" required>
                                </div>
                                <div class="flex items-end">
                                    <button type="button" @click="details.splice(index, 1)" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-red-700" :disabled="details.length === 1">Eliminar</button>
                                </div>
                            </div>
                        </template>
                        <button type="button" @click="details.push({ sku: '', quantity: 1, subtotal: 0 })" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-blue-700 mt-4">
                            + Añadir SKU
                        </button>
                        <div class="mt-6 text-right text-lg font-bold text-gray-800">
                            Subtotal Total: $<span x-text="calculateTotalSubtotal()">0.00</span>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 mt-8">
                        <a href="{{ route('customer-service.orders.index') }}" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Guardar Pedido</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>