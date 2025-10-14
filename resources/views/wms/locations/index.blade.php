<x-app-layout>
    {{-- 1. Inicializamos el componente de Alpine.js para manejar la selección --}}
    <div x-data="{ selected: [] }">
        <x-slot name="header">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mapa del Almacén (Ubicaciones)
                </h2>
                
                {{-- 2. Acciones principales --}}
                <div class="flex items-center space-x-2">
                    {{-- Formulario para imprimir etiquetas seleccionadas --}}
                    <form :action="('{{ route('wms.locations.print-labels') }}')" method="POST" target="_blank" class="inline-block">
                        @csrf
                        {{-- Se añaden inputs ocultos por cada ID seleccionado --}}
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <button type="submit" :disabled="selected.length === 0" class="px-4 py-2 bg-gray-700 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-print mr-2"></i> Imprimir (<span x-text="selected.length"></span>)
                        </button>
                    </form>

                    <a href="{{ route('wms.locations.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-indigo-700">
                        Añadir Ubicación
                    </a>
                </div>
            </div>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{-- Mensajes de sesión para éxito o error --}}
                @if (session('success'))
                    <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert"><p>{{ session('success') }}</p></div>
                @endif
                @if (session('error'))
                     <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert"><p>{{ session('error') }}</p></div>
                @endif

                {{-- Sección de Importación Masiva --}}
                <div class="mb-8 p-6 bg-white border rounded-lg shadow-sm">
                    <h3 class="font-semibold text-lg mb-3 text-gray-800">Importación Masiva</h3>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('wms.locations.template') }}" class="text-sm font-medium text-indigo-600 hover:underline">
                            <i class="fas fa-download mr-1"></i> Descargar Plantilla CSV
                        </a>
                        <form action="{{ route('wms.locations.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center">
                            @csrf
                            <input type="file" name="file" accept=".csv" required class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-md ml-2 shadow-sm hover:bg-black">Importar</button>
                        </form>
                    </div>
                </div>

                {{-- Tabla de Ubicaciones --}}
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    {{-- 3. Checkbox para seleccionar/deseleccionar todo --}}
                                    <th class="p-4">
                                        <input type="checkbox" @click="$el.checked ? selected = {{ $locations->pluck('id') }} : selected = []" class="rounded border-gray-300">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sec. Picking</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Almacén</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación Completa</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($locations as $location)
                                    <tr class="hover:bg-gray-50">
                                        {{-- 4. Checkbox para cada fila individual --}}
                                        <td class="p-4">
                                            <input type="checkbox" :value="{{ $location->id }}" x-model="selected" class="rounded border-gray-300">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">{{ $location->code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-center">{{ $location->pick_sequence ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $location->warehouse->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ $location->type }}</span></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $location->aisle }}-{{ $location->rack }}-{{ $location->shelf }}-{{$location->bin}}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('wms.locations.edit', $location) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                            <form action="{{ route('wms.locations.destroy', $location) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta ubicación?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No hay ubicaciones registradas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-4">
                    {{ $locations->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>