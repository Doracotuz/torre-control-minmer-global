@extends('layouts.app')

@section('content')
<div class="w-full max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold">Editar Activo: {{ $asset->asset_tag }}</h1>
    
    <div class="bg-white p-8 rounded-xl shadow-lg mt-8">
        <form action="{{ route('asset-management.assets.update', $asset) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Columna Izquierda --}}
                <div class="space-y-6">
                    <div>
                        <label for="asset_tag" class="block font-semibold">Etiqueta de Activo</label>
                        <input type="text" id="asset_tag" name="asset_tag" class="form-input w-full mt-1" value="{{ old('asset_tag', $asset->asset_tag) }}" required>
                    </div>
                    <div>
                        <label for="serial_number" class="block font-semibold">Número de Serie</label>
                        <input type="text" id="serial_number" name="serial_number" class="form-input w-full mt-1" value="{{ old('serial_number', $asset->serial_number) }}" required>
                    </div>
                    <div x-data="{ models: {{ $groupedModels->toJson() }}, categories: {{ $groupedModels->keys()->toJson() }}, selectedCategory: '{{ old('category', $currentCategoryName) }}', filteredModels: [] }"
                         x-init="filteredModels = models[selectedCategory] || []">
                        <div>
                            <label for="category" class="block font-semibold">Categoría</label>
                            <select id="category" x-model="selectedCategory" @change="filteredModels = models[selectedCategory] || []" class="form-input w-full mt-1">
                                <option value="">-- Selecciona una categoría --</option>
                                <template x-for="category in categories" :key="category">
                                    <option :value="category" x-text="category"></option>
                                </template>
                            </select>
                        </div>
                        <div x-show="selectedCategory" class="mt-6">
                            <label for="hardware_model_id" class="block font-semibold">Modelo</label>
                            <select id="hardware_model_id" name="hardware_model_id" class="form-input w-full mt-1" required>
                                <option value="">-- Selecciona un modelo --</option>
                                <template x-for="model in filteredModels" :key="model.id">
                                    <option :value="model.id" x-text="model.name" :selected="model.id == {{ old('hardware_model_id', $asset->hardware_model_id) }}"></option>
                                </template>
                            </select>
                        </div>
                        {{-- Campos dinámicos --}}
                        <div x-show="selectedCategory === 'Laptop' || selectedCategory === 'Desktop'" class="space-y-6 border-t pt-4 mt-4">
                            <h3 class="font-bold text-lg">Especificaciones Técnicas</h3>
                            <div><label for="cpu" class="block font-semibold">Procesador</label><input type="text" name="cpu" id="cpu" class="form-input w-full mt-1" value="{{ old('cpu', $asset->cpu) }}"></div>
                            <div><label for="ram" class="block font-semibold">RAM</label><input type="text" name="ram" id="ram" class="form-input w-full mt-1" value="{{ old('ram', $asset->ram) }}"></div>
                            <div><label for="storage" class="block font-semibold">Almacenamiento</label><input type="text" name="storage" id="storage" class="form-input w-full mt-1" value="{{ old('storage', $asset->storage) }}"></div>
                            <div><label for="mac_address" class="block font-semibold">MAC Address</label><input type="text" name="mac_address" id="mac_address" class="form-input w-full mt-1" value="{{ old('mac_address', $asset->mac_address) }}"></div>
                        </div>
                        <div x-show="selectedCategory === 'Celular'" class="space-y-6 border-t pt-4 mt-4">
                             <h3 class="font-bold text-lg">Detalles de Telefonía</h3>
                            <div>
                                <label for="phone_plan_type" class="block font-semibold">Tipo de Plan</label>
                                <select name="phone_plan_type" id="phone_plan_type" class="form-input w-full mt-1">
                                    <option value="">-- Selecciona --</option>
                                    <option value="Prepago" @selected(old('phone_plan_type', $asset->phone_plan_type) == 'Prepago')>Prepago</option>
                                    <option value="Plan" @selected(old('phone_plan_type', $asset->phone_plan_type) == 'Plan')>Plan</option>
                                </select>
                            </div>
                            <div><label for="phone_number" class="block font-semibold">Número Telefónico</label><input type="text" name="phone_number" id="phone_number" class="form-input w-full mt-1" value="{{ old('phone_number', $asset->phone_number) }}"></div>
                        </div>
                    </div>
                </div>

                {{-- Columna Derecha --}}
                <div class="space-y-6">
                    <div>
                        <label for="site_id" class="block font-semibold">Sitio / Ubicación</label>
                        <select id="site_id" name="site_id" class="form-input w-full mt-1" required>
                            @foreach($sites as $site)
                            <option value="{{ $site->id }}" @selected(old('site_id', $asset->site_id) == $site->id)>{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block font-semibold">Estatus</label>
                        <select id="status" name="status" class="form-input w-full mt-1" required>
                            <option value="En Almacén" @selected(old('status', $asset->status) == 'En Almacén')>En Almacén</option>
                            <option value="Asignado" @selected(old('status', $asset->status) == 'Asignado')>Asignado</option>
                            <option value="En Reparación" @selected(old('status', $asset->status) == 'En Reparación')>En Reparación</option>
                            <option value="Prestado" @selected(old('status', $asset->status) == 'Prestado')>Prestado</option>
                            <option value="De Baja" @selected(old('status', $asset->status) == 'De Baja')>De Baja</option>
                        </select>
                    </div>
                    <div>
                        <label for="purchase_date" class="block font-semibold">Fecha de Compra</label>
                        <input type="date" id="purchase_date" name="purchase_date" class="form-input w-full mt-1" value="{{ old('purchase_date', $asset->purchase_date) }}">
                    </div>
                    <div>
                        <label for="warranty_end_date" class="block font-semibold">Fin de Garantía</label>
                        <input type="date" id="warranty_end_date" name="warranty_end_date" class="form-input w-full mt-1" value="{{ old('warranty_end_date', $asset->warranty_end_date) }}">
                    </div>
                    <div>
                        <label for="notes" class="block font-semibold">Notas Adicionales</label>
                        <textarea id="notes" name="notes" rows="4" class="form-input w-full mt-1">{{ old('notes', $asset->notes) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="mt-8 text-right">
                <a href="{{ route('asset-management.assets.show', $asset) }}" class="btn bg-gray-200 text-gray-700">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Activo</button>
            </div>
        </form>
    </div>
</div>
@endsection