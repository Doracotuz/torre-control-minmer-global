<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Crear Nueva Sesión de Conteo</h2>
    </x-slot>
    
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('wms.physical-counts.store') }}" method="POST">
                    @csrf

                    {{-- INICIO DE LA CORRECCIÓN: Bloque para mostrar errores --}}
                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                            <p class="font-bold">¡Ocurrió un error!</p>
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                            <p class="font-bold">Por favor, corrige los siguientes errores:</p>
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    {{-- FIN DE LA CORRECCIÓN --}}

                    <div class="space-y-4">
                        <div>
                            <label for="name">Nombre de la Sesión</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label for="type">Tipo de Conteo</label>
                            <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="cycle" @selected(old('type') == 'cycle')>Cíclico</option>
                                <option value="full" @selected(old('type') == 'full')>Completo (Wall-to-Wall)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('wms.physical-counts.index') }}" class="px-4 py-2 bg-gray-200 rounded-md mr-4">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Crear y Generar Tareas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>