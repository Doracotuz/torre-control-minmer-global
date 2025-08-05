@extends('layouts.app')

@section('content')

<style>
    :root {
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
        --color-text-primary: #2b2b2b;
        --color-text-secondary: #666666;
        --color-surface: #ffffff;
        --color-background: #f3f4f6;
        --color-success: #10B981;
        --color-danger: #EF4444;
        --color-warning: #F59E0B;
    }
    body { 
        background-color: var(--color-background); 
    }
    .btn { padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1); }
    .btn-primary { background-color: var(--color-primary); color: var(--color-surface); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1); }
    .form-label { font-weight: 600; color: var(--color-primary); margin-bottom: 0.5rem; display: block; }
    .form-input { border-radius: 0.5rem; border-color: #e5e7eb; transition: all 0.3s ease; width: 100%; padding: 0.75rem 1rem; }
    .form-input:focus { --tw-ring-color: var(--color-accent); border-color: var(--color-accent); outline: none; box-shadow: 0 0 0 2px var(--tw-ring-color); }
</style>

<div class="w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <header class="mb-8">
        <h1 class="text-4xl font-bold text-[var(--color-text-primary)]">Crear Nuevo Ticket</h1>
        <p class="text-[var(--color-text-secondary)] mt-2">Describe tu problema o solicitud y el equipo de TI se pondrá en contacto.</p>
    </header>

    <div class="bg-white p-8 rounded-xl shadow-lg">
        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="title" class="form-label">Título</label>
                    <input type="text" id="title" name="title" class="form-input" placeholder="Ej: Problema con la impresora del 2º piso" value="{{ old('title') }}" required>
                    @error('title') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="category_id" class="form-label">Categoría</label>
                    <select id="category_id" name="category_id" class="form-input" required>
                        <option value="">-- Selecciona una categoría --</option>
                        @forelse($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @empty
                            <option value="" disabled>No hay categorías disponibles. Contacta a un administrador.</option>
                        @endforelse
                    </select>
                    @error('category_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label for="description" class="form-label">Descripción Detallada</label>
                    <textarea id="description" name="description" rows="6" class="form-input" placeholder="Por favor, sé lo más específico posible. Incluye mensajes de error, qué estabas haciendo, etc." required>{{ old('description') }}</textarea>
                    @error('description') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="priority" class="form-label">Prioridad</label>
                    <select id="priority" name="priority" class="form-input" required>
                        <option value="Baja" @selected(old('priority') == 'Baja')>Baja</option>
                        <option value="Media" @selected(old('priority', 'Media') == 'Media')>Media</option>
                        <option value="Alta" @selected(old('priority') == 'Alta')>Alta</option>
                    </select>
                    @error('priority') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="attachment" class="form-label">Adjuntar Fotografía (Opcional)</label>
                    <input type="file" id="attachment" name="attachment" class="form-input" accept="image/png, image/jpeg, image/gif">
                    @error('attachment') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-8 text-right">
                <button type="submit" class="btn btn-primary">Enviar Ticket</button>
            </div>
        </form>
    </div>
</div>
@endsection