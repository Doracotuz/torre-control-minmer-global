@extends('layouts.app')

@section('content')
<div class="w-full max-w-6xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Gestionar Software</h1>
        <div class="flex items-center space-x-2">
            <a href="{{ route('asset-management.dashboard') }}" class="btn bg-gray-600 text-white">
                <i class="fas fa-arrow-left mr-2"></i> Volver al Dashboard
            </a>
            <a href="{{ route('asset-management.software-licenses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Añadir Licencia
            </a>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4 font-semibold text-left">Nombre del Software</th>
                        <th class="p-4 font-semibold text-left">Licencias (Usados / Totales)</th>
                        <th class="p-4 font-semibold text-left">Fecha de Compra</th>
                        <th class="p-4 font-semibold text-left">Fecha de Vencimiento</th>
                        <th class="p-4 font-semibold text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($licenses as $license)
                        <tr>
                            <td class="p-4 font-semibold">{{ $license->name }}</td>
                            <td class="p-4">
                                {{ $license->assignments_count }} / {{ $license->total_seats }}
                            </td>
                            <td class="p-4">{{ $license->purchase_date ? $license->purchase_date->format('d/m/Y') : 'N/A' }}</td>
                            <td class="p-4">{{ $license->expiry_date ? $license->expiry_date->format('d/m/Y') : 'No vence' }}</td>
                            <td class="p-4">
                                <a href="{{ route('asset-management.software-licenses.edit', $license) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center p-8 text-gray-500">No hay licencias de software registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($licenses->hasPages())
        <div class="p-4 bg-gray-50 border-t">
            {!! $licenses->links() !!}
        </div>
        @endif
    </div>
</div>
@endsection