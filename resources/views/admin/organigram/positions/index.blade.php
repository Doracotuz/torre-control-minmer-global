<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Posiciones del Organigrama') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-8">
                {{-- Mensajes de éxito y error --}}
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

                <div x-data="{
                    editingPosition: null, // Objeto para la posición que se está editando
                    newPositionName: '',
                    newPositionDescription: '',
                    newPositionHierarchyLevel: '',
                    
                    editPosition(position) {
                        this.editingPosition = position;
                        this.newPositionName = position.name;
                        this.newPositionDescription = position.description;
                        this.newPositionHierarchyLevel = position.hierarchy_level;
                        window.scrollTo({ top: 0, behavior: 'smooth' }); // Desplazarse al inicio del formulario
                    },
                    cancelEdit() {
                        this.editingPosition = null;
                        this.newPositionName = '';
                        this.newPositionDescription = '';
                        this.newPositionHierarchyLevel = '';
                    }
                }">
                    <h3 class="text-xl font-semibold text-[#2c3856] mb-4" style="font-family: 'Raleway', sans-serif;">
                        <span x-text="editingPosition ? 'Editar Posición' : 'Añadir Nueva Posición'"></span>
                    </h3>

                    <form method="POST"
                        :action="editingPosition ? '{{ route('admin.organigram.positions.update', ['organigram_position' => '_id_']) }}'.replace('_id_', editingPosition.id) : '{{ route('admin.organigram.positions.store') }}'"
                        class="mb-8 p-6 bg-gray-50 rounded-lg shadow-sm border border-gray-200">
                        @csrf
                        <template x-if="editingPosition">
                            @method('PUT')
                        </template>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="name" :value="__('Nombre de la Posición')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                    x-model="newPositionName" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>
                            <div>
                                <x-input-label for="hierarchy_level" :value="__('Nivel Jerárquico (Opcional)')" />
                                <x-text-input id="hierarchy_level" name="hierarchy_level" type="number" class="mt-1 block w-full"
                                    x-model="newPositionHierarchyLevel" min="0" />
                                <x-input-error class="mt-2" :messages="$errors->get('hierarchy_level')" />
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Descripción (Opcional)')" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                x-model="newPositionDescription"></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <template x-if="editingPosition">
                                <button type="button" @click="cancelEdit()" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                    {{ __('Cancelar Edición') }}
                                </button>
                            </template>
                            <x-primary-button>
                                <span x-text="editingPosition ? 'Actualizar Posición' : 'Crear Posición'"></span>
                            </x-primary-button>
                        </div>
                    </form>

                    <h3 class="text-xl font-semibold text-[#2c3856] mb-4 mt-8" style="font-family: 'Raleway', sans-serif;">{{ __('Posiciones Existentes') }}</h3>

                    @if ($positions->isEmpty())
                        <p class="text-lg text-gray-600 py-8 text-center">No hay posiciones registradas.</p>
                    @else
                        {{-- AÑADIDO: Contenedor overflow-x-auto para responsividad de tabla --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">
                                            {{ __('Nombre') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Nivel Jerárquico') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Descripción') }}
                                        </th>
                                        <th scope="col" class="relative px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg">
                                            {{ __('Acciones') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($positions as $position)
                                        <tr class="hover:bg-gray-100 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $position->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $position->hierarchy_level ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-700 truncate max-w-xs">
                                                {{ $position->description ?? 'Sin descripción' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                                <button type="button" @click="editPosition(JSON.parse(JSON.stringify({{ $position }})))" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200">
                                                    {{ __('Editar') }}
                                                </button>
                                                <form action="{{ route('admin.organigram.positions.destroy', $position) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta posición? Si hay miembros asignados a ella, su posición se volverá nula.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 transition-colors duration-200">
                                                        {{ __('Eliminar') }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>