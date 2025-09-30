@extends('layouts.app')

@section('content')
<div class="w-full max-w-4xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Gestionar Sitios</h1>
        <a href="{{ route('asset-management.sites.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Añadir Sitio
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
                        <th class="p-4 font-semibold text-left">Nombre</th>
                        <th class="p-4 font-semibold text-left">Dirección</th>
                        <th class="p-4 font-semibold text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($sites as $site)
                        <tr>
                            <td class="p-4 font-semibold">{{ $site->name }}</td>
                            <td class="p-4">{{ $site->address ?? 'N/A' }}</td>
                            <td class="p-4">
                                <a href="{{ route('asset-management.sites.edit', $site) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">Editar</a>
                                <form action="{{ route('asset-management.sites.destroy', $site) }}" method="POST" class="inline ml-4" onsubmit="return confirm('¿Estás seguro de eliminar este sitio?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center p-8 text-gray-500">No hay sitios registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t">
            {!! $sites->links() !!}
        </div>
    </div>
</div>
@endsection