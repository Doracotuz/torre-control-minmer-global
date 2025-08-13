<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Datos Originales de <span class="text-yellow-600">{{ $order->so_number }}</span></h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('customer-service.orders.update-original', $order) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 p-4 mb-6">
                        <p class="font-bold">Nota:</p>
                        <p>Esta sección es para modificar los datos que fueron cargados originalmente en el pedido.</p>
                    </div>
                    @if ($errors->any())<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><p><b>Error:</b> {{ $errors->first() }}</p></div>@endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label for="so_number" class="block text-sm font-medium text-gray-700">Número de SO</label><input type="text" name="so_number" value="{{ old('so_number', $order->so_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                        <div><label for="purchase_order" class="block text-sm font-medium text-gray-700">Orden de Compra</label><input type="text" name="purchase_order" value="{{ old('purchase_order', $order->purchase_order) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700">Razón Social</label>
                            <div x-data="{ open: false, query: '{{ old('customer_name', $order->customer_name) }}', selected: '{{ old('customer_name', $order->customer_name) }}' }" @click.away="open = false" class="relative">
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
                            <label for="channel" class="block text-sm font-medium text-gray-700">Canal</label>
                            <select name="channel" id="channel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Seleccione un canal</option>
                                @foreach ($channels as $channel)
                                    <option value="{{ $channel }}" {{ old('channel', $order->channel) == $channel ? 'selected' : '' }}>{{ $channel }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="origin_warehouse" class="block text-sm font-medium text-gray-700">Almacén Origen</label>
                            <div x-data="{ open: false, query: '{{ old('origin_warehouse', $order->origin_warehouse) }}', selected: '{{ old('origin_warehouse', $order->origin_warehouse) }}' }" @click.away="open = false" class="relative">
                                <input type="hidden" name="origin_warehouse" x-model="selected" required>
                                <input type="text" x-model="query" @focus="open = true" @input="open = true" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Buscar almacén..." required>
                                <ul x-show="open" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="warehouse in {{ json_encode($warehouses) }}" :key="warehouse.id">
                                        <li @click="selected = warehouse.name; query = warehouse.name; open = false" x-show="warehouse.name.toLowerCase().includes(query.toLowerCase())" class="px-4 py-2 cursor-pointer hover:bg-gray-100" x-text="warehouse.name"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <div><label for="shipping_address" class="block text-sm font-medium text-gray-700">Dirección de Envío</label><input type="text" name="shipping_address" value="{{ old('shipping_address', $order->shipping_address) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                        <div><label for="total_bottles" class="block text-sm font-medium text-gray-700">Total Botellas</label><input type="number" name="total_bottles" value="{{ old('total_bottles', $order->total_bottles) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                        <div><label for="total_boxes" class="block text-sm font-medium text-gray-700">Total Cajas</label><input type="number" name="total_boxes" value="{{ old('total_boxes', $order->total_boxes) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                        <div><label for="subtotal" class="block text-sm font-medium text-gray-700">Subtotal</label><input type="number" step="0.01" name="subtotal" value="{{ old('subtotal', $order->subtotal) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                    </div>
                    
                    <div class="flex justify-end gap-4 mt-8">
                        <a href="{{ route('customer-service.orders.show', $order) }}" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>