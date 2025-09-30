@extends('layouts.app')

@section('content')
<div class="w-full max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Editar Licencia de Software</h1>
        <form action="{{ route('asset-management.software-licenses.update', $softwareLicense) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block font-semibold">Nombre del Software</label>
                    <input type="text" id="name" name="name" class="form-input w-full mt-1" value="{{ old('name', $softwareLicense->name) }}" required>
                </div>
                <div>
                    <label for="total_seats" class="block font-semibold">Licencias Totales</label>
                    <input type="number" id="total_seats" name="total_seats" class="form-input w-full mt-1" value="{{ old('total_seats', $softwareLicense->total_seats) }}" min="1" required>
                </div>
                 <div>
                    <label for="purchase_date" class="block font-semibold">Fecha de Compra (Opcional)</label>
                    <input type="date" id="purchase_date" name="purchase_date" class="form-input w-full mt-1" value="{{ old('purchase_date', $softwareLicense->purchase_date?->format('Y-m-d')) }}">
                </div>
                 <div>
                    <label for="expiry_date" class="block font-semibold">Fecha de Vencimiento (Opcional)</label>
                    <input type="date" id="expiry_date" name="expiry_date" class="form-input w-full mt-1" value="{{ old('expiry_date', $softwareLicense->expiry_date?->format('Y-m-d')) }}">
                </div>
                <div class="md:col-span-2">
                    <label for="license_key" class="block font-semibold">Clave de Licencia (Opcional)</label>
                    <textarea id="license_key" name="license_key" rows="4" class="form-input w-full mt-1">{{ old('license_key', $softwareLicense->license_key) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">La clave se guardará de forma encriptada. Dejar en blanco para no modificar.</p>
                </div>
            </div>
            <div class="mt-8 flex justify-end space-x-2">
                <a href="{{ route('asset-management.software-licenses.index') }}" class="btn bg-gray-200 text-gray-700">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Licencia</button>
            </div>
        </form>
    </div>
</div>
@endsection