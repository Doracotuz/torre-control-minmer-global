@extends('layouts.app')

@section('content')

<style>
    :root {
        /* Paleta de colores completa y refinada */
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
        --color-text-primary: #2b2b2b;
        --color-text-secondary: #666666;
        --color-surface: #ffffff;
        --color-background: #f3f4f6;
        --color-border: #d1d5db; /* Color de borde estandarizado */
    }
    body { 
        background-color: var(--color-background); 
    }
    /* Estilos de formulario consistentes */
    .form-label { font-weight: 600; color: var(--color-text-primary); margin-bottom: 0.5rem; display: block; }
    .form-input, .form-select, .form-textarea { 
        border-radius: 0.5rem; 
        border: 1px solid var(--color-border); 
        transition: all 150ms ease-in-out; 
        width: 100%; 
        padding: 0.75rem 1rem; 
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus { 
        --tw-ring-color: var(--color-primary); 
        border-color: var(--color-primary); 
        box-shadow: 0 0 0 2px var(--tw-ring-color); 
    }
    /* Estilos de botones consistentes */
    .btn { padding: 0.65rem 1.25rem; border-radius: 0.5rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm); transition: all 200ms ease-in-out; transform: translateY(0); border: 1px solid transparent; }
    .btn:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .btn-primary { background-color: var(--color-primary); color: white; }
    .btn-primary:hover { background-color: #212a41; } /* Darker primary */
    .btn-secondary { background-color: var(--color-surface); color: var(--color-text-secondary); border-color: var(--color-border); }
    .btn-secondary:hover { background-color: #f9fafb; }
</style>

<div class="w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <header class="mb-8">
        <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">Crear Nuevo Ticket</h1>
        <p class="text-[var(--color-text-secondary)] mt-2">Describe tu problema o solicitud y el equipo de TI se pondrá en contacto.</p>
    </header>

    <div class="bg-white p-8 rounded-xl shadow-lg">
        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                
                {{-- Título --}}
                <div class="md:col-span-2">
                    <label for="title" class="form-label">Título</label>
                    <input type="text" id="title" name="title" class="form-input" required value="{{ old('title') }}" placeholder="Ej: Mi computadora no enciende">
                </div>

                {{-- Categoría y Subcategoría con AlpineJS --}}
                <div class="md:col-span-2" 
                     x-data="{ selectedCategory: '{{ old('category_id') }}', categories: {{ $categories->toJson() }}, subCategories: [] }"
                     x-init="if(selectedCategory) { subCategories = categories.find(c => c.id == selectedCategory)?.sub_categories || [] }">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div>
                            <label for="category_id" class="form-label">Categoría</label>
                            <select id="category_id" name="category_id" class="form-select" required
                                    x-model="selectedCategory"
                                    @change="subCategories = categories.find(c => c.id == selectedCategory)?.sub_categories || []">
                                <option value="">-- Selecciona una categoría --</option>
                                <template x-for="category in categories" :key="category.id">
                                    <option :value="category.id" x-text="category.name"></option>
                                </template>
                            </select>
                        </div>
                        <div x-show="subCategories.length > 0" x-transition>
                            <label for="ticket_sub_category_id" class="form-label">Subcategoría</label>
                            <select id="ticket_sub_category_id" name="ticket_sub_category_id" class="form-select">
                                <option value="">-- Selecciona una subcategoría --</option>
                                <template x-for="subCategory in subCategories" :key="subCategory.id">
                                    <option :value="subCategory.id" x-text="subCategory.name" :selected="subCategory.id == '{{ old('ticket_sub_category_id') }}'"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>
                
                {{-- Activo Relacionado --}}
                @if($userAssets->isNotEmpty())
                <div>
                    <label for="hardware_asset_id" class="form-label">Activo Relacionado (Opcional)</label>
                    <select id="hardware_asset_id" name="hardware_asset_id" class="form-select">
                        <option value="">-- Ninguno --</option>
                        @foreach($userAssets as $asset)
                            <option value="{{ $asset->id }}" @selected(old('hardware_asset_id') == $asset->id)>
                                {{ $asset->model->name }} ({{ $asset->asset_tag }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                {{-- Prioridad --}}
                <div>
                    <label for="priority" class="form-label">Prioridad</label>
                    <select id="priority" name="priority" class="form-select" required>
                        <option value="Baja" @selected(old('priority') == 'Baja')>Baja</option>
                        <option value="Media" @selected(old('priority', 'Media') == 'Media')>Media</option>
                        <option value="Alta" @selected(old('priority') == 'Alta')>Alta</option>
                    </select>
                </div>
                
                {{-- Descripción --}}
                <div class="md:col-span-2">
                    <label for="description" class="form-label">Descripción Detallada</label>
                    <textarea id="description" name="description" rows="6" class="form-textarea" required placeholder="Por favor, sé lo más específico posible...">{{ old('description') }}</textarea>
                </div>

                {{-- **NUEVO** Componente para Adjuntar Archivo --}}
                <div class="md:col-span-2" x-data="{ fileName: '' }">
                    <label class="form-label">Adjuntar Fotografía (Opcional)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="attachment" class="relative cursor-pointer bg-white rounded-md font-medium text-[var(--color-primary)] hover:text-[var(--color-accent)] focus-within:outline-none">
                                    <span>Sube un archivo</span>
                                    <input id="attachment" name="attachment" type="file" class="sr-only" @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                                </label>
                                <p class="pl-1">o arrástralo aquí</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF hasta 10MB</p>
                        </div>
                    </div>
                    <div x-show="fileName" class="mt-2 text-sm text-gray-600" x-cloak>
                        <span class="font-semibold">Archivo seleccionado:</span> <span x-text="fileName"></span>
                    </div>
                </div>

            </div>

            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end items-center space-x-4">
                <a href="{{-- {{ route('tickets.index') }} --}}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Enviar Ticket</button>
            </div>
        </form>
    </div>
</div>
@endsection