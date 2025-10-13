<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Crear Orden de Venta</h2></x-slot>
    <div class="py-12" x-data="soForm()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('wms.sales-orders.store') }}" method="POST">
                @csrf
                <div class="bg-white p-8 rounded-lg shadow-xl space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label for="so_number">Nº de Orden de Venta (SO)</label><input type="text" name="so_number" required class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div><label for="invoice_number">Nº de Factura (Opcional)</label><input type="text" name="invoice_number" class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div><label for="customer_name">Nombre del Cliente</label><input type="text" name="customer_name" required class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div><label for="order_date">Fecha de la Orden</label><input type="date" name="order_date" value="{{ now()->format('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300"></div>
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="font-semibold text-lg">Productos</h3>
                        <div class="space-y-3 mt-2">
                            <template x-for="(line, index) in lines" :key="index">
                                <div class="flex items-center space-x-3">
                                    <select :name="`lines[${index}][product_id]`" class="w-1/2 rounded-md border-gray-300">
                                        <option value="">Seleccione Producto...</option>
                                        @foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>@endforeach
                                    </select>
                                    <input type="number" :name="`lines[${index}][quantity_ordered]`" placeholder="Cantidad" min="1" required class="w-1/4 rounded-md border-gray-300">
                                    <button type="button" @click="removeLine(index)" class="text-red-500 hover:text-red-700 p-2">&times; Quitar</button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="addLine()" class="mt-4 px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-md">+ Añadir Producto</button>
                    </div>

                    <div class="mt-6 flex justify-end"><button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md">Guardar Orden de Venta</button></div>
                </div>
            </form>
        </div>
    </div>
    @push('scripts')
    <script>
        function soForm() {
            return {
                lines: [{ product_id: '', quantity_ordered: '' }],
                addLine() { this.lines.push({ product_id: '', quantity_ordered: '' }); },
                removeLine(index) { this.lines.splice(index, 1); }
            }
        }
    </script>
    @endpush
</x-app-layout>