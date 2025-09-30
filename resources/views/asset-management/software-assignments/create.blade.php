@extends('layouts.app')

@section('content')
<div class="w-full max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold text-gray-800">Asignar Software</h1>
        
        <div class="mt-4 border-t pt-4">
            <p><strong>Activo:</strong> {{ $asset->model->name }} ({{ $asset->asset_tag }})</p>
        </div>

        <form action="{{ route('asset-management.software-assignments.store', $asset) }}" method="POST" class="mt-6">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="software_license_id" class="block font-semibold">Licencia de Software:</label>
                    <select id="software_license_id" name="software_license_id" class="form-input w-full mt-1" required>
                        <option value="">-- Selecciona una licencia disponible --</option>
                        @forelse($availableLicenses as $license)
                            <option value="{{ $license->id }}">
                                {{ $license->name }} (Disponibles: {{ $license->total_seats - $license->used_seats }})
                            </option>
                        @empty
                            <option value="" disabled>No hay licencias con asientos disponibles</option>
                        @endforelse
                    </select>
                </div>
                <div>
                    <label for="install_date" class="block font-semibold">Fecha de Instalación:</label>
                    <input type="date" id="install_date" name="install_date" value="{{ date('Y-m-d') }}" class="form-input w-full mt-1" required>
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