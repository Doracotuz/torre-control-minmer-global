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
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
</style>

<div class="min-h-screen bg-slate-50 pattern-grid pb-20" x-data="operatorView({{ json_encode($guia->load('facturas')) }})">
    
    <div class="relative bg-[#2c3856] pb-24 overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
        <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
        
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-12">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6 relative z-10">
                <div class="text-center md:text-left">
                    <span class="inline-block py-1 px-3 rounded-full bg-white/10 border border-white/20 text-blue-200 text-xs font-mono tracking-widest uppercase mb-2 backdrop-blur-sm">
                        Panel de Control
                    </span>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">
                        Guía <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-400">{{ $guia->guia }}</span>
                    </h2>
                </div>
                <div class="flex flex-col items-center md:items-end">
                    <p class="text-blue-200 text-xs uppercase tracking-widest mb-1">Estatus Actual</p>
                    <span class="px-4 py-2 rounded-lg font-bold shadow-lg backdrop-blur-md border border-white/10" :class="getBadgeClass(guia.estatus, true)" x-text="guia.estatus"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-20">
        
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-xl shadow-lg flex items-center gap-3">
                <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-lg flex items-center gap-3">
                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-red-800 font-bold">No se pudo guardar:</h3>
                        <ul class="mt-1 list-disc list-inside text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif        

        <div class="glass-panel rounded-2xl shadow-[0_20px_50px_rgba(8,_112,_184,_0.07)] overflow-hidden ring-1 ring-slate-900/5 p-6 md:p-8 space-y-8">
            
            <div x-show="guia.estatus === 'Planeada'" class="text-center py-8">
                <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 ring-4 ring-blue-50/50">
                    <svg class="w-10 h-10 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Iniciar Operación</h3>
                <p class="text-slate-500 mb-8 max-w-sm mx-auto">Confirma el inicio del viaje para comenzar el rastreo en tiempo real.</p>
                
                <form id="start-form" action="{{ route('operador.guia.start', $guia->guia) }}" method="POST">
                    @csrf
                    <input type="hidden" name="latitud" id="start-latitud">
                    <input type="hidden" name="longitud" id="start-longitud">
                    <input type="hidden" name="municipio" id="start-municipio">
                    <button type="button" @click="submitStartForm()" :disabled="isLoading" class="w-full max-w-sm mx-auto flex justify-center items-center px-8 py-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl font-bold text-lg hover:from-green-600 hover:to-green-700 shadow-lg shadow-green-500/30 transform transition hover:-translate-y-1 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isLoading" class="flex items-center gap-2">
                            INICIAR VIAJE <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </span>
                        <span x-show="isLoading" class="flex items-center"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Procesando...</span>
                    </button>
                </form>
            </div>

            <div x-show="guia.estatus === 'Camino a carga'" class="text-center py-6">
                <div class="relative inline-block mb-6">
                    <div class="absolute inset-0 bg-blue-200 rounded-full animate-ping opacity-25"></div>
                    <div class="relative w-16 h-16 bg-[#2c3856] rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-6">Llegada a Punto de Carga</h3>
                <button @click="openModal('Sistema', 'Llegada a carga')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-[#2c3856] text-white rounded-xl font-bold shadow-xl hover:bg-[#1e2742] transition-all">
                    Registrar Llegada
                </button>
            </div>

            <div x-show="guia.estatus === 'En espera de carga'" class="text-center py-6">
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6 text-orange-600">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-6">Finalizar Carga</h3>
                <button @click="openModal('Sistema', 'Fin de carga')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-[#2c3856] text-white rounded-xl font-bold shadow-xl hover:bg-[#1e2742] transition-all">
                    Registrar Fin de Carga
                </button>
            </div>

            <div x-show="guia.estatus === 'Por iniciar ruta'" class="text-center py-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 text-green-600">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 7m0 13V7" /></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-6">Iniciar Ruta a Destino</h3>
                <button @click="openModal('Sistema', 'En ruta')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl font-bold shadow-lg hover:from-green-600 hover:to-green-700 transition-all">
                    Confirmar Salida
                </button>
            </div>

            <div x-show="guia.estatus === 'En tránsito' || guia.estatus === 'En Pernocta'">
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-100 mb-8">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span> Acciones en Ruta
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button x-show="guia.estatus === 'En Pernocta'" @click="openModal('Sistema', 'En ruta')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-green-500 text-white rounded-xl font-bold hover:bg-green-600 shadow-md transition-all">Reanudar Ruta</button>
                        <button x-show="guia.estatus === 'En tránsito'" @click="openModal('Notificacion', 'Pernocta')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-indigo-500 text-white rounded-xl font-bold hover:bg-indigo-600 shadow-md transition-all">
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                            Registrar Pernocta
                        </button>
                        <button @click="openModal('Sistema', 'Llegada a cliente', true)" class="w-full justify-center inline-flex items-center px-6 py-4 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 shadow-md transition-all">
                             <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            Llegada a Cliente
                        </button>
                        
                        <button @click="openEventSelectionModal('Notificacion')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-slate-600 text-white rounded-xl font-bold hover:bg-slate-700 shadow-md transition-all">
                            Notificar Evento
                        </button>
                        <button @click="openEventSelectionModal('Incidencias')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-red-500 text-white rounded-xl font-bold hover:bg-red-600 shadow-md transition-all">
                            Reportar Incidencia
                        </button>
                    </div>
                </div>
                
                <h4 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Gestión de Entregas</h4>
                <div class="space-y-4">
                     <template x-for="factura in guia.facturas" :key="factura.id">
                        <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="flex items-center gap-4 w-full sm:w-auto">
                                <div class="bg-blue-50 p-2 rounded-lg text-blue-600">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                </div>
                                <div>
                                    <p class="font-black text-slate-800 text-lg" x-text="factura.numero_factura"></p>
                                    <p class="text-xs text-slate-500" x-text="factura.destino"></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                                <span class="px-3 py-1 text-xs font-bold rounded-full uppercase tracking-wider" :class="getBadgeClass(factura.estatus_entrega, false)" x-text="factura.estatus_entrega"></span>
                                
                                <div class="flex gap-2">
                                    <button x-show="factura.estatus_entrega === 'En cliente'" @click="openModal('Sistema', 'Proceso de entrega', false, [factura.id])" class="text-xs bg-[#ff9c00] text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-orange-600 transition-colors">Iniciar</button>
                                    <button x-show="factura.estatus_entrega === 'Entregando'" @click="openModal('Entrega', 'Entregada', false, [factura.id])" class="text-xs bg-green-500 text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-green-600 transition-colors">Entregada</button>
                                    <button x-show="factura.estatus_entrega === 'Entregando'" @click="openModal('Entrega', 'No entregada', false, [factura.id])" class="text-xs bg-red-500 text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-red-600 transition-colors">Fallida</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="guia.estatus === 'Completada'" class="py-10 text-center">
                 <div class="bg-green-50 border border-green-100 p-8 rounded-2xl inline-block shadow-lg">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-green-900 mb-2">¡Ruta Finalizada!</h3>
                    <p class="text-green-700">Has concluido todas las entregas de esta guía exitosamente.</p>
                </div>
            </div>
        </div>

        <div x-show="isModalOpen" style="display: none;" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-[#2c3856]/80 backdrop-blur-sm p-4" @keydown.escape.window="isModalOpen = false">
            <div @click.outside="isModalOpen = false" class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg max-h-[90vh] flex flex-col border border-white/20">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-[#2c3856]" x-text="modal.title"></h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                
                <form id="event-form" action="{{ route('operador.guia.event.store', $guia->guia) }}" method="POST" enctype="multipart/form-data" class="flex-grow flex flex-col overflow-hidden">
                    @csrf
                    <input type="hidden" name="tipo" x-model="modal.tipo">
                    <input type="hidden" name="subtipo" x-model="modal.subtipo">
                    <input type="hidden" name="latitud" id="event-latitud">
                    <input type="hidden" name="longitud" id="event-longitud">
                    <input type="hidden" name="municipio" id="event-municipio">

                    <div class="flex-grow overflow-y-auto pr-2 custom-scrollbar space-y-5">
                        
                        <div x-show="modal.isSelection">
                            <label for="subtipo_select" class="block text-sm font-bold text-gray-700 mb-2">Selecciona el tipo de evento</label>
                            <div class="relative">
                                <select id="subtipo_select" x-model="modal.subtipo" name="subtipo" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#ff9c00] focus:ring focus:ring-[#ff9c00]/20 sm:text-sm py-3 px-4 bg-slate-50">
                                    <template x-for="subtype in availableSubtypes" :key="subtype">
                                        <option :value="subtype" x-text="subtype"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        
                        <div x-show="modal.needsInvoices">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Seleccionar Facturas Afectadas</label>
                            <div class="max-h-40 overflow-y-auto border border-gray-200 rounded-xl p-3 bg-slate-50 space-y-2 custom-scrollbar">
                                <template x-for="factura in availableInvoicesForModal" :key="factura.id">
                                    <label class="flex items-center w-full p-2 hover:bg-white rounded-lg transition-colors cursor-pointer border border-transparent hover:border-gray-200">
                                        <input type="checkbox" name="factura_ids[]" :value="factura.id" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00]">
                                        <span class="ml-3 text-sm font-medium text-gray-700" x-text="factura.numero_factura + ' - ' + factura.destino"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Evidencia Fotográfica</label>
                            <div class="flex items-center justify-center w-full">
                                <label for="original-evidencia" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-white transition-colors">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                        <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click para subir</span> o arrastra</p>
                                    </div>
                                    <input type="file" id="original-evidencia" accept="image/*" multiple @change="processImages" class="hidden">
                                </label>
                            </div>
                            <input type="file" name="evidencia[]" id="processed-evidencia" multiple class="hidden">
                            <div class="flex justify-between mt-2">
                                <p class="text-xs text-gray-500" x-text="modal.evidenceRequired ? 'Obligatoria (máx. 10)' : 'Opcional'"></p>
                                <p class="text-xs text-[#ff9c00] font-semibold" id="file-count"></p>
                            </div>
                        </div>

                        <div>
                            <label for="nota" class="block text-sm font-bold text-gray-700 mb-2">Notas Adicionales</label>
                            <textarea name="nota" id="nota" rows="3" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#ff9c00] focus:ring focus:ring-[#ff9c00]/20 sm:text-sm bg-slate-50 px-4 py-3 resize-none placeholder-gray-400" placeholder="Escribe aquí cualquier observación relevante..."></textarea>
                        </div>
                    </div>

                    <p x-show="locationError" x-text="locationError" class="text-red-600 text-sm mt-4 bg-red-50 p-2 rounded border border-red-100"></p>
                    
                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-colors text-sm">Cancelar</button>
                        <button type="button" @click="submitEventForm()" :disabled="isLoading" class="px-5 py-2.5 bg-gradient-to-r from-[#ff9c00] to-orange-600 text-white rounded-xl font-bold hover:to-orange-700 shadow-lg shadow-orange-500/30 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                             <span x-show="!isLoading">Confirmar Evento</span>
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
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places" async defer></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('operatorView', (guiaData) => ({
            guia: guiaData,
            isModalOpen: false,
            isLoading: false,
            locationError: '',
            modal: { title: '', tipo: '', subtipo: '', needsInvoices: false, evidenceRequired: false, isSelection: false },

            eventSubtypes: {
                'Notificacion': ['Alimentos', 'Combustible', 'Sanitario', 'Otro'],
                'Incidencias': ['Rechazo', 'Percance', 'Tráfico', 'Falla mecánica', 'Incidencia con autoridad', 'Otro']
            },
            availableSubtypes: [],

            openModal(tipo, subtipo, needsInvoices = false, fixedFacturaIds = []) {
                this.modal = {
                    title: `Registrar: ${subtipo}`,
                    tipo: tipo,
                    subtipo: subtipo,
                    needsInvoices: needsInvoices,
                    evidenceRequired: (subtipo === 'Entregada' || subtipo === 'No entregada'),
                    fixedFacturaIds: fixedFacturaIds,
                    isSelection: false 
                };
                
                if(fixedFacturaIds.length > 0){
                    this.modal.needsInvoices = false;
                }

                this.isModalOpen = true;
            },

            openEventSelectionModal(tipo) {
                this.availableSubtypes = this.eventSubtypes[tipo] || [];
                this.modal = {
                    title: `Seleccionar ${tipo}`,
                    tipo: tipo,
                    subtipo: this.availableSubtypes[0],
                    needsInvoices: false,
                    evidenceRequired: false,
                    fixedFacturaIds: [],
                    isSelection: true
                };
                this.isModalOpen = true;
            },
            
            get availableInvoicesForModal() {
                if (this.modal.subtipo === 'Llegada a cliente') {
                    return this.guia.facturas.filter(f => f.estatus_entrega === 'En tránsito');
                }
                if (this.modal.subtipo === 'Proceso de entrega') {
                    return this.guia.facturas.filter(f => f.estatus_entrega === 'En cliente');
                }
                return this.guia.facturas;
            },
            
            getBadgeClass(status, isGuia) {
                const colors = {
                    'Planeada': 'bg-slate-100 text-slate-600 border border-slate-200',
                    'Camino a carga': 'bg-blue-50 text-blue-700 border border-blue-100',
                    'En espera de carga': 'bg-orange-50 text-orange-700 border border-orange-100',
                    'Por iniciar ruta': 'bg-teal-50 text-teal-700 border border-teal-100',
                    'En tránsito': 'bg-blue-100 text-blue-800 border border-blue-200',
                    'En Pernocta': 'bg-indigo-100 text-indigo-800 border border-indigo-200',
                    'En cliente': 'bg-purple-100 text-purple-800 border border-purple-200',
                    'Entregando': 'bg-fuchsia-100 text-fuchsia-800 border border-fuchsia-200',
                    'Entregada': 'bg-green-100 text-green-700 border border-green-200',
                    'No entregada': 'bg-red-50 text-red-700 border border-red-100',
                    'Completada': 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                    'default': 'bg-gray-100 text-gray-600'
                };
                const baseClasses = isGuia ? 'shadow-sm backdrop-blur' : '';
                return `${baseClasses} ${colors[status] || colors.default}`;
            },

            processImages(event) {
                const files = event.target.files;
                document.getElementById('file-count').innerText = `${files.length} archivo(s)`;
                const processedFiles = [];
                
                const processFile = (file) => {
                    return new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const img = new Image();
                            img.onload = () => {
                                const canvas = document.createElement('canvas');
                                const MAX_WIDTH = 800;
                                let width = img.width;
                                let height = img.height;

                                if (width > MAX_WIDTH) {
                                    height = height * (MAX_WIDTH / width);
                                    width = MAX_WIDTH;
                                }

                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(img, 0, 0, width, height);
                                
                                canvas.toBlob((blob) => {
                                    const newFile = new File([blob], file.name, {
                                        type: 'image/jpeg',
                                        lastModified: Date.now()
                                    });
                                    resolve(newFile);
                                }, 'image/jpeg', 0.7);
                            };
                            img.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    });
                };

                Promise.all(Array.from(files).map(processFile)).then(processedBlobs => {
                    const dataTransfer = new DataTransfer();
                    processedBlobs.forEach(blob => {
                        dataTransfer.items.add(blob);
                    });
                    document.getElementById('processed-evidencia').files = dataTransfer.files;
                });
            },           

            submitEventForm() {
                const form = document.getElementById('event-form');
                const evidenceInput = document.getElementById('processed-evidencia');
                const facturasCheckboxes = form.querySelectorAll('input[name="factura_ids[]"]:checked');

                if (this.modal.evidenceRequired && evidenceInput.files.length === 0) {
                    alert('La evidencia fotográfica es obligatoria para este evento.');
                    return;
                }
                if (this.modal.needsInvoices && facturasCheckboxes.length === 0) {
                    alert('Debes seleccionar al menos una factura para esta acción.');
                    return;
                }

                this.isLoading = true;
                this.locationError = '';
                this.getLocationAndSubmit('event-form', this.modal.fixedFacturaIds);
            },
            
            submitStartForm() {
                this.isLoading = true;
                this.locationError = '';
                this.getLocationAndSubmit('start-form');
            },

            getLocationAndSubmit(formId, fixedFacturaIds = []) {
                if (!navigator.geolocation) {
                    this.locationError = 'Geolocalización no está disponible en tu navegador.';
                    this.isLoading = false;
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const form = document.getElementById(formId);
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        form.querySelector(`input[name="latitud"]`).value = lat;
                        form.querySelector(`input[name="longitud"]`).value = lng;
                        
                        if (typeof google !== 'undefined' && google.maps) {
                            const geocoder = new google.maps.Geocoder();
                            const latlng = { lat: parseFloat(lat), lng: parseFloat(lng) };
                            try {
                                const { results } = await geocoder.geocode({ location: latlng });
                                let municipio = 'N/A';
                                if (results[0]) {
                                    for (const component of results[0].address_components) {
                                        if (component.types.includes("locality")) {
                                            municipio = component.long_name;
                                            break;
                                        }
                                    }
                                }
                                form.querySelector(`input[name="municipio"]`).value = municipio;
                            } catch (e) {
                                 console.error("Error de Geocodificación: ", e);
                                 form.querySelector(`input[name="municipio"]`).value = "Error al obtener municipio";
                            }
                        } else {
                            form.querySelector(`input[name="municipio"]`).value = "API de Google no disponible";
                        }
                        
                        form.querySelectorAll('input[type="hidden"][name="factura_ids[]"]').forEach(el => el.remove());
                        if (fixedFacturaIds.length > 0) {
                            fixedFacturaIds.forEach(id => {
                                const hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'factura_ids[]';
                                hiddenInput.value = id;
                                form.appendChild(hiddenInput);
                            });
                        }
                        
                        form.submit();
                    },
                    (error) => {
                        console.error("Error GPS:", error);
                        let mensaje = 'Error desconocido.';
                        switch(error.code) {
                            case 1: mensaje = 'Permiso denegado (Revisa el candado del navegador).'; break;
                            case 2: mensaje = 'Posición no disponible (Tu dispositivo no detecta señal).'; break;
                            case 3: mensaje = 'Tiempo de espera agotado (Timeout).'; break;
                        }
                        this.locationError = mensaje;
                        this.isLoading = false;
                    },
                    { enableHighAccuracy: true, timeout: 30000, maximumAge: 0 }
                );
            }
        }));
    });
</script>
@endpush