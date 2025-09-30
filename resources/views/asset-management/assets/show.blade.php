@extends('layouts.app')

@section('content')
<div class="w-full max-w-6xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $asset->model->name }}</h1>
            <p class="font-mono text-blue-600">{{ $asset->asset_tag }}</p>
        </div>
        <div>
            <a href="{{ route('asset-management.dashboard') }}" class="btn bg-gray-200 text-gray-700 mr-2">Volver al Dashboard</a>
            <a href="{{ route('asset-management.assets.edit', $asset) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Columna de Detalles --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold text-lg border-b pb-2 mb-4">Detalles del Activo</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <p><strong>No. Serie:</strong> {{ $asset->serial_number }}</p>
                <p><strong>Categoría:</strong> {{ $asset->model->category->name }}</p>
                <p><strong>Fabricante:</strong> {{ $asset->model->manufacturer->name }}</p>
                <p><strong>Ubicación:</strong> {{ $asset->site->name }}</p>
                <p><strong>Fecha de Compra:</strong> {{ $asset->purchase_date ? date('d/m/Y', strtotime($asset->purchase_date)) : 'N/A' }}</p>
                <p><strong>Fin de Garantía:</strong> {{ $asset->warranty_end_date ? date('d/m/Y', strtotime($asset->warranty_end_date)) : 'N/A' }}</p>
            </div>

            @if($asset->model->category->name === 'Laptop' || $asset->model->category->name === 'Desktop')
            <h3 class="font-bold text-lg border-b pb-2 my-4">Especificaciones Técnicas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <p><strong>Procesador:</strong> {{ $asset->cpu ?? 'N/A' }}</p>
                <p><strong>RAM:</strong> {{ $asset->ram ?? 'N/A' }}</p>
                <p><strong>Almacenamiento:</strong> {{ $asset->storage ?? 'N/A' }}</p>
                <p><strong>MAC Address:</strong> {{ $asset->mac_address ?? 'N/A' }}</p>
            </div>
            @endif
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">Software Asignado</h3>
                <a href="{{ route('asset-management.software-assignments.create', $asset) }}" class="btn btn-sm btn-primary">Asignar Software</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-2 font-semibold text-left">Nombre</th>
                            <th class="p-2 font-semibold text-left">Fecha de Instalación</th>
                            <th class="p-2 font-semibold text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($asset->softwareAssignments as $assignment)
                        <tr>
                            <td class="p-2">{{ $assignment->license->name }}</td>
                            <td class="p-2">{{ $assignment->install_date ? date('d/m/Y', strtotime($assignment->install_date)) : 'N/A' }}</td>
                            <td class="p-2">
                                <form action="{{ route('asset-management.software-assignments.destroy', $assignment) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres desinstalar este software?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline">Desinstalar</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="p-4 text-center text-gray-500">No hay software asignado a este activo.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>        

        {{-- Columna de Estado y Asignación --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="font-bold text-lg mb-2">Estado Actual</h3>
                <p class="status-badge status-{{ Str::slug($asset->status) }} inline-block">{{ $asset->status }}</p>

                @if($asset->status === 'En Almacén')
                    <a href="{{ route('asset-management.assignments.create', $asset) }}" class="btn btn-primary w-full mt-4">Asignar Activo</a>
                @endif
                
                @if($asset->currentAssignment)
                    <div class="mt-4 border-t pt-4">
                        <p class="text-sm font-semibold text-gray-600">Asignado a:</p>
                        <p class="text-lg font-bold">{{ $asset->currentAssignment->member->name }}</p>
                        <p class="text-sm text-gray-500">{{ $asset->currentAssignment->member->position->name ?? 'Sin Puesto' }}</p>
                        <p class="text-sm text-gray-500">Desde: {{ date('d/m/Y', strtotime($asset->currentAssignment->assignment_date)) }}</p>
                        
                        <a href="{{ route('asset-management.assignments.pdf', $asset->currentAssignment) }}" target="_blank" class="btn bg-indigo-600 text-white w-full mt-4 flex items-center justify-center">
                            <i class="fas fa-file-pdf mr-2"></i> Generar Responsiva
                        </a>

                        <form action="{{ route('asset-management.assignments.return', $asset->currentAssignment) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres registrar la devolución de este activo?');">
                            @csrf
                            <button type="submit" class="btn bg-yellow-500 text-white w-full mt-2">Registrar Devolución</button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="font-bold text-lg mb-2">Historial de Asignaciones</h3>
                <ul class="space-y-3 text-sm">
                @forelse($asset->assignments->sortByDesc('assignment_date') as $assignment)
                    <li class="border-b pb-2">
                        <p class="font-semibold">{{ $assignment->member->name }}</p>
                        <p class="text-gray-600">Asignado: {{ date('d/m/Y', strtotime($assignment->assignment_date)) }}</p>
                        @if($assignment->actual_return_date)
                        <p class="text-green-600">Devuelto: {{ date('d/m/Y', strtotime($assignment->actual_return_date)) }}</p>
                        @else
                        <p class="text-blue-600">Actualmente Asignado</p>
                        @endif
                    </li>
                @empty
                    <p class="text-gray-500">Este activo nunca ha sido asignado.</p>
                @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection