<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight">
            {{ __('Gestión de Áreas') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-6 md:p-8">
                
                {{-- Alertas de Éxito/Error --}}
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="fixed top-5 right-5 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]">
                        <div class="flex items-center"><svg class="w-6 h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><div><strong class="font-bold">{{ __('¡Éxito!') }}</strong><span class="block sm:inline ml-1">{{ session('success') }}</span></div></div>
                        <button @click="show = false" class="text-gray-400 hover:text-gray-700">&times;</button>
                    </div>
                @endif
                @if (session('error'))
                     <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="fixed top-5 right-5 z-50 bg-white border-l-4 border-red-600 text-red-700 px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]">
                        <div class="flex items-center"><svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><div><strong class="font-bold">{{ __('¡Error!') }}</strong><span class="block sm:inline ml-1">{{ session('error') }}</span></div></div>
                        <button @click="show = false" class="text-gray-400 hover:text-gray-700">&times;</button>
                    </div>
                @endif
                
                {{-- Contenedor con lógica de Alpine.js para el selector de vista --}}
                <div x-data="{ view: localStorage.getItem('areas_view_mode') || 'grid' }" x-init="$watch('view', val => localStorage.setItem('areas_view_mode', val))">
                    
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                        <div class="inline-flex rounded-lg shadow-sm">
                            <button @click="view = 'grid'" :class="{ 'bg-[#2c3856] text-white': view === 'grid', 'bg-white text-gray-600 hover:bg-gray-50': view !== 'grid' }" class="px-4 py-2 text-sm font-semibold border border-gray-200 rounded-l-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:z-10" title="Vista de Mosaico">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </button>
                            <button @click="view = 'list'" :class="{ 'bg-[#2c3856] text-white': view === 'list', 'bg-white text-gray-600 hover:bg-gray-50': view !== 'list' }" class="px-4 py-2 text-sm font-semibold border-t border-b border-r border-gray-200 rounded-r-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:z-10" title="Vista de Lista">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            </button>
                        </div>
                        <a href="{{ route('admin.areas.create') }}" class="inline-flex items-center px-5 py-2 bg-[#2c3856] border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#ff9c00] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff9c00] transition-all duration-300 transform hover:scale-105 shadow-md w-full sm:w-auto justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Crear Área
                        </a>
                    </div>

                    <div x-show="view === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @forelse ($areas as $area)
                            <div class="bg-white rounded-lg shadow border border-gray-100 p-5 flex flex-col transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                                <div class="flex justify-between items-start mb-3">
                                    <h5 class="text-lg font-bold text-[#2c3856]">{{ $area->name }}</h5>
                                    @if ($area->icon_path)
                                        {{-- CAMBIO PARA S3: Usar Storage::url para cargar la imagen desde S3 --}}
                                        <img class="h-10 w-10 object-contain" src="{{ Storage::url($area->icon_path) }}" alt="{{ $area->name }}">
                                    @else
                                        <div class="h-10 w-10 bg-gray-100 rounded-md flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" /></svg>
                                        </div>
                                    @endif
                                </div>
                                <p class="flex-grow text-sm text-gray-600 mb-4 line-clamp-3">{{ $area->description ?? 'Sin descripción.' }}</p>
                                <div class="mt-auto pt-4 border-t border-gray-100 flex justify-end space-x-2">
                                    <a href="{{ route('admin.areas.edit', $area) }}" class="inline-flex items-center px-3 py-1 bg-gray-100 text-[#2c3856] rounded-full font-semibold text-xs hover:bg-gray-200 transition-colors">Editar</a>
                                    <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta área? ¡Esta acción es irreversible!');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-full font-semibold text-xs hover:bg-red-200 transition-colors">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="col-span-full text-center text-gray-500 py-12">No hay áreas creadas. ¡Comienza creando una!</p>
                        @endforelse
                    </div>

                    <div x-show="view === 'list'" class="overflow-x-auto bg-white rounded-lg shadow border">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-[#2c3856]">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Icono</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Descripción</th>
                                    <th class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($areas as $area)
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($area->icon_path)
                                                {{-- CAMBIO PARA S3: Usar Storage::url para cargar la imagen desde S3 --}}
                                                <img class="h-9 w-9 object-contain" src="{{ Storage::url($area->icon_path) }}" alt="{{ $area->name }}">
                                            @else
                                                <div class="h-9 w-9 bg-gray-100 rounded-md flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" /></svg>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-bold text-gray-900">{{ $area->name }}</div></td>
                                        <td class="px-6 py-4"><p class="text-sm text-gray-600 line-clamp-2 max-w-md">{{ $area->description ?? 'N/A' }}</p></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><div class="flex items-center justify-end space-x-4"><a href="{{ route('admin.areas.edit', $area) }}" class="text-[#2c3856] hover:text-[#ff9c00] font-semibold">Editar</a><form action="{{ route('admin.areas.destroy', $area) }}" method="POST" onsubmit="return confirm('¿Estás seguro? ¡Esta acción es irreversible!');">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-800 font-semibold">Eliminar</button></form></div></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">No hay áreas creadas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>