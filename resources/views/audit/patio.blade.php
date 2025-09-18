@extends('layouts.audit-layout')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8" 
     x-data="{
        presentaManiobra: {{ old('presenta_maniobra', $audit->guia?->audit_patio_presenta_maniobra) ? 'true' : 'false' }},
        fotoUnidadPreview: null,
        fotoLlantasPreview: null,
        previewFile(event, target) {
            if (event.target.files.length > 0) {
                const reader = new FileReader();
                reader.onload = (e) => { this[target] = e.target.result; };
                reader.readAsDataURL(event.target.files[0]);
            } else {
                this[target] = null;
            }
        }
     }">

    <a href="{{ route('audit.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 mb-4 inline-block transition-colors">
        &larr; Volver al Dashboard
    </a>
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xl">
        <div class="border-b pb-4 mb-6">
            <h1 class="text-3xl font-bold text-[#2c3856]">Auditoría de Arribo de Unidad</h1>
            <p class="text-gray-600 mt-1">Guía: <span class="font-semibold text-gray-800">{{ $audit->guia?->guia ?? 'N/A' }}</span></p>
            <p class="text-gray-500 text-sm mt-1">Ubicación de Auditoría: <span class="font-medium">{{ $audit->location }}</span></p>
        </div>

        <form action="{{ route('audit.patio.store', $audit) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                    <p class="font-bold">Por favor, corrige los siguientes errores:</p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-6">
                
                {{-- SECCIÓN DE DATOS GENERALES --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label class="block text-sm font-medium text-gray-700">Operador</label><input type="text" name="operador" value="{{ old('operador', $audit->guia?->operador) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                    <div><label class="block text-sm font-medium text-gray-700">Placas</label><input type="text" name="placas" value="{{ old('placas', $audit->guia?->placas) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                    <div><label class="block text-sm font-medium text-gray-700">Fecha de Arribo</label><input type="date" name="arribo_fecha" value="{{ old('arribo_fecha', now()->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                    <div><label class="block text-sm font-medium text-gray-700">Hora de Arribo</label><input type="time" name="arribo_hora" value="{{ old('arribo_hora', now()->format('H:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                </div>

                {{-- SECCIÓN DE ESTADO DE UNIDAD --}}
                <div class="space-y-4 bg-gray-50 p-4 rounded-lg border">
                    <h3 class="font-semibold text-gray-800 mb-2">Estado de la Unidad</h3>
                    <div><label class="block text-sm font-medium text-gray-700">Estado de la Caja</label><select name="caja_estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required><option @selected(old('caja_estado', $audit->guia?->audit_patio_caja_estado) == 'Bueno')>Bueno</option><option @selected(old('caja_estado', $audit->guia?->audit_patio_caja_estado) == 'Regular')>Regular</option><option @selected(old('caja_estado', $audit->guia?->audit_patio_caja_estado) == 'Malo')>Malo</option></select></div>
                    <div><label class="block text-sm font-medium text-gray-700">Estado de las Llantas</label><select name="llantas_estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required><option @selected(old('llantas_estado', $audit->guia?->audit_patio_llantas_estado) == 'Bueno')>Bueno</option><option @selected(old('llantas_estado', $audit->guia?->audit_patio_llantas_estado) == 'Regular')>Regular</option><option @selected(old('llantas_estado', $audit->guia?->audit_patio_llantas_estado) == 'Malo')>Malo</option></select></div>
                    <div><label class="block text-sm font-medium text-gray-700">Nivel de Combustible</label><select name="combustible_nivel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required><option @selected(old('combustible_nivel', $audit->guia?->audit_patio_combustible_nivel) == 'Lleno')>Lleno</option><option @selected(old('combustible_nivel', $audit->guia?->audit_patio_combustible_nivel) == '3/4')>3/4</option><option @selected(old('combustible_nivel', $audit->guia?->audit_patio_combustible_nivel) == '1/2')>1/2</option><option @selected(old('combustible_nivel', $audit->guia?->audit_patio_combustible_nivel) == '1/4')>1/4</option><option @selected(old('combustible_nivel', $audit->guia?->audit_patio_combustible_nivel) == 'Reserva')>Reserva</option></select></div>
                    <div><label class="block text-sm font-medium text-gray-700">Equipo de Sujeción</label><select name="equipo_sujecion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required><option @selected(old('equipo_sujecion', $audit->guia?->audit_patio_equipo_sujecion) == 'No aplica')>No aplica</option><option @selected(old('equipo_sujecion', $audit->guia?->audit_patio_equipo_sujecion) == 'Barras logísticas')>Barras logísticas</option><option @selected(old('equipo_sujecion', $audit->guia?->audit_patio_equipo_sujecion) == 'Bandas')>Bandas</option><option @selected(old('equipo_sujecion', $audit->guia?->audit_patio_equipo_sujecion) == 'Eslingas')>Eslingas</option><option @selected(old('equipo_sujecion', $audit->guia?->audit_patio_equipo_sujecion) == 'Ambas')>Ambas</option></select></div>
                    
                    <div class="pt-4 border-t">
                        <label class="flex items-center"><input type="checkbox" name="presenta_maniobra" value="1" x-model="presentaManiobra" class="rounded mr-2 text-indigo-600 shadow-sm">Presenta Maniobra</label>
                        <div x-show="presentaManiobra" x-transition class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Número de Personas para Maniobra</label>
                            <input type="number" name="maniobra_personas" value="{{ old('maniobra_personas', $audit->guia?->audit_patio_maniobra_personas) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="1">
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN DE FOTOGRAFÍAS CON MEJOR ESTÉTICA --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fotografía de la Unidad <span class="text-red-500">*</span></label>
                        <input type="file" name="foto_unidad" x-ref="fotoUnidadInput" @change="previewFile($event, 'fotoUnidadPreview')" class="hidden" accept="image/*" capture="environment" required>
                        <div @click="$refs.fotoUnidadInput.click()" class="cursor-pointer border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors">
                            <template x-if="!fotoUnidadPreview">
                                <div><i class="fas fa-camera text-4xl text-gray-400"></i><p class="mt-2 text-sm text-gray-600">Clic para seleccionar imagen</p></div>
                            </template>
                            <template x-if="fotoUnidadPreview">
                                <img :src="fotoUnidadPreview" class="max-h-48 mx-auto rounded-md">
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fotografía de Llantas <span class="text-red-500">*</span></label>
                        <input type="file" name="foto_llantas" x-ref="fotoLlantasInput" @change="previewFile($event, 'fotoLlantasPreview')" class="hidden" accept="image/*" capture="environment" required>
                        <div @click="$refs.fotoLlantasInput.click()" class="cursor-pointer border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors">
                            <template x-if="!fotoLlantasPreview">
                                <div><i class="fas fa-dot-circle text-4xl text-gray-400"></i><p class="mt-2 text-sm text-gray-600">Clic para seleccionar imagen</p></div>
                            </template>
                            <template x-if="fotoLlantasPreview">
                                <img :src="fotoLlantasPreview" class="max-h-48 mx-auto rounded-md">
                            </template>
                        </div>
                    </div>
                </div>
                
                <div class="pt-4 border-t">
                    <button type="submit" class="w-full px-6 py-3 bg-purple-600 text-white rounded-lg font-bold shadow-lg hover:bg-purple-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Confirmar Arribo y Avanzar a Carga
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection