<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Editar Calidad</h2></x-slot>
    <div class="py-12"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8"><div class="bg-white p-8 rounded-lg shadow-xl">
        <form action="{{ route('wms.qualities.update', $quality) }}" method="POST"> @csrf @method('PUT')
            <div class="space-y-4">
                <div><label for="name">Nombre</label><input type="text" name="name" value="{{ $quality->name }}" required class="mt-1 block w-full rounded-md border-gray-300"></div>
                <div><label for="description">Descripción</label><textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300">{{ $quality->description }}</textarea></div>
            </div>
            <div class="mt-6 flex justify-end">
                <a href="{{ route('wms.qualities.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md mr-4">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Actualizar</button>
            </div>
        </form>
    </div></div></div>
</x-app-layout>