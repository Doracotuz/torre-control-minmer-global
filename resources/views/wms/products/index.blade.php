<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catálogo de Productos</h2>
            
            <div class="flex items-center gap-2 mt-2 md:mt-0" x-data="{ showImportModal: false }">
                <a href="{{ route('wms.products.template') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md shadow-sm hover:bg-gray-700 text-sm">
                    <i class="fas fa-download mr-1"></i> Descargar Plantilla
                </a>
                <button @click="showImportModal = true" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 text-sm">
                    <i class="fas fa-upload mr-1"></i> Importar CSV
                </button>
                <a href="{{ route('wms.products.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700 text-sm">
                    Añadir Producto
                </a>

                <div x-show="showImportModal" @keydown.escape.window="showImportModal = false" class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50 p-4" x-cloak>
                    <div @click.away="showImportModal = false" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Importar Productos desde CSV</h3>
                        <form action="{{ route('wms.products.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mt-2">
                                <p class="text-sm text-gray-600 mb-2">Selecciona un archivo CSV (codificado en UTF-8).</p>
                                <input type="file" name="file" accept=".csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" @click="showImportModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</button>
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Subir e Importar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                    <p>{{ session('error') }}</p>
                    @if (session('import_errors'))
                        <ul class="mt-2 text-sm list-disc list-inside">
                            @foreach (session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
            @if (session('warning'))
                <div class="mb-6 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md" role="alert">
                    <p>{{ session('warning') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
                <form action="{{ route('wms.products.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700">Buscar (SKU, Nombre, UPC)</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm" 
                               placeholder="Escribe y presiona Enter...">
                    </div>
                    <div>
                        <label for="brand_id" class="block text-sm font-medium text-gray-700">Marca</label>
                        <select name="brand_id" id="brand_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm">
                            <option value="">-- Todas las Marcas --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="product_type_id" class="block text-sm font-medium text-gray-700">Tipo de Producto</label>
                        <select name="product_type_id" id="product_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm">
                            <option value="">-- Todos los Tipos --</option>
                            @foreach($productTypes as $type)
                                <option value="{{ $type->id }}" @selected(request('product_type_id') == $type->id)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4 flex justify-end items-center gap-3">
                        <a href="{{ route('wms.products.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Limpiar Filtros</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700 text-sm">
                            <i class="fas fa-search mr-1"></i> Buscar
                        </button>
                        <a href="{{ route('wms.products.export-csv', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 text-sm">
                            <i class="fas fa-file-excel mr-1"></i> Exportar Resultados
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marca</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volumen (m³)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Piezas/Caja</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($products as $product)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-700">{{ $product->sku }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $product->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $product->brand->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ number_format($product->volume, 4) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $product->pieces_per_case }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('wms.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                        <form action="{{ route('wms.products.destroy', $product) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No se encontraron productos con esos filtros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</x-app-layout>