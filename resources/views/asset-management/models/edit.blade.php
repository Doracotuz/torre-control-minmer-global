@extends('layouts.app')

@section('content')
<div class="w-full max-w-lg mx-auto px-4 py-8">
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Editar Modelo</h1>
        <form action="{{ route('asset-management.models.update', $model) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="name" class="block font-semibold">Nombre del Modelo</label>
                    <input type="text" id="name" name="name" class="form-input w-full mt-1" value="{{ old('name', $model->name) }}" required>
                </div>
                <div>
                    <label for="manufacturer_id" class="block font-semibold">Fabricante</label>
                    <select name="manufacturer_id" id="manufacturer_id" class="form-input w-full mt-1" required>
                        <option value="">-- Selecciona --</option>
                        @foreach($manufacturers as $manufacturer)
                            <option value="{{ $manufacturer->id }}" @selected(old('manufacturer_id', $model->manufacturer_id) == $manufacturer->id)>
                                {{ $manufacturer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                 <div>
                    <label for="hardware_category_id" class="block font-semibold">Categoría</label>
                    <select name="hardware_category_id" id="hardware_category_id" class="form-input w-full mt-1" required>
                        <option value="">-- Selecciona --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('hardware_category_id', $model->hardware_category_id) == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-8 flex justify-end space-x-2">
                <a href="{{ route('asset-management.models.index') }}" class="btn bg-gray-200 text-gray-700">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection