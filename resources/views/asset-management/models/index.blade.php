@extends('layouts.app')

@section('content')
<div class="w-full max-w-5xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Gestionar Modelos de Hardware</h1>
        <a href="{{ route('asset-management.models.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Añadir Modelo
        </a>
    </div>
    <a href="{{ route('asset-management.dashboard') }}" class="btn bg-gray-600 text-white">
        <i class="fas fa-arrow-left mr-2"></i> Volver al Dashboard
    </a>    
    <br>
    <br>
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4 font-semibold text-left">Nombre del Modelo</th>
                        <th class="p-4 font-semibold text-left">Fabricante</th>
                        <th class="p-4 font-semibold text-left">Categoría</th>
                        <th class="p-4 font-semibold text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($models as $model)
                        <tr>
                            <td class="p-4 font-semibold">{{ $model->name }}</td>
                            <td class="p-4">{{ $model->manufacturer->name }}</td>
                            <td class="p-4">{{ $model->category->name }}</td>
                            <td class="p-4">
                                <a href="{{ route('asset-management.models.edit', $model) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">Editar</a>
                                <form action="{{ route('asset-management.models.destroy', $model) }}" method="POST" class="inline ml-4" onsubmit="return confirm('¿Estás seguro?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center p-8 text-gray-500">No hay modelos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($models->hasPages())
        <div class="p-4 bg-gray-50 border-t">
            {!! $models->links() !!}
        </div>
        @endif
    </div>
</div>
@endsection