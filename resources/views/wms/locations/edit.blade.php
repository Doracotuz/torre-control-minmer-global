<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Ubicación</h2></x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('wms.locations.update', $location) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('wms.locations._form')
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('wms.locations.index') }}" class="px-4 py-2 bg-gray-300 rounded-md mr-4">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Actualizar Ubicación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>