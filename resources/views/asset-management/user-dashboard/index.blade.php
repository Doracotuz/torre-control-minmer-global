@extends('layouts.app')

@section('content')
{{-- (Puedes copiar los estilos del archivo index.blade.php si lo deseas) --}}
<style>
    :root { --color-primary: #2c3856; --color-accent: #ff9c00; }
    body { background-color: #f3f4f6; }
</style>

<div class="w-full max-w-4xl mx-auto px-4 py-12">
    <header class="mb-8">
        <a href="{{ route('asset-management.dashboard') }}" class="text-sm text-gray-500 hover:text-[var(--color-primary)]">
            <i class="fas fa-arrow-left mr-2"></i> Volver al Dashboard
        </a>
        <h1 class="text-4xl font-bold text-gray-800 tracking-tight mt-2">Responsivas por Usuario</h1>
        <p class="text-gray-500 mt-1">Selecciona un usuario para ver sus activos asignados y generar su responsiva consolidada.</p>
    </header>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre del Colaborador</th>
                    <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Puesto</th>
                    <th class="p-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Activos Asignados</th>
                    <th class="p-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($members as $member)
                <tr class="hover:bg-blue-50">
                    <td class="p-4 font-semibold text-gray-800">{{ $member->name }}</td>
                    <td class="p-4 text-gray-600">{{ $member->position->name ?? 'N/A' }}</td>
                    <td class="p-4 text-center font-bold text-[var(--color-primary)]">{{ $member->assignments_count }}</td>
                    <td class="p-4 text-right">
                        <a href="{{ route('asset-management.user-dashboard.show', $member) }}" class="font-semibold text-[var(--color-primary)] hover:underline">
                            Ver Detalles <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-8 text-center text-gray-500">No hay usuarios con activos asignados actualmente.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
         @if ($members->hasPages())
            <div class="p-4 bg-gray-50 border-t">
                {!! $members->links() !!}
            </div>
        @endif
    </div>
</div>
@endsection