@extends('layouts.guest-rutas')

@section('content')
<style>
    @keyframes pulse-ring {
        0% { transform: scale(0.33); opacity: 1; }
        80%, 100% { opacity: 0; }
    }
    .glass-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .pattern-grid {
        background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
        background-size: 24px 24px;
    }
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px; width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9; border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1; border-radius: 4px;
    }
</style>

<div class="min-h-screen bg-slate-50 pattern-grid pb-20" x-data="maniobristaView('{{ $estadoActual }}', '{{ $numero_empleado }}', '{{ $googleMapsApiKey }}', {{ $facturasPendientes->values()->toJson() }})">
    
    <div class="relative bg-[#2c3856] pb-24 overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
        <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
        
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-12">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6 relative z-10">
                <div class="text-center md:text-left">
                    <span class="inline-block py-1 px-3 rounded-full bg-white/10 border border-white/20 text-blue-200 text-xs font-mono tracking-widest uppercase mb-2 backdrop-blur-sm">
                        Panel de Asignación
                    </span>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">
                        Guía <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-400">{{ $guia->guia }}</span>
                    </h2>
                </div>
                <div class="flex flex-col items-center md:items-end">
                    <p class="text-blue-200 text-xs uppercase tracking-widest mb-1">Maniobrista Asignado</p>
                    <span class="px-4 py-2 rounded-lg font-bold shadow-lg backdrop-blur-md border border-white/10 bg-white/5 text-white font-mono" x-text="'{{ $numero_empleado }}'"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-20">
        
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-xl shadow-lg flex items-center gap-3">
                <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-lg flex items-center gap-3">
                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            </div>
        @endif

        <div class="glass-panel rounded-2xl shadow-[0_20px_50px_rgba(8,_112,_184,_0.07)] overflow-hidden ring-1 ring-slate-900/5 p-6 md:p-8 space-y-8">
            
            <div x-show="currentState === 'Llegada a carga'" class="text-center py-6">
                <div class="relative inline-block mb-6">
                    <div class="absolute inset-0 bg-blue-200 rounded-full animate-ping opacity-25"></div>
                    <div class="relative w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center ring-4 ring-blue-50/50">
                        <svg class="w-10 h-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Paso 1: Llegada a Carga</h3>
                <p class="text-slate-500 mb-8 max-w-sm mx-auto">Confirma tu llegada al punto de recolección para iniciar el proceso.</p>
                <button @click="openEventModal('Llegada a carga')" class="w-full max-w-sm mx-auto justify-center inline-flex items-center px-8 py-4 bg-[#2c3856] text-white rounded-xl font-bold text-lg shadow-xl hover:bg-[#1e2742] transition-all transform hover:-translate-y-1 active:scale-95">
                    Registrar Llegada
                </button>
            </div>

            <div x-show="currentState === 'Inicio de ruta'" class="text-center py-6">
                <div class="w-20 h-20 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-6 ring-4 ring-orange-50/50">
                    <svg class="w-10 h-10 text-[#ff9c00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Paso 2: Inicio de Ruta</h3>
                <p class="text-slate-500 mb-8 max-w-sm mx-auto">La carga ha finalizado. Registra el inicio del viaje.</p>
                <button @click="openEventModal('Inicio de ruta')" class="w-full max-w-sm mx-auto justify-center inline-flex items-center px-8 py-4 bg-gradient-to-r from-[#ff9c00] to-orange-600 text-white rounded-xl font-bold text-lg shadow-xl hover:to-orange-700 transition-all transform hover:-translate-y-1 active:scale-95">
                    Registrar Inicio de Ruta
                </button>
            </div>

            <div x-show="currentState === 'En Ruta (Entregas)'">
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></span>
                    <h3 class="text-xl font-bold text-slate-800">Paso 3: Operación en Destino</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <button @click="openEventModal('Llegada a destino')" class="group relative overflow-hidden w-full p-6 bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md transition-all text-left">
                        <div class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                        <div class="relative z-10">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 mb-4">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                            <h4 class="font-bold text-slate-800">Registrar Llegada</h4>
                            <p class="text-xs text-slate-500 mt-1">Confirmar arribo con cliente</p>
                        </div>
                    </button>

                    <button @click="openDeliveryModal()" class="group relative overflow-hidden w-full p-6 bg-[#2c3856] rounded-2xl shadow-lg hover:shadow-xl transition-all text-left">
                        <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                        <div class="relative z-10">
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center text-white mb-4 backdrop-blur-sm">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                            <h4 class="font-bold text-white">Entregar Evidencias</h4>
                            <p class="text-xs text-blue-200 mt-1">Subir fotos de maniobra</p>
                        </div>
                    </button>
                </div>
                
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-100">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Estado de Facturas</h4>
                    <div class="space-y-3">
                        @foreach($guia->facturas as $factura)
                            <div class="flex flex-col sm:flex-row justify-between items-center p-4 rounded-xl border transition-all {{ $factura->evidenciasManiobra->isNotEmpty() ? 'bg-white border-green-200 shadow-sm' : 'bg-slate-100/50 border-slate-200' }}">
                                <div class="flex items-center gap-4 mb-2 sm:mb-0 w-full">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 {{ $factura->evidenciasManiobra->isNotEmpty() ? 'bg-green-100 text-green-600' : 'bg-slate-200 text-slate-500' }}">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-sm">{{ $factura->numero_factura }}</p>
                                        <p class="text-xs text-slate-500">{{ $factura->destino }}</p>
                                    </div>
                                </div>
                                <div class="w-full sm:w-auto text-right">
                                    @if($factura->evidenciasManiobra->isNotEmpty())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                            Completado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-200 text-slate-600">
                                            Pendiente
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div x-show="currentState === 'Completado'" class="py-8 text-center">
                 <div class="bg-green-50 border border-green-100 p-8 rounded-2xl inline-block shadow-lg relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-green-200 rounded-full opacity-50 blur-xl"></div>
                    <div class="relative z-10">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-green-900 mb-2">¡Flujo Completado!</h3>
                        <p class="text-green-700 max-w-xs mx-auto text-sm">Todas las evidencias han sido registradas exitosamente. Buen trabajo.</p>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="isEventModalOpen" style="display: none;" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-[#2c3856]/80 backdrop-blur-sm p-4" @keydown.escape.window="isEventModalOpen = false">
            <div @click.outside="isEventModalOpen = false" class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm border border-white/20">
                <h3 class="text-xl font-bold text-[#2c3856] mb-6" x-text="`Registrar: ${evento.tipo}`"></h3>
                <form id="event-form" action="{{ route('maniobrista.guia.event.store', ['guia' => $guia->guia, 'empleado' => $numero_empleado]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="evento_tipo" x-model="evento.tipo">
                    <input type="hidden" name="latitud" id="event-latitud-input">
                    <input type="hidden" name="longitud" id="event-longitud-input">
                    <input type="hidden" name="municipio" id="event-municipio-input">
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Evidencia (Cámara obligatoria)</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="evidencia-evento" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-white transition-colors">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <p class="mb-2 text-sm text-gray-500 font-medium">Tocar para abrir cámara</p>
                                </div>
                                <input type="file" name="evidencia" id="evidencia-evento" accept="image/*" capture="camera" required class="hidden" onchange="document.getElementById('file-chosen-event').textContent = this.files[0].name">
                            </label>
                        </div>
                        <p id="file-chosen-event" class="text-xs text-center text-[#ff9c00] mt-2 font-medium"></p>
                    </div>
                    
                    <p x-show="locationError" x-text="locationError" class="text-red-600 text-xs bg-red-50 p-2 rounded border border-red-100"></p>
                    
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="isEventModalOpen = false" class="px-5 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-colors text-sm">Cancelar</button>
                        <button type="button" @click="submitSingleEventForm()" :disabled="isLoading" class="px-5 py-2.5 bg-gradient-to-r from-[#ff9c00] to-orange-600 text-white rounded-xl font-bold hover:to-orange-700 shadow-lg shadow-orange-500/30 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                             <span x-show="!isLoading">Confirmar</span>
                             <span x-show="isLoading" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Enviando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="isDeliveryModalOpen" style="display: none;" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-[#2c3856]/80 backdrop-blur-sm p-4" @keydown.escape.window="isDeliveryModalOpen = false">
            <div @click.outside="isDeliveryModalOpen = false" class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg max-h-[90vh] flex flex-col border border-white/20">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-[#2c3856]">Registrar Entrega</h3>
                    <button @click="isDeliveryModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form id="evidencias-form" action="{{ route('maniobrista.guia.evidencias.store', ['guia' => $guia->guia, 'empleado' => $numero_empleado]) }}" method="POST" enctype="multipart/form-data" class="flex-grow flex flex-col overflow-hidden">
                    @csrf
                    <input type="hidden" name="latitud" id="evidencias-latitud-input">
                    <input type="hidden" name="longitud" id="evidencias-longitud-input">
                    <input type="hidden" name="municipio" id="evidencias-municipio-input">
                    
                    <p class="text-sm text-slate-500 mb-4 bg-blue-50 p-3 rounded-lg border border-blue-100">
                        Selecciona las facturas y adjunta la evidencia fotográfica correspondiente.
                    </p>
                    
                    <div class="flex-grow overflow-y-auto pr-2 custom-scrollbar space-y-4">
                        <template x-for="factura in pendingInvoices" :key="factura.id">
                            <div class="border border-gray-200 p-4 rounded-xl bg-slate-50 hover:bg-white transition-colors" :class="{'ring-2 ring-indigo-500 bg-indigo-50': selectedInvoices.includes(factura.id)}">
                                <label class="flex items-start cursor-pointer w-full">
                                    <input type="checkbox" :value="factura.id" @change="toggleFiles(factura.id, $event.target.checked)" class="mt-1 h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                    <div class="ml-3 flex-1">
                                        <p class="font-bold text-slate-800" x-text="factura.numero_factura"></p>
                                        <p class="text-xs text-slate-500" x-text="factura.destino"></p>
                                    </div>
                                </label>
                                
                                <div x-show="selectedInvoices.includes(factura.id)" x-transition class="mt-4 pl-8 border-t border-gray-200/50 pt-3">
                                    <label :for="'evidencia-' + factura.id" class="w-full flex items-center justify-center px-4 py-3 bg-white border border-dashed border-gray-300 text-gray-700 rounded-lg font-bold text-xs hover:bg-gray-50 transition-colors cursor-pointer shadow-sm">
                                        <svg class="w-5 h-5 mr-2 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span>Subir Fotos (Max 3)</span>
                                    </label>
                                    <input type="file" :id="'evidencia-' + factura.id" :name="`evidencias[${factura.id}][]`" accept="image/*" multiple class="hidden evidencia-input" onchange="updateFileList(this)">
                                    <div :id="'file-list-' + factura.id" class="text-xs text-slate-500 mt-2 font-mono bg-slate-100 p-2 rounded hidden"></div>
                                </div>
                            </div>
                        </template>
                        <div x-show="pendingInvoices.length === 0" class="text-center py-8">
                            <p class="text-slate-400 font-medium">No hay facturas pendientes</p>
                        </div>
                    </div>

                    <p x-show="locationError" x-text="locationError" class="text-red-600 text-xs bg-red-50 p-2 rounded border border-red-100 mt-2"></p>
                    
                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="isDeliveryModalOpen = false" class="px-5 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-colors text-sm">Cancelar</button>
                        <button type="button" @click="submitEvidenciasForm()" :disabled="isLoading" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-500/30 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                             <span x-show="!isLoading">Confirmar Entregas</span>
                             <span x-show="isLoading" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Enviando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places"></script>
<script>
    function updateFileList(input) {
        const fileListContainer = document.getElementById(`file-list-${input.id.split('-')[1]}`);
        fileListContainer.innerHTML = '';
        if (input.files.length > 0) {
            fileListContainer.classList.remove('hidden');
            const list = document.createElement('ul'); list.className = 'list-disc list-inside';
            for (const file of input.files) {
                const listItem = document.createElement('li'); listItem.textContent = file.name; list.appendChild(listItem);
            }
            fileListContainer.appendChild(list);
        } else {
            fileListContainer.classList.add('hidden');
        }
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('maniobristaView', (estadoActual, numEmpleado, apiKey, facturasPendientes) => ({
            isLoading: false, locationError: '', isEventModalOpen: false, isDeliveryModalOpen: false,
            currentState: estadoActual, evento: { tipo: '', empleado: numEmpleado }, googleMapsApiKey: apiKey,
            pendingInvoices: facturasPendientes, selectedInvoices: [],

            openEventModal(tipoDeEvento) { this.evento.tipo = tipoDeEvento; this.isEventModalOpen = true; },
            openDeliveryModal() { this.selectedInvoices = []; this.isDeliveryModalOpen = true; },

            toggleFiles(facturaId, isChecked) {
                if (isChecked) {
                    if (!this.selectedInvoices.includes(facturaId)) this.selectedInvoices.push(facturaId);
                } else {
                    this.selectedInvoices = this.selectedInvoices.filter(id => id !== facturaId);
                }
            },
            
            async getMunicipality(lat, lng) {
                try {
                    const response = await fetch(`https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${this.googleMapsApiKey}`);
                    const data = await response.json();
                    if (data.status === 'OK' && data.results.length > 0) {
                        for (const result of data.results) {
                            for (const component of result.address_components) {
                                if (component.types.includes('locality')) return component.long_name;
                                if (component.types.includes('administrative_area_level_1')) var state = component.long_name;
                            }
                        }
                        return state || 'Ubicación no encontrada';
                    } return 'N/A';
                } catch (error) { console.error('Error en geocodificación:', error); return 'Error de red'; }
            },

            submitSingleEventForm() {
                this.isLoading = true; this.locationError = '';
                if (!document.getElementById('evidencia-evento').files.length) {
                    alert('La foto de evidencia es obligatoria.'); this.isLoading = false; return;
                }
                this.getLocationAndSubmit('event-form');
            },

            submitEvidenciasForm() {
                if (this.selectedInvoices.length === 0) { alert('Debes seleccionar al menos una factura.'); return; }
                let filesAttached = false;
                this.selectedInvoices.forEach(id => {
                    const input = document.getElementById(`evidencia-${id}`);
                    if (input && input.files.length > 0) filesAttached = true;
                });
                if (!filesAttached) {
                    if (!confirm('No has adjuntado archivos. ¿Continuar?')) return;
                }
                this.isLoading = true; this.locationError = '';
                this.getLocationAndSubmit('evidencias-form');
            },

            getLocationAndSubmit(formId) {
                if (!navigator.geolocation) { this.locationError = 'Geolocalización no soportada.'; this.isLoading = false; return; }
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const lat = position.coords.latitude; const lng = position.coords.longitude;
                        document.getElementById(`${formId.split('-')[0]}-latitud-input`).value = lat;
                        document.getElementById(`${formId.split('-')[0]}-longitud-input`).value = lng;
                        
                        const municipio = await this.getMunicipality(lat, lng);
                        document.getElementById(`${formId.split('-')[0]}-municipio-input`).value = municipio;
                        
                        document.getElementById(formId).submit();
                    },
                    () => { this.locationError = 'Activa el GPS y otorga los permisos.'; this.isLoading = false; },
                    { enableHighAccuracy: true }
                );
            }
        }));
    });
</script>
@endpush