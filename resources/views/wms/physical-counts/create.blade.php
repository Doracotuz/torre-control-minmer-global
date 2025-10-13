<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Crear Nueva Sesión de Conteo</h2></x-slot>
    <div class="py-12"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
            <form action="{{ route('wms.physical-counts.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div><label for="name">Nombre de la Sesión</label><input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300"></div>
                    <div><label for="type">Tipo de Conteo</label><select name="type" required class="mt-1 block w-full rounded-md border-gray-300"><option value="cycle">Cíclico</option><option value="full">Completo (Wall-to-Wall)</option></select></div>
                </div>
                <div class="mt-6 flex justify-end"><button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Crear y Generar Tareas</button></div>
            </form>
        </div>
    </div></div>
</x-app-layout>