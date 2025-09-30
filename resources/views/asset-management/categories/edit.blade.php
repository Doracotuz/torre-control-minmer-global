@extends('layouts.app')

@section('content')
<div class="w-full max-w-lg mx-auto px-4 py-8">
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Editar Categoría</h1>
        <form action="{{ route('asset-management.categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block font-semibold">Nombre de la Categoría</label>
                <input type="text" id="name" name="name" class="form-input w-full mt-1" value="{{ old('name', $category->name) }}" required>
            </div>
            <div class="mt-8 flex justify-end space-x-2">
                <a href="{{ route('asset-management.categories.index') }}" class="btn bg-gray-200 text-gray-700">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection