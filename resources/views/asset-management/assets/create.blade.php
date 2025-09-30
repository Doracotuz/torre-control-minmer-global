@extends('layouts.app')

@section('content')
<style>
    :root {
        /* Tu paleta de colores */
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
        --color-text-primary: #2b2b2b;
        --color-text-secondary: #666666;
        --color-surface: #ffffff;
        
        /* Colores de apoyo */
        --color-primary-dark: #212a41; /* Versión oscurecida para hover */
        --color-background: #f3f4f6;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    }

    body {
        background-color: var(--color-background);
    }

    /* Estilos para formularios (Inputs, Selects, Textareas) */
    .form-input, .form-select, .form-textarea {
        border-radius: 0.5rem;
        border-color: #d1d5db;
        transition: all 150ms ease-in-out;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        --tw-ring-color: var(--color-primary);
        border-color: var(--color-primary);
        box-shadow: 0 0 0 2px var(--tw-ring-color);
    }
    label.form-label {
        font-weight: 600;
        color: var(--color-text-primary);
        margin-bottom: 0.5rem;
        display: block;
    }
    
    /* Botones */
    .btn {
        padding: 0.65rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow-sm);
        transition: all 200ms ease-in-out;
        transform: translateY(0);
    }
    .btn:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
    .btn-primary {
        background-color: var(--color-primary);
        color: white;
    }
    .btn-primary:hover {
        background-color: var(--color-primary-dark);
    }
    .btn-secondary {
        background-color: var(--color-surface);
        color: var(--color-text-secondary);
        border: 1px solid #d1d5db;
    }
    .btn-secondary:hover {
        background-color: #f9fafb;
    }

    /* Badges de Estado */
    .status-badge { 
        padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; 
        font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .status-asignado { background-color: #3B82F6; color: white; }
    .status-en-almacen { background-color: #10B981; color: white; }
    .status-en-reparacion { background-color: var(--color-accent); color: white; }
    .status-prestado { background-color: #8B5CF6; color: white; }
    .status-de-baja { background-color: var(--color-text-secondary); color: white; }
</style>
<div class="w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <a href="{{ url()->previous() }}" class="text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-primary)] transition-colors mb-2 inline-block">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
        <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">Registrar Nuevo Activo</h1>
        <p class="text-[var(--color-text-secondary)] mt-1">Completa los detalles del nuevo hardware.</p>
    </div>
    
    <div class="bg-white p-8 rounded-xl shadow-lg mt-8">
        <form action="{{ route('asset-management.assets.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                
                {{-- Columna Izquierda --}}
                <div class="space-y-6">
                    <div>
                        <label for="asset_tag" class="form-label">Etiqueta de Activo</label>
                        <input type="text" id="asset_tag" name="asset_tag" class="form-input w-full" value="{{ old('asset_tag') }}" required>
                    </div>
                    <div>
                        <label for="serial_number" class="form-label">Número de Serie</label>
                        <input type="text" id="serial_number" name="serial_number" class="form-input w-full" value="{{ old('serial_number') }}" required>
                    </div>

                    {{-- AlpineJS Componente para Categoría/Modelo --}}
                    <div x-data="{ models: {{ $groupedModels->toJson() }}, categories: {{ $groupedModels->keys()->toJson() }}, selectedCategory: '{{ old('category') }}', filteredModels: [] }"
                         x-init="filteredModels = models[selectedCategory] || []" class="space-y-6">
                        <div>
                            <label for="category" class="form-label">Categoría</label>
                            <select id="category" x-model="selectedCategory" @change="filteredModels = models[selectedCategory] || []" class="form-select w-full">
                                <option value="">-- Selecciona una categoría --</option>
                                <template x-for="category in categories" :key="category">
                                    <option :value="category" x-text="category"></option>
                                </template>
                            </select>
                        </div>
                        <div x-show="selectedCategory" x-transition>
                            <label for="hardware_model_id" class="form-label">Modelo</label>
                            <select id="hardware_model_id" name="hardware_model_id" class="form-select w-full" required>
                                <option value="">-- Selecciona un modelo --</option>
                                <template x-for="model in filteredModels" :key="model.id">
                                    <option :value="model.id" x-text="model.name" :selected="model.id == {{ old('hardware_model_id') }}"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Campos dinámicos para Laptops/Desktops --}}
                        <div x-show="selectedCategory === 'Laptop' || selectedCategory === 'Desktop'" x-transition class="space-y-6 border-t border-gray-200 pt-6 mt-6">
                            <h3 class="font-bold text-lg text-[var(--color-text-primary)]">Especificaciones Técnicas</h3>
                            <div><label for="cpu" class="form-label">Procesador</label><input type="text" name="cpu" id="cpu" class="form-input w-full" value="{{ old('cpu') }}"></div>
                            <div><label for="ram" class="form-label">RAM (e.g., 16GB)</label><input type="text" name="ram" id="ram" class="form-input w-full" value="{{ old('ram') }}"></div>
                            <div><label for="storage" class="form-label">Almacenamiento (e.g., 512GB SSD)</label><input type="text" name="storage" id="storage" class="form-input w-full" value="{{ old('storage') }}"></div>
                            <div><label for="mac_address" class="form-label">MAC Address</label><input type="text" name="mac_address" id="mac_address" class="form-input w-full" value="{{ old('mac_address') }}"></div>
                        </div>

                        {{-- Campos dinámicos para Celulares --}}
                        <div x-show="selectedCategory === 'Celular'" x-transition class="space-y-6 border-t border-gray-200 pt-6 mt-6">
                             <h3 class="font-bold text-lg text-[var(--color-text-primary)]">Detalles de Telefonía</h3>
                             <div>
                                <label for="phone_plan_type" class="form-label">Tipo de Plan</label>
                                <select name="phone_plan_type" id="phone_plan_type" class="form-select w-full"><option value="">-- Selecciona --</option><option value="Prepago">Prepago</option><option value="Plan">Plan</option></select>
                            </div>
                            <div><label for="phone_number" class="form-label">Número Telefónico</label><input type="text" name="phone_number" id="phone_number" class="form-input w-full" value="{{ old('phone_number') }}"></div>
                        </div>
                    </div>
                </div>

                {{-- Columna Derecha --}}
                <div class="space-y-6">
                    <div>
                        <label for="site_id" class="form-label">Sitio / Ubicación</label>
                        <select id="site_id" name="site_id" class="form-select w-full" required>
                            @foreach($sites as $site)<option value="{{ $site->id }}" @selected(old('site_id') == $site->id)>{{ $site->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="form-label">Estatus</label>
                        <select id="status" name="status" class="form-select w-full" required>
                            <option value="En Almacén" selected>En Almacén</option><option value="Asignado">Asignado</option><option value="En Reparación">En Reparación</option><option value="Prestado">Prestado</option><option value="De Baja">De Baja</option>
                        </select>
                    </div>
                    <div>
                        <label for="purchase_date" class="form-label">Fecha de Compra</label>
                        <input type="date" id="purchase_date" name="purchase_date" class="form-input w-full" value="{{ old('purchase_date') }}">
                    </div>
                    <div>
                        <label for="warranty_end_date" class="form-label">Fin de Garantía</label>
                        <input type="date" id="warranty_end_date" name="warranty_end_date" class="form-input w-full" value="{{ old('warranty_end_date') }}">
                    </div>
                    <div>
                        <label for="notes" class="form-label">Notas Adicionales</label>
                        <textarea id="notes" name="notes" rows="5" class="form-textarea w-full">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end items-center space-x-4">
                <a href="{{ route('asset-management.dashboard') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Activo</button>
            </div>
        </form>
    </div>
</div>
@endsection