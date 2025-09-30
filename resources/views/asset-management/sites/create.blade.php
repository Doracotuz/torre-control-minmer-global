@extends('layouts.app')

@section('content')
<div class="w-full max-w-lg mx-auto px-4 py-8">
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Añadir Nuevo Sitio</h1>
        <form action="{{ route('asset-management.sites.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="name" class="block font-semibold">Nombre del Sitio</label>
                    <input type="text" id="name" name="name" class="form-input w-full mt-1" required>
                </div>
                <div>
                    <label for="address" class="block font-semibold">Dirección (Opcional)</label>
                    <input type="text" id="address" name="address" class="form-input w-full mt-1">
                </div>
            </div>
            <div class="mt-8 flex justify-end space-x-2">
                <a href="{{ route('asset-management.sites.index') }}" class="btn bg-gray-200 text-gray-700">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Sitio</button>
            </div>
        </form>
    </div>
</div>
@endsection