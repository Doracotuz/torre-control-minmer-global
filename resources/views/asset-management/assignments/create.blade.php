@extends('layouts.app')

@section('content')
<div class="w-full max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold text-gray-800">Asignar Activo</h1>
        
        <div class="mt-4 border-t pt-4">
            <p><strong>Activo:</strong> {{ $asset->model->name }}</p>
            <p><strong>Etiqueta:</strong> <span class="font-mono">{{ $asset->asset_tag }}</span></p>
            <p><strong>No. Serie:</strong> {{ $asset->serial_number }}</p>
        </div>

        <form action="{{ route('asset-management.assignments.store', $asset) }}" method="POST" class="mt-6">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="organigram_member_id" class="block font-semibold">Asignar a:</label>
                    <select id="organigram_member_id" name="organigram_member_id" class="form-input w-full mt-1" required>
                        <option value="">-- Selecciona un miembro del equipo --</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }} - ({{ $member->position->name ?? 'Sin Puesto' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="assignment_date" class="block font-semibold">Fecha de Asignación:</label>
                    <input type="date" id="assignment_date" name="assignment_date" value="{{ date('Y-m-d') }}" class="form-input w-full mt-1" required>
                </div>
            </div>
            <div class="mt-8 text-right">
                <a href="{{ route('asset-management.assets.show', $asset) }}" class="btn bg-gray-200 text-gray-700">Cancelar</a>
                <button type="submit" class="btn btn-primary">Confirmar Asignación</button>
            </div>
        </form>
    </div>
</div>
@endsection