<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="p-4"><svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paradas</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($rutas as $ruta)
                <tr>
                    <td class="p-4">
                        <input type="checkbox" class="route-checkbox rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00]" 
                               value="{{ $ruta->id }}"
                               x-model="selectedRutas">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ruta->nombre }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ruta->tipo_ruta }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ruta->paradas_count }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('rutas.plantillas.edit', $ruta) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                        <form action="{{ route('rutas.plantillas.duplicate', $ruta) }}" method="POST" class="inline ml-4" onsubmit="event.preventDefault(); duplicarRuta(this, '{{ $ruta->nombre }}');">@csrf<input type="hidden" name="new_name"><button type="submit" class="text-green-600 hover:text-green-900">Duplicar</button></form>
                        <form action="{{ route('rutas.plantillas.destroy', $ruta) }}" method="POST" class="inline ml-4" onsubmit="return confirm('¿Estás seguro?');">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No se encontraron rutas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginación --}}
<div class="mt-6">
    {{ $rutas->appends(request()->query())->links() }}
</div>
