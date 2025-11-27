@extends('layouts.app')

@section('content')

@php
    $user = Auth::user();
    $isSuperAdmin = $user && $user->is_area_admin && $user->area?->name === 'Administración';
    $isClosed = !is_null($maintenance->end_date);
    $canEdit = !$isClosed || $isSuperAdmin;
@endphp

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
    }
    
    .modern-input {
        padding-left: 2.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        background-color: #f9fafb;
    }
    .modern-input:focus {
        background-color: #fff;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(44, 56, 86, 0.1);
    }

    fieldset:disabled {
        opacity: 0.7;
        pointer-events: none;
        filter: grayscale(0.5);
    }
</style>

<div class="min-h-screen bg-[#f3f4f6] pb-20">
    
    <div class="bg-gradient-to-r from-[var(--primary)] to-[var(--primary-light)] pt-10 pb-32 px-4 sm:px-6 lg:px-8 shadow-xl rounded-b-[3rem] relative overflow-hidden">
        <div class="absolute right-0 top-0 h-full w-1/2 bg-white/5 skew-x-12 transform origin-top-right"></div>
        <div class="absolute left-10 bottom-10 text-[10rem] text-white/5 font-bold leading-none select-none">
            #{{ $maintenance->id }}
        </div>

        <div class="max-w-7xl mx-auto relative z-10 flex flex-col md:flex-row justify-between items-start md:items-end">
            <div class="text-white">
                <div class="flex items-center gap-3 mb-2">
                    <span class="bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-white/10">
                        {{ $maintenance->type }}
                    </span>
                    @if($maintenance->end_date)
                        <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">
                            Cerrado
                        </span>
                    @else
                        <span class="bg-amber-500 text-white px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg animate-pulse">
                            En Proceso
                        </span>
                    @endif
                </div>
                <h1 class="text-4xl md:text-5xl font-black tracking-tight">Mantenimiento</h1>
                <p class="mt-2 text-blue-100 text-lg flex items-center">
                    <i class="fas fa-cube mr-2 opacity-70"></i> {{ $maintenance->asset->model->name }} 
                    <span class="mx-2 opacity-50">|</span> 
                    <span class="font-mono opacity-90">{{ $maintenance->asset->asset_tag }}</span>
                </p>
            </div>
            <div class="mt-6 md:mt-0 flex gap-3">
                <a href="{{ route('asset-management.maintenances.index') }}" class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl backdrop-blur-md transition-all border border-white/10">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
                <a href="{{ route('asset-management.maintenances.pdf', $maintenance) }}" target="_blank" class="px-5 py-3 bg-red-500/80 hover:bg-red-500 text-white rounded-xl backdrop-blur-md transition-all border border-white/10 shadow-lg">
                    <i class="fas fa-file-pdf mr-2"></i> PDF
                </a>
            </div>
        </div>
    </div>

    @if(!$canEdit)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-24 mb-6 relative z-30">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r shadow-lg flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-lock text-red-500 text-xl mr-4"></i>
                    <div>
                        <p class="text-red-700 font-bold">Registro Bloqueado</p>
                        <p class="text-red-600 text-sm">Este mantenimiento ha finalizado. Solo el Super Administrador puede hacer cambios.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-20">
        <form action="{{ route('asset-management.maintenances.update', $maintenance) }}" 
              method="POST" 
              enctype="multipart/form-data"
              x-data="{ 
                  endDate: '{{ old('end_date', $maintenance->end_date ? $maintenance->end_date->format('Y-m-d') : '') }}',
                  finalStatus: '{{ $maintenance->asset->status === 'De Baja' ? 'De Baja' : 'En Almacén' }}'
              }">
            @csrf
            @method('PUT')

            <fieldset {{ !$canEdit ? 'disabled' : '' }} class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="glass-card rounded-2xl p-6 md:p-8 bg-white">
                        <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-4 mb-6 flex items-center">
                            <i class="fas fa-clipboard-list text-[var(--primary)] mr-2 bg-blue-50 p-2 rounded-lg"></i>
                            Reporte Técnico
                        </h3>

                        <div class="space-y-6">
                            <div class="bg-blue-50/50 p-5 rounded-xl border border-blue-100 transition-all"
                                 :class="endDate ? 'ring-2 ring-green-400 bg-green-50/30' : ''">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Fecha de Finalización / Cierre</label>
                                <div class="relative">
                                    <i class="fas fa-calendar-check form-floating-icon z-10"></i>
                                    <input type="date" 
                                           name="end_date" 
                                           x-model="endDate"
                                           class="modern-input w-full py-3 form-input cursor-pointer">
                                </div>
                            </div>

                            <div x-show="endDate" x-transition 
                                 class="bg-orange-50/50 p-4 rounded-xl border border-orange-100">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Conclusión del Servicio <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <i class="fas fa-gavel form-floating-icon z-10"></i>
                                    <select name="final_asset_status" 
                                            x-model="finalStatus"
                                            @change="if(finalStatus === 'process') { endDate = ''; finalStatus = 'En Almacén'; }"
                                            class="modern-input w-full py-3 form-select cursor-pointer">
                                        <option value="En Almacén">Equipo Reparado (Enviar a Almacén)</option>
                                        <option value="De Baja">Equipo Irreparable (Dar de Baja)</option>
                                        <option disabled>──────────────────────────</option>
                                        <option value="process" class="text-blue-600 font-bold">
                                            &#8634; Regresar a "En Proceso"
                                        </option>
                                    </select>
                                </div>
                                <p class="text-xs mt-2" :class="finalStatus === 'De Baja' ? 'text-red-600 font-bold' : 'text-gray-500'">
                                    <i class="fas" :class="finalStatus === 'De Baja' ? 'fa-exclamation-triangle' : 'fa-info-circle'"></i>
                                    <span x-text="finalStatus === 'De Baja' ? 'ADVERTENCIA: El activo cambiará a estatus De Baja.' : 'El activo quedará disponible para nueva asignación.'"></span>
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Acciones Realizadas <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <i class="fas fa-tools form-floating-icon top-6"></i>
                                    <textarea name="actions_taken" rows="4" required
                                              class="modern-input w-full py-3 form-textarea"
                                              placeholder="Describa detalladamente la reparación o mantenimiento...">{{ old('actions_taken', $maintenance->actions_taken) }}</textarea>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Insumos / Partes</label>
                                    <div class="relative">
                                        <i class="fas fa-microchip form-floating-icon top-6"></i>
                                        <textarea name="parts_used" rows="3"
                                                  class="modern-input w-full py-3 form-textarea"
                                                  placeholder="Ej: Batería, SSD...">{{ old('parts_used', $maintenance->parts_used) }}</textarea>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Costo Total</label>
                                    <div class="relative">
                                        <i class="fas fa-dollar-sign form-floating-icon"></i>
                                        <input type="number" name="cost" step="0.01" 
                                               value="{{ old('cost', $maintenance->cost) }}"
                                               class="modern-input w-full py-3 form-input text-lg font-mono" placeholder="0.00">
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1 pl-2">Moneda Nacional (MXN)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card rounded-2xl p-6 md:p-8 bg-white">
                        <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-4 mb-6 flex items-center">
                            <i class="fas fa-camera text-[var(--accent)] mr-2 bg-orange-50 p-2 rounded-lg"></i>
                            Evidencia Fotográfica
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @for ($i = 1; $i <= 3; $i++)
                                @php
                                    $photoPath = "photo_{$i}_path";
                                    $currentPhoto = $maintenance->$photoPath;
                                    $photoUrl = $currentPhoto ? Storage::disk('s3')->url($currentPhoto) : null;
                                @endphp

                                <div x-data="{ 
                                        hasPhoto: {{ $currentPhoto ? 'true' : 'false' }}, 
                                        markedForDeletion: false,
                                        previewUrl: '{{ $photoUrl }}'
                                     }" 
                                     class="relative group">
                                    
                                    <input type="hidden" name="remove_photo_{{ $i }}" x-model="markedForDeletion">
                                    
                                    <div x-show="hasPhoto && !markedForDeletion" class="relative h-40 rounded-xl overflow-hidden border border-gray-200 shadow-sm group-hover:shadow-md transition-all">
                                        <img :src="previewUrl" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                                        
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <button type="button" @click="markedForDeletion = true" 
                                                    class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transform hover:scale-110 transition-all shadow-lg" title="Eliminar Foto">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                        <div class="absolute top-2 left-2 bg-black/50 text-white text-[10px] px-2 py-1 rounded backdrop-blur-sm">
                                            Foto {{ $i }}
                                        </div>
                                    </div>

                                    <div x-show="!hasPhoto || markedForDeletion" 
                                         class="h-40 border-2 border-dashed border-gray-300 rounded-xl flex flex-col items-center justify-center bg-gray-50 hover:bg-white hover:border-[var(--primary)] transition-all cursor-pointer group-hover:shadow-md relative">
                                        
                                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-300 mb-2 group-hover:text-[var(--primary)] transition-colors"></i>
                                        <span class="text-xs font-semibold text-gray-500 group-hover:text-[var(--primary)]">Subir Foto {{ $i }}</span>
                                        
                                        <input type="file" name="photo_{{ $i }}" accept="image/*"
                                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                               @change="hasPhoto = true; markedForDeletion = false;">
                                    </div>
                                    
                                    <p x-show="markedForDeletion" class="text-[10px] text-red-500 mt-1 text-center font-bold animate-pulse">
                                        <i class="fas fa-times-circle"></i> Se eliminará al guardar
                                    </p>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    
                    <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-[var(--primary)]">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Sobre el Activo</h4>
                        
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-16 w-16 rounded-xl bg-gray-100 flex items-center justify-center text-3xl text-[var(--primary)]">
                                @if($maintenance->asset->model->category->name == 'Laptop') <i class="fas fa-laptop"></i>
                                @elseif($maintenance->asset->model->category->name == 'Celular') <i class="fas fa-mobile-alt"></i>
                                @else <i class="fas fa-box"></i> @endif
                            </div>
                            <div>
                                <div class="font-bold text-gray-800 leading-tight">{{ $maintenance->asset->model->name }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $maintenance->asset->model->manufacturer->name }}</div>
                            </div>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between border-b border-gray-100 pb-2">
                                <span class="text-gray-500">Serie:</span>
                                <span class="font-mono font-medium text-gray-800">{{ $maintenance->asset->serial_number }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-100 pb-2">
                                <span class="text-gray-500">Ubicación:</span>
                                <span class="font-medium text-gray-800">{{ $maintenance->asset->site->name }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-100 pb-2">
                                <span class="text-gray-500">Diagnóstico Inicial:</span>
                            </div>
                            <p class="text-gray-600 bg-gray-50 p-3 rounded-lg text-xs italic">
                                "{{ $maintenance->diagnosis }}"
                            </p>
                        </div>
                    </div>

                    @if ($maintenance->substitute_asset_id)
                        <div class="bg-amber-50 rounded-2xl p-5 border border-amber-200 shadow-sm relative overflow-hidden">
                            <div class="absolute -right-4 -top-4 text-amber-100 text-6xl opacity-50">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="relative z-10">
                                <h4 class="text-amber-800 font-bold text-sm flex items-center mb-2">
                                    <i class="fas fa-exclamation-triangle mr-2"></i> Activo Sustituto
                                </h4>
                                <p class="text-xs text-amber-700 mb-3 leading-relaxed">
                                    El equipo <strong>{{ $maintenance->substituteAsset->asset_tag }}</strong> está prestado temporalmente.
                                </p>
                                <div class="text-[10px] bg-white/50 p-2 rounded border border-amber-200 text-amber-900 font-semibold text-center">
                                    Se devolverá automáticamente al finalizar.
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($canEdit)
                        <div class="sticky top-6">
                            <button type="submit" 
                                    class="w-full py-4 rounded-xl font-bold text-white shadow-lg shadow-blue-900/20 transform transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex items-center justify-center group"
                                    :class="endDate ? 'bg-gradient-to-r from-green-500 to-green-600' : 'bg-[var(--primary)] hover:bg-[var(--primary-light)]'">
                                
                                <template x-if="endDate">
                                    <span class="flex items-center">
                                        <i class="fas fa-check-circle text-xl mr-3 animate-bounce"></i>
                                        <span>FINALIZAR TICKET</span>
                                    </span>
                                </template>
                                
                                <template x-if="!endDate">
                                    <span class="flex items-center">
                                        <i class="fas fa-save text-xl mr-3 group-hover:rotate-12 transition-transform"></i>
                                        <span>GUARDAR AVANCES</span>
                                    </span>
                                </template>
                            </button>
                            <p class="text-center text-xs text-gray-400 mt-3">
                                Todos los cambios quedan registrados en el historial.
                            </p>
                        </div>
                    @endif

                </div>
            </fieldset>
        </form>
    </div>
</div>
@endsection