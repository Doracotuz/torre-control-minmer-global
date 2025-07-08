<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Áreas') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-4 sm:p-8">
                {{-- Mensajes de éxito/error --}}
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300 transform scale-90 opacity-0" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200 transform scale-100 opacity-100" x-transition:leave-end="opacity-0 scale-90"
                         class="fixed top-4 right-4 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <strong class="font-bold mr-1">{{ __('¡Éxito!') }}</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300 transform scale-90 opacity-0" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200 transform scale-100 opacity-100" x-transition:leave-end="opacity-0 scale-90"
                         class="fixed top-4 right-4 z-50 bg-white border-l-4 border-red-600 text-red-700 px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <strong class="font-bold mr-1">{{ __('¡Error!') }}</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                @endif

                <div class="flex justify-end mb-4">
                    <a href="{{ route('admin.areas.create') }}" class="inline-flex items-center px-4 py-2 bg-[#2b2b2b] border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#666666] focus:bg-[#666666] active:bg-[#000000] focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md">
                        {{ __('Crear Nueva Área') }}
                    </a>
                </div>

                {{-- Contenedor de la tabla: se oculta en móviles y se muestra un listado apilado --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Nombre') }}
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sm:px-6">
                                    {{ __('Icono') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Descripción') }}
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">{{ __('Acciones') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($areas as $area)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $area->name }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap sm:px-6">
                                        @if ($area->icon_path)
                                            <img src="{{ asset('storage/' . $area->icon_path) }}" alt="{{ $area->name }} Icon" class="h-8 w-8 object-contain rounded-md border border-gray-200 p-1">
                                        @else
                                            <span class="text-gray-400 text-sm">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 truncate w-32 sm:w-48 lg:w-64">
                                            {{ $area->description ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-1 sm:space-x-3 flex flex-col sm:flex-row items-end justify-end space-y-2 sm:space-y-0">
                                        <a href="{{ route('admin.areas.edit', $area) }}" class="inline-flex items-center px-3 py-1 bg-indigo-500 border border-transparent rounded-md font-semibold text-xxs sm:text-xs text-white uppercase tracking-widest hover:bg-indigo-600 focus:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm w-full sm:w-auto justify-center">
                                            {{ __('Editar') }}
                                        </a>
                                        <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" class="inline-block w-full sm:w-auto" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta área? Esto eliminará también todas las carpetas, archivos y permisos asociados a esta área, y desvinculará a los usuarios. ¡Esta acción es irreversible!');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-500 border border-transparent rounded-md font-semibold text-xxs sm:text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm w-full">
                                                {{ __('Eliminar') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Versión móvil de la tabla (listado apilado) --}}
                <div class="sm:hidden space-y-4">
                    @foreach ($areas as $area)
                        <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-base font-semibold text-gray-900">{{ $area->name }}</div>
                                @if ($area->icon_path)
                                    <img src="{{ asset('storage/' . $area->icon_path) }}" alt="{{ $area->name }} Icon" class="h-8 w-8 object-contain rounded-md border border-gray-200 p-1">
                                @endif
                            </div>
                            <div class="text-sm text-gray-700 mb-3">
                                <span class="font-medium">{{ __('Descripción:') }}</span> {{ $area->description ?? 'N/A' }}
                            </div>
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('admin.areas.edit', $area) }}" class="inline-flex items-center px-3 py-1 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 focus:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                                    {{ __('Editar') }}
                                </a>
                                <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta área? Esto eliminará también todas las carpetas, archivos y permisos asociados a esta área, y desvinculará a los usuarios. ¡Esta acción es irreversible!');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                                        {{ __('Eliminar') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</x-app-layout>