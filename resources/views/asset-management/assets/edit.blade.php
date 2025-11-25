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
        position: absolute; top: 50%; left: 1rem; transform: translateY(-50%); color: #9ca3af; pointer-events: none; z-index: 10;
    }
    .modern-input, .modern-select {
        padding-left: 2.75rem; border: 1px solid #e5e7eb; border-radius: 0.75rem; transition: all 0.3s ease; background-color: #f9fafb; width: 100%;
    }
    .modern-input:focus, .modern-select:focus {
        background-color: #fff; border-color: var(--primary); outline: none; box-shadow: 0 0 0 4px rgba(44, 56, 86, 0.1);
    }
</style>

<div class="min-h-screen bg-[#f3f4f6] pb-20">
    
    <div class="bg-gradient-to-r from-[var(--primary)] to-[var(--primary-light)] pt-10 pb-32 px-4 sm:px-6 lg:px-8 shadow-xl rounded-b-[3rem] relative overflow-hidden">
        <div class="absolute right-0 top-0 h-full w-1/2 bg-white/5 skew-x-12 transform origin-top-right"></div>
        <div class="absolute left-10 bottom-10 text-[6rem] md:text-[8rem] text-white/5 font-bold leading-none select-none whitespace-nowrap">
            {{ $asset->asset_tag }}
        </div>

        <div class="max-w-7xl mx-auto relative z-10 flex flex-col md:flex-row justify-between items-end">
            <div class="text-white">
                <div class="flex items-center gap-2 mb-2">
                    <span class="bg-blue-500/20 text-blue-100 border border-blue-400/30 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-md">
                        Edición
                    </span>
                    <span class="bg-white/10 px-3 py-1 rounded-full text-xs font-mono border border-white/20">
                        ID: {{ $asset->id }}
                    </span>
                </div>
                <h1 class="text-4xl md:text-5xl font-black tracking-tight">Editar Activo</h1>
                <p class="mt-2 text-blue-100 text-lg flex items-center">
                    <i class="fas fa-box mr-2 opacity-70"></i> {{ $asset->model->name }}
                </p>
            </div>
            <div class="mt-6 md:mt-0 flex gap-3">
                 <a href="{{ route('asset-management.assets.show', $asset) }}" class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl backdrop-blur-md transition-all border border-white/10 font-medium">
                    <i class="fas fa-eye mr-2"></i> Ver Detalles
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-0">
        <form action="{{ route('asset-management.assets.update', $asset) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    <div class="glass-card rounded-2xl p-6 md:p-8 bg-white">
                        <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-4 mb-6 flex items-center">
                            <i class="fas fa-pen-nib text-[var(--primary)] mr-2 bg-blue-50 p-2 rounded-lg"></i>
                            Datos Principales
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Etiqueta de Activo</label>
                                <div class="relative"><i class="fas fa-tag form-floating-icon"></i><input type="text" name="asset_tag" class="modern-input py-3" value="{{ old('asset_tag', $asset->asset_tag) }}" required></div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Número de Serie</label>
                                <div class="relative"><i class="fas fa-barcode form-floating-icon"></i><input type="text" name="serial_number" class="modern-input py-3" value="{{ old('serial_number', $asset->serial_number) }}" required></div>
                            </div>
                        </div>

                         <div x-data="{ models: {{ $groupedModels->toJson() }}, categories: {{ $groupedModels->keys()->toJson() }}, selectedCategory: '{{ old('category', $currentCategoryName) }}', filteredModels: [] }"
                             x-init="filteredModels = models[selectedCategory] || []">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Categoría</label>
                                    <div class="relative"><i class="fas fa-layer-group form-floating-icon"></i><select x-model="selectedCategory" @change="filteredModels = models[selectedCategory] || []" class="modern-select py-3 appearance-none"><option value="">--</option><template x-for="category in categories" :key="category"><option :value="category" x-text="category" :selected="category === selectedCategory"></option></template></select></div>
                                </div>
                                <div x-show="selectedCategory" x-transition>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Modelo</label>
                                    <div class="relative"><i class="fas fa-box-open form-floating-icon"></i><select name="hardware_model_id" class="modern-select py-3 appearance-none"><template x-for="model in filteredModels" :key="model.id"><option :value="model.id" x-text="model.name" :selected="model.id == {{ old('hardware_model_id', $asset->hardware_model_id) }}"></option></template></select></div>
                                </div>
                            </div>
                            
                            <div x-show="['Laptop', 'Desktop', 'Celular', 'Ipad', 'Monitor'].includes(selectedCategory)" x-transition class="bg-gray-50 rounded-xl p-5 border border-gray-100 mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div><label class="text-xs font-bold text-gray-500">Procesador</label><input type="text" name="cpu" class="modern-input py-2 text-sm" value="{{ old('cpu', $asset->cpu) }}"></div>
                                <div><label class="text-xs font-bold text-gray-500">RAM</label><input type="text" name="ram" class="modern-input py-2 text-sm" value="{{ old('ram', $asset->ram) }}"></div>
                                <div><label class="text-xs font-bold text-gray-500">Storage</label><input type="text" name="storage" class="modern-input py-2 text-sm" value="{{ old('storage', $asset->storage) }}"></div>
                                <div><label class="text-xs font-bold text-gray-500">MAC Address</label><input type="text" name="mac_address" class="modern-input py-2 text-sm" value="{{ old('mac_address', $asset->mac_address) }}"></div>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card rounded-2xl p-6 md:p-8 bg-white">
                        <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-4 mb-6 flex items-center">
                            <i class="fas fa-images text-[var(--accent)] mr-2 bg-orange-50 p-2 rounded-lg"></i>
                            Galería
                        </h3>
                         <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            @foreach([1, 2, 3] as $i)
                                @php
                                    $photoPath = $asset->{"photo_{$i}_path"};
                                    $photoUrl = $photoPath ? Storage::disk('s3')->url($photoPath) : null;
                                @endphp
                                <div x-data="{ hasPhoto: {{ $photoPath ? 'true' : 'false' }}, markedForDeletion: false, previewUrl: '{{ $photoUrl }}' }" class="relative group">
                                    <input type="hidden" name="remove_photo_{{ $i }}" x-model="markedForDeletion">
                                    <input name="photo_{{ $i }}" type="file" x-ref="photo{{ $i }}" class="hidden" @change="
                                        const reader = new FileReader();
                                        reader.onload = (e) => { previewUrl = e.target.result; hasPhoto = true; markedForDeletion = false; };
                                        reader.readAsDataURL($event.target.files[0]);
                                    ">
                                    
                                    <div @click="$refs.photo{{ $i }}.click()" class="aspect-square rounded-xl border-2 border-dashed border-gray-300 flex flex-col items-center justify-center cursor-pointer hover:border-[var(--primary)] overflow-hidden relative" :class="{'border-solid border-[var(--primary)]': hasPhoto && !markedForDeletion}">
                                        <template x-if="hasPhoto && !markedForDeletion">
                                            <img :src="previewUrl" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!hasPhoto || markedForDeletion">
                                            <div class="text-center p-4 text-gray-400"><i class="fas fa-camera text-2xl"></i><p class="text-xs mt-2">Foto {{ $i }}</p></div>
                                        </template>
                                        
                                        <template x-if="hasPhoto && !markedForDeletion">
                                            <button @click.stop.prevent="markedForDeletion = true" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow hover:scale-110">&times;</button>
                                        </template>
                                    </div>
                                    <p x-show="markedForDeletion" class="text-xs text-red-500 text-center mt-1 font-bold">Se eliminará</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-[var(--primary)] sticky top-6">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-6">Estado y Control</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Estatus Actual</label>
                                <div class="relative"><i class="fas fa-info-circle form-floating-icon"></i>
                                    <select name="status" class="modern-select py-3">
                                        @foreach(['En Almacén', 'Asignado', 'En Reparación', 'Prestado', 'De Baja'] as $st)
                                            <option value="{{ $st }}" @selected(old('status', $asset->status) == $st)>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Ubicación</label>
                                <div class="relative"><i class="fas fa-map-marker-alt form-floating-icon"></i>
                                    <select name="site_id" class="modern-select py-3">
                                        @foreach($sites as $site)<option value="{{ $site->id }}" @selected(old('site_id', $asset->site_id) == $site->id)>{{ $site->name }}</option>@endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="border-t border-gray-100 pt-4 my-4"></div>
                            
                            <div><label class="block text-sm font-bold text-gray-700 mb-1">Notas</label><textarea name="notes" rows="4" class="w-full rounded-lg border-gray-300 text-sm bg-gray-50">{{ old('notes', $asset->notes) }}</textarea></div>

                            <button type="submit" class="w-full bg-[var(--primary)] hover:bg-[var(--primary-light)] text-white font-bold py-4 rounded-xl shadow-lg transition-all transform hover:-translate-y-1 mt-4 flex items-center justify-center">
                                <i class="fas fa-sync-alt mr-2"></i> Actualizar Activo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection