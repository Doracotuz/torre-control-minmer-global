<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Añadir Nuevo Producto</h2></x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('wms.products.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                            <input type="text" name="sku" id="sku" value="{{ old('sku') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500">
                            @error('sku') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="upc" class="block text-sm font-medium text-gray-700">UPC (Opcional)</label>
                            <input type="text" name="upc" id="upc" value="{{ old('upc', $product->upc ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>                        
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Producto</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500">
                        </div>
                        
                        {{-- Selectores para catálogos --}}
                        <div>
                            <label for="brand_id" class="block text-sm font-medium text-gray-700">Marca</label>
                            <select name="brand_id" id="brand_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500">
                                <option value="">-- Sin Marca --</option>
                                @foreach($brands as $brand) <option value="{{ $brand->id }}">{{ $brand->name }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="product_type_id" class="block text-sm font-medium text-gray-700">Tipo de Producto</label>
                            <select name="product_type_id" id="product_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500">
                                <option value="">-- Sin Tipo --</option>
                                @foreach($productTypes as $type) <option value="{{ $type->id }}">{{ $type->name }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="unit_of_measure" class="block text-sm font-medium text-gray-700">Unidad de Empaque</label>
                            <input type="text" name="unit_of_measure" id="unit_of_measure" value="{{ old('unit_of_measure') }}" required placeholder="Ej: Caja, Pieza, Pallet" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="pieces_per_case" class="block text-sm font-medium text-gray-700">Piezas por Caja</label>
                            <input type="number" name="pieces_per_case" id="pieces_per_case" 
                                value="{{ old('pieces_per_case', $product->pieces_per_case ?? 1) }}" 
                                min="1" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>                        
                        
                        {{-- Dimensiones --}}
                        <div class="md:col-span-2">
                            <p class="block text-sm font-medium text-gray-700">Dimensiones y Peso</p>
                            <div class="grid grid-cols-4 gap-4 mt-1">
                                <div>
                                    <label for="length" class="text-xs text-gray-600">Largo (cm)</label>
                                    <input type="number" name="length" id="length" value="{{ old('length') }}" step="0.01" class="block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                 <div>
                                    <label for="width" class="text-xs text-gray-600">Ancho (cm)</label>
                                    <input type="number" name="width" id="width" value="{{ old('width') }}" step="0.01" class="block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                 <div>
                                    <label for="height" class="text-xs text-gray-600">Alto (cm)</label>
                                    <input type="number" name="height" id="height" value="{{ old('height') }}" step="0.01" class="block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                 <div>
                                    <label for="weight" class="text-xs text-gray-600">Peso (kg)</label>
                                    <input type="number" name="weight" id="weight" value="{{ old('weight') }}" step="0.01" class="block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('wms.products.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md mr-4 hover:bg-gray-300">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>