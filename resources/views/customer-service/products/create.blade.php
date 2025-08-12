<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Añadir Nuevo Producto') }}</h2>
    </x-slot>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('customer-service.products.store') }}" method="POST" x-data="{ sku: '{{ old('sku', '') }}' }">
                    @csrf
                    @if ($errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            <p class="font-bold">Hay errores:</p>
                            <ul class="mt-2 list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700">SKU (Único)</label>
                            <input type="text" name="sku" id="sku" x-model="sku" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Tipo (Automático)</label>
                            <input type="text" id="type" :value="sku.startsWith('5') || sku.startsWith('2') ? 'Promocional' : 'Producto'" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100">
                        </div>
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea name="description" id="description" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea>
                        </div>
                        <div>
                            <label for="packaging_factor" class="block text-sm font-medium text-gray-700">F. Empaque</label>
                            <input type="number" name="packaging_factor" id="packaging_factor" value="{{ old('packaging_factor') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="cs_brand_id" class="block text-sm font-medium text-gray-700">Marca</label>
                            <select id="select-brand" name="cs_brand_id" required placeholder="Busca o crea una marca...">
                                <option value="">Selecciona una marca</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('cs_brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-4 mt-8">
                        <a href="{{ route('customer-service.products.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new TomSelect('#select-brand',{
                create: true,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        });
    </script>

</x-app-layout>
