<x-app-layout>

<x-slot name="header"></x-slot>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        .animate-slide-up { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(20px); }
        @keyframes slideUp { to { opacity: 1; transform: translateY(0); } }
        
        .toggle-checkbox:checked { right: 0; border-color: #ff9c00; }
        .toggle-checkbox:checked + .toggle-label { background-color: #ff9c00; }
        
        .modern-tab-active { background-color: #2c3856; color: white; box-shadow: 0 4px 6px -1px rgba(44, 56, 86, 0.2); }
        .modern-tab-inactive { background-color: white; color: #64748b; border: 1px solid #e2e8f0; }
        .modern-tab-inactive:hover { background-color: #f8fafc; color: #2c3856; }

        .img-upload-box {
            border: 2px dashed #cbd5e1;
            transition: all 0.3s ease;
        }
        .img-upload-box:hover {
            border-color: #ff9c00;
            background-color: #fff7ed;
        }
    </style>

    <div class="min-h-screen font-sans text-[#2c3856] p-6 lg:p-10"
         x-data="{ 
            activeTab: 'preparado',
            loading: false,
            submitForm() {
                this.loading = true;
                document.getElementById('conditionsForm').submit();
            }
         }">

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-6 animate-slide-up">
            <div class="relative">
                <div class="absolute -left-4 top-0 h-full w-1 bg-[#ff9c00] rounded-full"></div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Configuración</span>
                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700 text-xs font-bold border border-blue-200">{{ $client->name }}</span>
                </div>
                <h2 class="font-montserrat font-extrabold text-3xl md:text-4xl text-[#2c3856] flex items-center gap-3">
                    <span class="bg-white p-2 rounded-xl shadow-sm border border-gray-100 text-[#ff9c00]">
                        <i class="fas fa-clipboard-check"></i>
                    </span>
                    Condiciones de Entrega
                </h2>
                <p class="text-gray-500 text-sm mt-2 font-medium ml-1">Define los requisitos obligatorios y material visual de referencia.</p>
            </div>
            
            <div class="flex flex-wrap gap-3 items-center">
                <a href="{{ route('ff.admin.show', 'clients') }}" 
                   class="group px-5 py-2.5 bg-white text-gray-600 border border-gray-200 rounded-xl font-bold shadow-sm hover:shadow-md hover:text-[#2c3856] transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> 
                    <span>Volver</span>
                </a>

                <a href="{{ route('ff.admin.clients.conditions.pdf', $client->id) }}" target="_blank"
                class="px-4 py-2.5 bg-white border border-red-200 text-red-600 rounded-xl font-bold flex items-center gap-2 hover:bg-red-50 transition-all shadow-sm">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </a>                
                
                <button @click="submitForm()" 
                   :disabled="loading"
                   class="px-6 py-2.5 bg-[#2c3856] text-white rounded-xl font-bold flex items-center gap-2 shadow-lg hover:bg-[#1a233a] hover:-translate-y-0.5 transition-all disabled:opacity-70 disabled:cursor-not-allowed">
                    <i class="fas fa-save" :class="loading ? 'fa-spin fa-spinner' : 'fa-save'"></i>
                    <span x-text="loading ? 'Guardando...' : 'Guardar Cambios'"></span>
                </button>
            </div>
        </div>

        <div class="flex gap-4 mb-6 overflow-x-auto pb-2 animate-slide-up" style="animation-delay: 0.1s;">
            <button @click="activeTab = 'preparado'" class="px-6 py-3 rounded-xl font-bold text-sm transition-all flex items-center gap-2 min-w-max" :class="activeTab === 'preparado' ? 'modern-tab-active' : 'modern-tab-inactive'">
                <i class="fas fa-box-open"></i> Preparado
            </button>
            <button @click="activeTab = 'documentacion'" class="px-6 py-3 rounded-xl font-bold text-sm transition-all flex items-center gap-2 min-w-max" :class="activeTab === 'documentacion' ? 'modern-tab-active' : 'modern-tab-inactive'">
                <i class="fas fa-file-alt"></i> Documentación
            </button>
            <button @click="activeTab = 'evidencia'" class="px-6 py-3 rounded-xl font-bold text-sm transition-all flex items-center gap-2 min-w-max" :class="activeTab === 'evidencia' ? 'modern-tab-active' : 'modern-tab-inactive'">
                <i class="fas fa-camera"></i> Evidencia
            </button>
        </div>

        <form id="conditionsForm" action="{{ route('ff.admin.clients.conditions.update', $client->id) }}" method="POST" enctype="multipart/form-data" class="animate-slide-up" style="animation-delay: 0.2s;">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-3xl shadow-[0_10px_40px_-10px_rgba(44,56,86,0.08)] border border-white/50 p-8 min-h-[500px]">
                
                <div x-show="activeTab === 'preparado'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="mb-6 flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg"><i class="fas fa-dolly"></i></div>
                        <h3 class="text-xl font-bold text-[#2c3856]">Requisitos de Preparación</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                        @php
                            $prepFields = [
                                'revision_upc' => 'Revisión de UPC vs Factura', 'distribucion_tienda' => 'Distribución por Tienda',
                                're_etiquetado' => 'Re-etiquetado', 'colocacion_sensor' => 'Colocación de Sensor',
                                'preparado_especial' => 'Preparado Especial', 'tipo_unidad_aceptada' => 'Tipo de Unidad Aceptada',
                                'equipo_seguridad' => 'Equipo de Seguridad', 'registro_patronal' => 'Registro Patronal (SUA)',
                                'entrega_otros_pedidos' => 'Entrega con Otros Pedidos', 'insumos_herramientas' => 'Insumos y Herramientas',
                                'maniobra' => 'Maniobra', 'identificaciones' => 'Identificaciones para Acceso',
                                'etiqueta_fragil' => 'Etiqueta de Frágil', 'tarima_chep' => 'Tarima CHEP',
                                'granel' => 'Granel', 'tarima_estandar' => 'Tarima Estándar',
                            ];
                        @endphp
                        @foreach($prepFields as $key => $label)
                            <x-toggle-switch name="{{ $key }}" label="{{ $label }}" :checked="$conditions->$key" />
                        @endforeach
                    </div>

                    <div class="pt-6 border-t border-gray-100">
                        <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Evidencia Fotográfica (Preparado)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @for($i=1; $i<=3; $i++)
                                <x-image-uploader name="prep_img_{{ $i }}" label="Imagen {{ $i }}" :current="$conditions->getImageUrl('prep_img_'.$i)" />
                            @endfor
                        </div>
                    </div>
                </div>

                <div x-show="activeTab === 'documentacion'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="mb-6 flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="p-2 bg-sky-50 text-sky-600 rounded-lg"><i class="fas fa-folder-open"></i></div>
                        <h3 class="text-xl font-bold text-[#2c3856]">Documentación Requerida</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                        @php
                            $docFields = [
                                'doc_factura' => 'Factura', 'doc_do' => 'DO', 'doc_carta_maniobra' => 'Carta Maniobra',
                                'doc_carta_poder' => 'Carta Poder', 'doc_orden_compra' => 'Orden de Compra',
                                'doc_carta_confianza' => 'Carta Confianza', 'doc_confirmacion_cita' => 'Confirmación de Cita',
                                'doc_carta_caja_cerrada' => 'Carta Caja Cerrada', 'doc_confirmacion_facturas' => 'Confirmación de Facturas',
                                'doc_caratula_entrega' => 'Carátula de Entrega', 'doc_pase_vehicular' => 'Pase Vehicular',
                            ];
                        @endphp
                        @foreach($docFields as $key => $label)
                            <x-toggle-switch name="{{ $key }}" label="{{ $label }}" :checked="$conditions->$key" color="bg-sky-500" />
                        @endforeach
                    </div>

                    <div class="pt-6 border-t border-gray-100">
                        <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Ejemplos de Documentos</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @for($i=1; $i<=3; $i++)
                                <x-image-uploader name="doc_img_{{ $i }}" label="Ejemplo {{ $i }}" :current="$conditions->getImageUrl('doc_img_'.$i)" />
                            @endfor
                        </div>
                    </div>
                </div>

                <div x-show="activeTab === 'evidencia'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="mb-6 flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="p-2 bg-purple-50 text-purple-600 rounded-lg"><i class="fas fa-images"></i></div>
                        <h3 class="text-xl font-bold text-[#2c3856]">Evidencia de Entrega</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                        @php
                            $evidFields = [
                                'evid_folio_recibo' => 'Folio de Recibo', 'evid_factura_sellada' => 'Factura Sellada o Firmada',
                                'evid_sello_tarima' => 'Sello Tarima CHEP', 'evid_etiqueta_recibo' => 'Etiqueta de Recibo',
                                'evid_acuse_oc' => 'Acuse de Orden de Compra', 'evid_hoja_rechazo' => 'Hoja de Rechazo',
                                'evid_anotacion_rechazo' => 'Anotación de Rechazo', 'evid_contrarrecibo' => 'Contrarrecibo de Equipo',
                                'evid_formato_reparto' => 'Formato de Reparto',
                            ];
                        @endphp
                        @foreach($evidFields as $key => $label)
                            <x-toggle-switch name="{{ $key }}" label="{{ $label }}" :checked="$conditions->$key" color="bg-purple-500" />
                        @endforeach
                    </div>

                    <div class="pt-6 border-t border-gray-100">
                        <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Ejemplos de Evidencia</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @for($i=1; $i<=3; $i++)
                                <x-image-uploader name="evid_img_{{ $i }}" label="Evidencia {{ $i }}" :current="$conditions->getImageUrl('evid_img_'.$i)" />
                            @endfor
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</x-app-layout>