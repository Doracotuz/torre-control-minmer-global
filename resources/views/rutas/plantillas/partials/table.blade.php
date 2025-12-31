<div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white/40">
    <table class="min-w-full divide-y divide-gray-200/50">
        <thead class="bg-[#2c3856]/5">
            <tr>
                <th class="p-4 w-10">
                    <svg class="w-5 h-5 text-[#2c3856]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </th>
                <th class="px-4 py-3 text-left text-xs font-bold text-[#2c3856] uppercase tracking-wider">Nombre</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-[#2c3856] uppercase tracking-wider">Tipo</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-[#2c3856] uppercase tracking-wider">Paradas</th>
                <th class="px-4 py-3 text-right text-xs font-bold text-[#2c3856] uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200/50">
            @forelse ($rutas as $ruta)
                <tr class="hover:bg-[#ff9c00]/10 transition-colors duration-200 group">
                    <td class="p-4">
                        <input type="checkbox" class="route-checkbox rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00] cursor-pointer" 
                               value="{{ $ruta->id }}"
                               x-model="selectedRutas">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="text-sm font-bold text-[#2c3856]">{{ $ruta->nombre }}</div>
                        <div class="text-xs text-gray-500">{{ $ruta->region ?? 'N/A' }}</div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $ruta->tipo_ruta == 'Entrega' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $ruta->tipo_ruta == 'Traslado' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $ruta->tipo_ruta == 'Importacion' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}
                        ">
                            {{ $ruta->tipo_ruta }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-medium text-gray-600">
                        {{ $ruta->paradas_count }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-3 opacity-70 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('rutas.plantillas.edit', $ruta) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('rutas.plantillas.duplicate', $ruta) }}" method="POST" class="inline" onsubmit="event.preventDefault(); duplicarRuta(this, '{{ $ruta->nombre }}');">
                                @csrf
                                <input type="hidden" name="new_name">
                                <button type="submit" class="text-green-600 hover:text-green-900" title="Duplicar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </form>
                            <form action="{{ route('rutas.plantillas.destroy', $ruta) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta plantilla?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700" title="Eliminar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-3a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">No se encontraron rutas con los filtros actuales.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $rutas->appends(request()->query())->links() }}
</div>