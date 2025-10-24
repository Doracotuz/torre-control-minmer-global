<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ejecutar Tarea de Conteo</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <form action="{{ route('wms.physical-counts.tasks.record', $task) }}" method="POST">
                    @csrf
                    <div class="p-8">
                        <div class="text-center">
                            <p class="text-sm font-medium text-gray-500">UBICACIÓN A CONTAR</p>
                            <p class="text-4xl font-mono font-bold text-gray-800 bg-gray-50 rounded-lg py-2 my-2">
                                {{ $task->location->aisle }}-{{ $task->location->rack }}-{{ $task->location->shelf }}-{{ $task->location->bin }}
                            </p>
                        </div>

                        <div class="text-center mt-4">
                            <p class="text-sm font-medium text-gray-500">TARIMA (LPN)</p>
                            <p class="text-2xl font-mono font-bold text-indigo-600 bg-indigo-50 rounded-lg py-2 my-2">
                                {{ $task->pallet->lpn ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="mt-6">
                            <p class="text-sm font-medium text-gray-500">PRODUCTO A CONTAR</p>
                            <div class="mt-2 p-4 border rounded-lg">
                                <p class="font-bold text-lg text-gray-900">{{ $task->product->name }}</p>
                                <p class="font-mono text-sm text-gray-600">{{ $task->product->sku }}</p>
                            </div>
                        </div>

                        <div class="mt-8">
                            <label for="counted_quantity" class="block text-center text-lg font-medium text-gray-700">Cantidad Contada</label>
                            <input type="number" name="counted_quantity" id="counted_quantity"
                                   min="0" required
                                   class="mt-2 block w-full text-center text-3xl font-bold rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end">
                        <a href="{{ route('wms.physical-counts.show', $task->physical_count_session_id) }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md mr-4">Cancelar</a>
                        <button type="submit" class="px-6 py-3 bg-green-600 text-white font-semibold rounded-md shadow-md hover:bg-green-700">
                            Guardar Conteo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>