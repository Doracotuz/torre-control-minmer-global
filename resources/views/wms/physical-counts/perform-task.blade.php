<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ejecutar Tarea de Conteo</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <form action="{{ route('wms.physical-counts.tasks.record', $task) }}" method="POST">
                    @csrf
                    
                    <div class="p-6 bg-gray-800 text-white text-center rounded-t-lg">
                        <p class="text-sm font-medium text-gray-300 uppercase">UBICACIÓN</p>
                        <p class="text-4xl font-mono font-bold my-1">
                            {{ $task->location->aisle }}-{{ $task->location->rack }}-{{ $task->location->shelf }}-{{ $task->location->bin }}
                        </p>
                    </div>
                    
                    <div class="p-6 space-y-5">
                        <div class="text-center">
                            <p class="text-sm font-medium text-gray-500">TARIMA (LPN)</p>
                            <p class="text-2xl font-mono font-bold text-indigo-600 my-1">
                                {{ $task->pallet->lpn ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">PRODUCTO A CONTAR</p>
                            <div class="mt-1 p-4 border rounded-lg bg-gray-50">
                                <p class="font-bold text-lg text-gray-900">{{ $task->product->name }}</p>
                                <p class="font-mono text-sm text-gray-600">{{ $task->product->sku }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="counted_quantity" class="block text-center text-lg font-bold text-gray-800 mb-2">
                                CANTIDAD CONTADA
                            </label>
                            <input type="number" name="counted_quantity" id="counted_quantity"
                                   min="0" required autofocus
                                   pattern="[0-9]*" inputmode="numeric" {{-- Optimizado para teclado numérico móvil --}}
                                   class="mt-1 block w-full text-center text-5xl font-bold rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3">
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-4 sm:px-6 grid grid-cols-2 gap-4 rounded-b-lg">
                        <a href="{{ route('wms.physical-counts.show', $task->physical_count_session_id) }}" 
                           class="w-full inline-flex justify-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="w-full inline-flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-green-600 hover:bg-green-700">
                            <i class="fas fa-check-circle mr-2"></i> Guardar Conteo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>