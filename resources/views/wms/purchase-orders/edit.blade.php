<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Editar Orden de Compra: <span class="font-mono text-indigo-600">{{ $purchaseOrder->po_number }}</span>
        </h2>
    </x-slot>
    
    <div class="py-12" x-data="poForm({ lines: {{ $purchaseOrder->lines->toJson() }} })">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('wms.purchase-orders.update', $purchaseOrder) }}" method="POST">
                @csrf
                @method('PUT')
                
                @if ($errors->any())<div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                
                <div class="bg-white p-8 rounded-lg shadow-xl space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label for="po_number">Nº de PO</label><input type="text" name="po_number" id="po_number" value="{{ old('po_number', $purchaseOrder->po_number) }}" required class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div><label for="expected_date">Fecha Esperada</label><input type="date" name="expected_date" id="expected_date" value="{{ old('expected_date', $purchaseOrder->expected_date) }}" required class="mt-1 block w-full rounded-md border-gray-300"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label for="container_number">Contenedor</label><input type="text" name="container_number" id="container_number" value="{{ old('container_number', $purchaseOrder->container_number) }}" class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div><label for="document_invoice">Documento o Factura</label><input type="text" name="document_invoice" id="document_invoice" value="{{ old('document_invoice', $purchaseOrder->document_invoice) }}" class="mt-1 block w-full rounded-md border-gray-300"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label for="pedimento_a4">Pedimento A4</label><input type="text" name="pedimento_a4" id="pedimento_a4" value="{{ old('pedimento_a4', $purchaseOrder->pedimento_a4) }}" class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div><label for="pedimento_g1">Pedimento G1</label><input type="text" name="pedimento_g1" id="pedimento_g1" value="{{ old('pedimento_g1', $purchaseOrder->pedimento_g1) }}" class="mt-1 block w-full rounded-md border-gray-300"></div>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h3 class="font-semibold text-lg">Productos</h3>
                        <div class="space-y-3 mt-2"><template x-for="(line, index) in lines" :key="index"><div class="flex items-center space-x-3"><select :name="`lines[${index}][product_id]`" x-model="line.product_id" class="w-1/2 rounded-md border-gray-300"><option value="">Seleccione...</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>@endforeach</select><input type="number" :name="`lines[${index}][quantity_ordered]`" x-model="line.quantity_ordered" placeholder="Cantidad" min="1" required class="w-1/4 rounded-md border-gray-300"><button type="button" @click="removeLine(index)" class="text-red-500 hover:text-red-700 p-2">&times; Quitar</button></div></template></div>
                        <button type="button" @click="addLine()" class="mt-4 px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-md">+ Añadir Producto</button>
                    </div>

                    <div class="mt-6 flex justify-end"><a href="{{ route('wms.purchase-orders.show', $purchaseOrder) }}" class="px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 mr-4">Cancelar</a><button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md">Guardar Cambios</button></div>
                </div>
            </form>
        </div>
    </div>
    <script>
        function poForm(data = {}) {
            return {
                lines: data.lines || [{ product_id: '', quantity_ordered: '' }],
                addLine() { this.lines.push({ product_id: '', quantity_ordered: '' }); },
                removeLine(index) { this.lines.splice(index, 1); }
            }
        }
    </script>
</x-app-layout>