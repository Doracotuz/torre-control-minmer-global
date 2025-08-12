<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">F. Empaque</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marca</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($products as $product)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->sku }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($product->description, 50) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->packaging_factor }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->brand->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->type == 'Producto' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $product->type }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('customer-service.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                        <form action="{{ route('customer-service.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 ml-4">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No se encontraron productos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $products->withQueryString()->links() }}
</div>
