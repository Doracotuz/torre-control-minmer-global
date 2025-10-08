@extends('layouts.app')

@section('content')
<style>
    :root {
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
    }
    body {
        background-color: #f3f4f6;
    }
    .btn {
        padding: 0.65rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 200ms ease-in-out;
    }
    .btn-primary {
        background-color: var(--color-primary);
        color: white;
    }
    .btn-primary:hover {
        background-color: #212a41;
    }
    .btn-secondary {
        background-color: white;
        color: #666;
        border: 1px solid #d1d5db;
    }
    .btn-secondary:hover {
        background-color: #f9fafb;
    }
</style>

<div class="w-full max-w-5xl mx-auto px-4 py-12">
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <a href="{{ route('asset-management.user-dashboard.index') }}" class="text-sm text-gray-500 hover:text-[var(--color-primary)]">
                <i class="fas fa-arrow-left mr-2"></i> Volver a la lista de usuarios
            </a>
            <h1 class="text-4xl font-bold text-gray-800 tracking-tight mt-2">{{ $member->name }}</h1>
            <p class="text-gray-500 mt-1">{{ $member->position->name ?? 'Sin puesto asignado' }}</p>
        </div>
        <div>
            <a href="{{ route('asset-management.user-dashboard.pdf', $member) }}" target="_blank" class="btn btn-primary mt-4 sm:mt-0">
                <i class="fas fa-file-pdf mr-2"></i> Generar PDF Consolidado
            </a>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold text-[var(--color-primary)]">Activos Asignados Actualmente</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Etiqueta</th>
                                <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Categoría</th>
                                <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Modelo</th>
                                <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Serie</th>
                                <th class="p-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha Asignación</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($assignments as $assignment)
                            <tr class="hover:bg-blue-50">
                                <td class="p-4 font-mono font-semibold text-[var(--color-primary)]">
                                    <a href="{{ route('asset-management.assets.show', $assignment->asset) }}" class="hover:underline">{{ $assignment->asset->asset_tag }}</a>
                                </td>
                                <td class="p-4 text-gray-600">{{ $assignment->asset->model->category->name }}</td>
                                <td class="p-4 font-semibold text-gray-800">{{ $assignment->asset->model->name }}</td>
                                <td class="p-4 text-gray-600">{{ $assignment->asset->serial_number }}</td>
                                <td class="p-4 text-right text-gray-600">{{ \Carbon\Carbon::parse($assignment->assignment_date)->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-500">Este usuario no tiene activos asignados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-[var(--color-primary)] mb-4">Adjuntar Responsiva Firmada</h3>
                <form action="{{ route('asset-management.user-dashboard.uploadReceipt', $member) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="signed_receipt" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                    <button type="submit" class="btn btn-primary w-full mt-4 text-sm">
                        <i class="fas fa-upload mr-2"></i> Subir Archivo
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-[var(--color-primary)] mb-4">Historial de Responsivas</h3>
                <ul class="space-y-3">
                    @forelse($responsivas as $responsiva)
                    <li class="flex justify-between items-center text-sm border-b pb-2 last:border-b-0">
                        <div>
                            <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                            <span class="font-semibold">Subido:</span>
                            <span>{{ \Carbon\Carbon::parse($responsiva->generated_date)->format('d/m/Y') }}</span>
                        </div>
                        <a href="{{ Storage::disk('s3')->url($responsiva->file_path) }}" target="_blank" class="font-semibold text-[var(--color-primary)] hover:underline">
                            Ver
                        </a>
                    </li>
                    @empty
                    <p class="text-sm text-center text-gray-500 py-4">No se han subido responsivas consolidadas.</p>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection