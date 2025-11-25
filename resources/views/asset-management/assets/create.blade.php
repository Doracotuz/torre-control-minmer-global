@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary: #2c3856;
        --primary-light: #3d4d75;
        --accent: #ff9c00;
        --bg-color: #f3f4f6;
    }
    [x-cloak] { display: none !important; }

    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
    }
    
    .form-floating-icon {
        position: absolute;
        top: 50%;
        left: 1rem;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
        z-index: 10;
    }
    
    .modern-input, .modern-select, .modern-textarea {
        padding-left: 2.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        background-color: #f9fafb;
        width: 100%;
    }
    .modern-input:focus, .modern-select:focus, .modern-textarea:focus {
        background-color: #fff;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(44, 56, 86, 0.1);
        outline: none;
    }
    .modern-textarea { padding-top: 1rem; padding-left: 1rem; }
    .form-floating-icon.top-icon { top: 1.5rem; }
</style>

<div class="min-h-screen bg-[#f3f4f6] pb-20">
    
    <div class="bg-gradient-to-r from-[var(--primary)] to-[var(--primary-light)] pt-10 pb-32 px-4 sm:px-6 lg:px-8 shadow-xl rounded-b-[3rem] relative overflow-hidden">
        <div class="absolute right-0 top-0 h-full w-1/2 bg-white/5 skew-x-12 transform origin-top-right"></div>
        <div class="absolute left-10 bottom-10 text-[8rem] text-white/5 font-bold leading-none select-none">
            NUEVO
        </div>

        <div class="max-w-7xl mx-auto relative z-10 flex flex-col md:flex-row justify-between items-end">
            <div class="text-white">
                <div class="flex items-center gap-2 mb-2">
                    <span class="bg-green-500/20 text-green-100 border border-green-400/30 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-md">
                        Alta de Inventario
                    </span>
                </div>
                <h1 class="text-4xl md:text-5xl font-black tracking-tight">Registrar Activo</h1>
                <p class="mt-2 text-blue-100 text-lg">Ingresa los detalles técnicos y administrativos del nuevo equipo.</p>
            </div>
            <div class="mt-6 md:mt-0">
                <a href="{{ route('asset-management.dashboard') }}" class="group flex items-center px-5 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl backdrop-blur-md transition-all border border-white/10">
                    <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                    Volver al Tablero
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-0">
        <form action="{{ route('asset-management.assets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="glass-card rounded-2xl p-6 md:p-8 bg-white">
                        <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-4 mb-6 flex items-center">
                            <i class="fas fa-fingerprint text-[var(--primary)] mr-2 bg-blue-50 p-2 rounded-lg"></i>
                            Identificación
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Etiqueta de Activo <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <i class="fas fa-tag form-floating-icon"></i>
                                    <input type="text" name="asset_tag" class="modern-input py-3" value="{{ old('asset_tag') }}" placeholder="Ej: ACT-2025-001" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Número de Serie <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <i class="fas fa-barcode form-floating-icon"></i>
                                    <input type="text" name="serial_number" class="modern-input py-3" value="{{ old('serial_number') }}" placeholder="Ej: XJ9-12345" required>
                                </div>
                            </div>
                        </div>

                        <div x-data="{ models: {{ $groupedModels->toJson() }}, categories: {{ $groupedModels->keys()->toJson() }}, selectedCategory: '{{ old('category') }}', filteredModels: [] }"
                             x-init="filteredModels = models[selectedCategory] || []">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Categoría <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <i class="fas fa-layer-group form-floating-icon"></i>
                                        <select x-model="selectedCategory" @change="filteredModels = models[selectedCategory] || []" class="modern-select py-3 appearance-none" required>
                                            <option value="">Selecciona...</option>
                                            <template x-for="category in categories" :key="category">
                                                <option :value="category" x-text="category"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                                <div x-show="selectedCategory" x-transition>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Modelo <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <i class="fas fa-box-open form-floating-icon"></i>
                                        <select name="hardware_model_id" class="modern-select py-3 appearance-none" required>
                                            <option value="">Selecciona...</option>
                                            <template x-for="model in filteredModels" :key="model.id">
                                                <option :value="model.id" x-text="model.name" :selected="model.id == {{ old('hardware_model_id', 'null') }}"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div x-show="['Laptop', 'Desktop', 'Celular', 'Ipad', 'Monitor'].includes(selectedCategory)" x-transition class="bg-gray-50 rounded-xl p-5 border border-gray-100 mt-6">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Especificaciones Técnicas</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600">Procesador</label>
                                        <input type="text" name="cpu" class="modern-input py-2 text-sm" placeholder="Ej: Intel i7" value="{{ old('cpu') }}">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600">RAM</label>
                                        <input type="text" name="ram" class="modern-input py-2 text-sm" placeholder="Ej: 16GB" value="{{ old('ram') }}">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600">Almacenamiento</label>
                                        <input type="text" name="storage" class="modern-input py-2 text-sm" placeholder="Ej: 512GB SSD" value="{{ old('storage') }}">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600">MAC Address</label>
                                        <input type="text" name="mac_address" class="modern-input py-2 text-sm" placeholder="00:00:00:00:00:00" value="{{ old('mac_address') }}">
                                    </div>
                                </div>
                            </div>

                             <div x-show="selectedCategory === 'Celular'" x-transition class="bg-blue-50 rounded-xl p-5 border border-blue-100 mt-4">
                                <h4 class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-4">Datos de Línea</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600">Número</label>
                                        <input type="text" name="phone_number" class="modern-input py-2 text-sm" placeholder="55 1234 5678" value="{{ old('phone_number') }}">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600">Plan</label>
                                        <select name="phone_plan_type" class="modern-select py-2 text-sm">
                                            <option value="">Selecciona...</option>
                                            <option value="Plan">Plan Empresarial</option>
                                            <option value="Prepago">Prepago</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card rounded-2xl p-6 md:p-8 bg-white">
                        <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-4 mb-6 flex items-center">
                            <i class="fas fa-camera text-[var(--accent)] mr-2 bg-orange-50 p-2 rounded-lg"></i>
                            Evidencia Fotográfica
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            @foreach([1, 2, 3] as $i)
                                <div x-data="{ photoName: null, photoPreview: null }" class="group relative">
                                    <input name="photo_{{ $i }}" type="file" x-ref="photo{{ $i }}" class="hidden" @change="
                                        photoName = $event.target.files[0].name;
                                        const reader = new FileReader();
                                        reader.onload = (e) => { photoPreview = e.target.result };
                                        reader.readAsDataURL($event.target.files[0]);
                                    ">
                                    
                                    <div @click="$refs.photo{{ $i }}.click()" 
                                         class="aspect-square rounded-xl border-2 border-dashed border-gray-300 flex flex-col items-center justify-center cursor-pointer hover:border-[var(--primary)] hover:bg-blue-50 transition-all overflow-hidden relative"
                                         :class="{'border-solid border-[var(--primary)]': photoPreview}">
                                        
                                        <template x-if="!photoPreview">
                                            <div class="text-center p-4">
                                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-300 group-hover:text-[var(--primary)] transition-colors"></i>
                                                <p class="text-xs font-bold text-gray-500 mt-2">Foto {{ $i }}</p>
                                            </div>
                                        </template>

                                        <template x-if="photoPreview">
                                            <img :src="photoPreview" class="w-full h-full object-cover">
                                        </template>

                                        <template x-if="photoPreview">
                                            <button @click.stop.prevent="photoName = null; photoPreview = null; $refs.photo{{ $i }}.value = null" 
                                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow-lg hover:scale-110 transition-transform">
                                                &times;
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-[var(--primary)] sticky top-6">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-6">Detalles Administrativos</h4>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Estatus Inicial</label>
                                <div class="relative">
                                    <i class="fas fa-info-circle form-floating-icon"></i>
                                    <select name="status" class="modern-select py-3" required>
                                        <option value="En Almacén" selected>En Almacén</option>
                                        <option value="Asignado">Asignado</option>
                                        <option value="En Reparación">En Reparación</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Ubicación / Sitio</label>
                                <div class="relative">
                                    <i class="fas fa-map-marker-alt form-floating-icon"></i>
                                    <select name="site_id" class="modern-select py-3" required>
                                        @foreach($sites as $site)
                                            <option value="{{ $site->id }}" @selected(old('site_id') == $site->id)>{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 pt-4 my-4"></div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Fecha Compra</label>
                                <input type="date" name="purchase_date" class="modern-input py-2 pl-4" value="{{ old('purchase_date') }}">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Fin Garantía</label>
                                <input type="date" name="warranty_end_date" class="modern-input py-2 pl-4" value="{{ old('warranty_end_date') }}">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Notas</label>
                                <textarea name="notes" rows="4" class="modern-textarea w-full rounded-lg border-gray-300 text-sm" placeholder="Detalles adicionales...">{{ old('notes') }}</textarea>
                            </div>

                            <button type="submit" class="w-full bg-[var(--primary)] hover:bg-[var(--primary-light)] text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-900/20 transition-all transform hover:-translate-y-1 mt-4">
                                <i class="fas fa-save mr-2"></i> Guardar Activo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection