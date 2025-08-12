<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editando Almacén: <span class="text-blue-600">{{ $warehouse->name }}</span></h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('customer-service.warehouses.update', $warehouse) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @if ($errors->any())<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><p class="font-bold">Hay errores:</p><ul class="mt-2 list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label for="warehouse_id" class="block text-sm font-medium text-gray-700">ID Almacén</label><input type="text" name="warehouse_id" id="warehouse_id" value="{{ old('warehouse_id', $warehouse->warehouse_id) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label for="name" class="block text-sm font-medium text-gray-700">Nombre del Almacén</label><input type="text" name="name" id="name" value="{{ old('name', $warehouse->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                    </div>
                    <div class="flex justify-end gap-4 mt-8"><a href="{{ route('customer-service.warehouses.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</a><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Actualizar Almacén</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
