<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Planificación de Rutas') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12" x-data="planningManager()">
        <div id="flash-success" class="fixed top-20 right-4 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert" style="display: none;">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <strong class="font-bold mr-1">¡Éxito!</strong>
                <span id="flash-success-message" class="block sm:inline"></span>
            </div>
            <button onclick="document.getElementById('flash-success').style.display = 'none';" class="text-gray-500 hover:text-gray-700 focus:outline-none"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <div id="flash-error" class="fixed top-20 right-4 z-50 bg-white border-l-4 border-red-600 text-red-700 px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert" style="display: none;">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <strong class="font-bold mr-1">¡Error!</strong>
                <span id="flash-error-message" class="block sm:inline"></span>
            </div>
            <button onclick="document.getElementById('flash-error').style.display = 'none';" class="text-gray-500 hover:text-gray-700 focus:outline-none"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>

        <div x-show="selectedPlannings.length > 0" 
             class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 w-11/12 max-w-5xl bg-gray-800 text-white p-3 rounded-lg shadow-2xl flex flex-col justify-between items-center"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4">
            
            <div class="w-full flex flex-col md:flex-row justify-between items-center">
               <span class="mb-2 md:mb-0" x-text="`(${selectedPlannings.length}) registros seleccionados.`"></span>
               <div class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-4">
                   <button @click="bulkEdit()" class="px-4 py-2 w-full sm:w-auto bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700"><i class="fas fa-edit mr-2"></i>Editar Selección</button>
                   <button @click="createGuide()" class="px-4 py-2 w-full sm:w-auto bg-[#ff9c00] text-white rounded-md text-sm font-semibold hover:bg-orange-600"><i class="fas fa-plus-circle mr-2"></i>Crear Guía</button>
                   <button @click="openAddToGuiaModal()" class="px-4 py-2 w-full sm:w-auto bg-orange-600 text-white rounded-md text-sm font-semibold hover:bg-orange-700"><i class="fas fa-plus mr-2"></i>Añadir a Guía</button>                        
               </div>
            </div>

            <div class="w-full border-t border-gray-600 mt-3 pt-3 text-xs">
                <div class="grid grid-cols-3 md:grid-cols-6 gap-2 text-center">
                    <div>
                        <span class="font-bold block" x-text="`$${selectionTotals.subtotal.toLocaleString('es-MX', {minimumFractionDigits: 2})}`"></span>
                        <span class="text-gray-400">Subtotal</span>
                    </div>
                    <div>
                        <span class="font-bold block" x-text="selectionTotals.cajas.toLocaleString('es-MX')"></span>
                        <span class="text-gray-400">Cajas</span>
                    </div>
                    <div>
                        <span class="font-bold block" x-text="selectionTotals.pzs.toLocaleString('es-MX')"></span>
                        <span class="text-gray-400">Botellas</span>
                    </div>
                    <div>
                        <span class="font-bold block" x-text="selectionTotals.clientes.toLocaleString('es-MX')"></span>
                        <span class="text-gray-400">Clientes</span>
                    </div>
                    <div>
                        <span class="font-bold block" x-text="selectionTotals.so_count.toLocaleString('es-MX')"></span>
                        <span class="text-gray-400">SO Sel.</span>
                    </div>
                    <div>
                        <span class="font-bold block" x-text="selectionTotals.factura_count.toLocaleString('es-MX')"></span>
                        <span class="text-gray-400">Facturas Sel.</span>
                    </div>
                </div>
            </div>
        </div>        
        
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <div class="mb-4">
                    @include('customer-service.planning._filters')
                </div>

                <!-- <div class="mb-6">
                    <div x-show="selectedPlannings.length > 0" class="bg-gray-800 text-white p-3 rounded-lg shadow-lg flex flex-col justify-between items-center transition-transform w-full" x-transition>
                        <div class="w-full flex flex-col md:flex-row justify-between items-center">
                           <span class="mb-2 md:mb-0" x-text="`(${selectedPlannings.length}) registros seleccionados.`"></span>
                           <div class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-4">
                               <button @click="bulkEdit()" class="px-4 py-2 w-full sm:w-auto bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700"><i class="fas fa-edit mr-2"></i>Editar Selección</button>
                               <button @click="createGuide()" class="px-4 py-2 w-full sm:w-auto bg-[#ff9c00] text-white rounded-md text-sm font-semibold hover:bg-orange-600"><i class="fas fa-plus-circle mr-2"></i>Crear Guía</button>
                               <button @click="openAddToGuiaModal()" class="px-4 py-2 w-full sm:w-auto bg-orange-600 text-white rounded-md text-sm font-semibold hover:bg-orange-700"><i class="fas fa-plus mr-2"></i>Añadir a Guía</button>                        
                           </div>
                        </div>

                        {{-- Sección de Totales de la Selección --}}
                        <div x-show="selectedPlannings.length > 0" class="w-full border-t border-gray-600 mt-3 pt-3 text-xs">
                            <div class="grid grid-cols-3 md:grid-cols-6 gap-2 text-center">
                                <div>
                                    <span class="font-bold block" x-text="`$${selectionTotals.subtotal.toLocaleString('es-MX', {minimumFractionDigits: 2})}`"></span>
                                    <span class="text-gray-400">Subtotal</span>
                                </div>
                                <div>
                                    <span class="font-bold block" x-text="selectionTotals.cajas.toLocaleString('es-MX')"></span>
                                    <span class="text-gray-400">Cajas</span>
                                </div>
                                <div>
                                    <span class="font-bold block" x-text="selectionTotals.pzs.toLocaleString('es-MX')"></span>
                                    <span class="text-gray-400">Botellas</span>
                                </div>
                                <div>
                                    <span class="font-bold block" x-text="selectionTotals.clientes.toLocaleString('es-MX')"></span>
                                    <span class="text-gray-400">Clientes</span>
                                </div>
                                <div>
                                    <span class="font-bold block" x-text="selectionTotals.so_count.toLocaleString('es-MX')"></span>
                                    <span class="text-gray-400">SO Sel.</span>
                                </div>
                                <div>
                                    <span class="font-bold block" x-text="selectionTotals.factura_count.toLocaleString('es-MX')"></span>
                                    <span class="text-gray-400">Facturas Sel.</span>
                                </div>
                            </div>
                        </div>
                    </div>               
                </div> -->

                <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0 mb-6">
                    <div class="flex items-center space-x-2 sm:space-x-4 flex-wrap">
                        <a href="{{ route('customer-service.planning.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-blue-700"><i class="fas fa-plus mr-2"></i>Añadir</a>                    
                        <button @click="isColumnModalOpen = true" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700"><i class="fas fa-columns mr-2"></i>Columnas</button>
                        <div x-data="{ isWidthMenuOpen: false }" class="relative">
                            <button @click="isWidthMenuOpen = !isWidthMenuOpen" @click.away="isWidthMenuOpen = false" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700 flex items-center">
                                <i class="fas fa-arrows-alt-h mr-2"></i>Ancho
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="isWidthMenuOpen" x-transition class="absolute z-10 mt-2 w-48 bg-white rounded-md shadow-lg border">
                                <a @click="setColumnWidths('uniform'); isWidthMenuOpen = false" href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ancho Uniforme</a>
                                <a @click="setColumnWidths('auto'); isWidthMenuOpen = false" href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ajuste Automático</a>
                            </div>
                        </div>                        
                        <button @click="isAdvancedFilterModalOpen = true" class="px-4 py-2 bg-teal-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-teal-700"><i class="fas fa-filter mr-2"></i>Filtros</button>
                        <button @click="exportToCsv()" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-green-700"><i class="fas fa-file-csv mr-2"></i>Exportar</button>
                    </div>
                </div>
                
                <div id="planning-table-container" x-show="!isLoading">
                    @include('customer-service.planning._table')
                </div>
                
                <div x-show="!isLoading && pagination.total > 0" class="mt-4 flex flex-col md:flex-row justify-between items-center text-sm text-gray-700">
                    <p class="mb-2 md:mb-0">
                        Mostrando de <span x-text="pagination.from || 0"></span> a <span x-text="pagination.to || 0"></span> de <span x-text="pagination.total || 0"></span> resultados
                    </p>
                    <div class="flex items-center text-sm text-gray-700">
                        <span>Mostrar</span>
                            <select x-model="rowsPerPage" @change="changeRowsPerPage()" class="mx-2 rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="40">40</option>
                                <option value="50">50</option>
                                <option value="60">60</option>
                                <option value="70">70</option>
                                <option value="80">80</option>
                                <option value="90">90</option>
                                <option value="100">100</option>
                            </select>
                        <span>registros.</span>
                    </div>                    
                    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center">
                        <template x-for="(link, index) in pagination.links" :key="index">
                            <button @click="changePage(link)" 
                                    :disabled="!link.url"
                                    class="px-3 py-1 mx-1 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                    :class="{
                                        'bg-indigo-600 text-white': link.active,
                                        'bg-white text-gray-600 hover:bg-gray-100': !link.active && link.url,
                                        'bg-gray-100 text-gray-400 cursor-not-allowed': !link.url
                                    }"
                                    >
                                <span x-html="link.label"></span>
                            </button>
                        </template>
                    </nav>
                </div>
                
                <div x-show="isLoading" class="text-center py-10">
                    <i class="fas fa-spinner fa-spin text-4xl text-gray-500"></i>
                    <p class="mt-2 text-gray-600">Cargando datos...</p>
                </div>
            </div>
        </div>
        
        @include('customer-service.planning._column-selector-modal')
        @include('customer-service.planning._scales-modal')
        @include('customer-service.planning._add-to-guia-modal')
        @include('customer-service.planning._advanced-filters-modal')
        
        <div x-show="isEditGuiaModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="closeAllModals()">
            <div @click.outside="closeAllModals()" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
                <h3 class="text-xl font-bold text-[#2c3856] mb-4">Editar Número de Guía</h3>
                <form @submit.prevent="submitGuiaEdit()">
                    <div>
                        <label for="guia_number_input" class="block text-sm font-medium text-gray-700">Nuevo número de guía</label>
                        <input type="text" id="guia_number_input" x-model="guiaToEdit.number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div class="mt-6 flex justify-end space-x-4">
                        <button type="button" @click="closeAllModals()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="isGuiaDetailModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div @click.outside="closeAllModals()" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl">
                <template x-if="guiaInModalLoading">
                    <div class="text-center p-8"><i class="fas fa-spinner fa-spin text-4xl text-gray-500"></i></div>
                </template>
                <template x-if="!guiaInModalLoading && guiaInModal">
                    <div>
                        <div class="flex justify-between items-start border-b pb-3 mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-[#2c3856]">Detalle de la Guía</h3>
                                <div class="flex items-center space-x-2">
                                    <p class="font-semibold text-lg text-gray-800" x-text="guiaInModal.guia.guia"></p>
                                    <button @click="openEditGuiaModal(guiaInModal.guia.id, guiaInModal.guia.guia, true)" type="button" class="text-indigo-500 hover:text-indigo-700" title="Editar Número de Guía">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" @click="closeAllModals()" class="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                            <div><strong>Operador:</strong> <span x-text="guiaInModal.guia.operador"></span></div>
                            <div><strong>Placas:</strong> <span x-text="guiaInModal.guia.placas"></span></div>
                            <div><strong>Teléfono:</strong> <span x-text="guiaInModal.guia.telefono || 'Pendiente'"></span></div>
                            <div><strong>Origen:</strong> <span x-text="guiaInModal.guia.origen"></span></div>
                            <div><strong>Custodia:</strong> <span x-text="guiaInModal.guia.custodia"></span></div>
                        </div>
                        <h4 class="font-semibold text-gray-700">Órdenes Incluidas:</h4>
                        <ul class="list-disc list-inside text-sm mt-2 mb-4">
                            <template x-for="planning in guiaInModal.guia.plannings" :key="planning.id">
                                <li x-text="`SO: ${planning.so_number} - ${planning.razon_social}`"></li>
                            </template>
                        </ul>

                        <div class="mt-4 border-t pt-4">
                            <h4 class="font-semibold text-gray-700 mb-2">Observaciones de la Carga</h4>
                            <div class="bg-gray-50 p-3 rounded-lg border max-h-40 overflow-y-auto">
                                <template x-if="guiaInModal.observaciones_con_so && guiaInModal.observaciones_con_so.length > 0">
                                    <ul class="list-none space-y-2 text-sm text-gray-700">
                                        <template x-for="item in guiaInModal.observaciones_con_so" :key="item.so + item.fuente">
                                            <li>
                                                <strong class="text-blue-600" x-text="`${item.fuente} (${item.so}):`"></strong>
                                                <span x-text="item.observacion"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                                <template x-if="!guiaInModal.observaciones_con_so || guiaInModal.observaciones_con_so.length === 0">
                                    <p class="text-sm text-gray-500">No hay observaciones registradas.</p>
                                </template>
                            </div>
                        </div>                        
                        
                        <div class="mt-4 border-t pt-4">
                            <h4 class="font-semibold text-gray-700 mb-2">Resumen de la Carga:</h4>
                            <div class="grid grid-cols-3 md:grid-cols-5 gap-2 text-sm text-center">
                                <div class="bg-gray-100 p-2 rounded">
                                    <span class="font-bold block" x-text="guiaModalTotals.cajas.toLocaleString('es-MX')"></span>
                                    <span class="text-gray-500">Cajas</span>
                                </div>
                                <div class="bg-gray-100 p-2 rounded">
                                    <span class="font-bold block" x-text="guiaModalTotals.pzs.toLocaleString('es-MX')"></span>
                                    <span class="text-gray-500">Botellas</span>
                                </div>
                                <div class="bg-gray-100 p-2 rounded">
                                    <span class="font-bold block" x-text="guiaModalTotals.clientes.toLocaleString('es-MX')"></span>
                                    <span class="text-gray-500">Clientes</span>
                                </div>
                                <div class="bg-gray-100 p-2 rounded">
                                    <span class="font-bold block" x-text="guiaModalTotals.maniobras.toLocaleString('es-MX')"></span>
                                    <span class="text-gray-500">Maniobristas</span>
                                </div>                                
                                <div class="bg-gray-100 p-2 rounded">
                                    <span class="font-bold block" x-text="guiaModalTotals.so_count.toLocaleString('es-MX')"></span>
                                    <span class="text-gray-500">SOs</span>
                                </div>
                                <div class="bg-gray-100 p-2 rounded">
                                    <span class="font-bold block" x-text="guiaModalTotals.factura_count.toLocaleString('es-MX')"></span>
                                    <span class="text-gray-500">Facturas</span>
                                </div>                               
                            </div>
                        </div>
                        <div class="mt-4 space-y-3">
                            <h5 class="font-semibold text-gray-600 text-sm">Capacidad de Unidad</h5>
                            <div class="bg-gray-50 p-3 rounded-lg border grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <div>
                                        <p class="text-xs text-gray-500">Capacidad Actual:</p>
                                        <p class="font-bold text-gray-800" x-text="actualCapacity"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-500">Unidad Sugerida (por carga):</p>
                                        <p class="font-bold text-blue-700" x-text="suggestedCapacity"></p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <select x-model="editableCapacity" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <template x-for="option in capacityOptions" :key="option">
                                            <option :value="option" x-text="option"></option>
                                        </template>
                                    </select>
                                    <button @click="saveCapacity()" class="px-3 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700 whitespace-nowrap">Guardar</button>
                                </div>
                            </div>
                        </div>                        
                        <div class="flex justify-between items-center font-bold text-lg text-gray-800 border-t pt-3 mt-4">
                            <button @click="openEmailModal()" type="button" class="px-4 py-2 bg-teal-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-teal-700">
                                <i class="fas fa-envelope mr-2"></i>Correo
                            </button>
                            <span>Valor Total de la Carga: <span class="text-green-600" x-text="`$${guiaInModal.total_subtotal.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`"></span></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="isEmailModalOpen" @keydown.escape.window="isEmailModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" style="display: none;">
            <div @click.outside="isEmailModalOpen = false" class="bg-white rounded-lg shadow-xl w-full max-w-4xl h-[90vh] flex flex-col">
                <div class="flex justify-between items-center border-b p-4">
                    <h3 class="text-xl font-bold text-[#2c3856]">Enviar Correo de Ruta</h3>
                    <button @click="isEmailModalOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>

                <div class="p-6 flex-grow overflow-y-auto">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Destinatarios:</label>
                        <div class="mt-1 flex flex-wrap gap-2 p-2 border rounded-md min-h-[40px]">
                            <template x-for="(recipient, index) in emailData.recipients" :key="index">
                                <span class="flex items-center bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                                    <span x-text="recipient"></span>
                                    <button @click="removeRecipient(index)" class="ml-2 text-blue-600 hover:text-blue-800">&times;</button>
                                </span>
                            </template>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <select x-model="newRecipient" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Seleccionar de la lista...</option>
                                <template x-for="user in allRecipients" :key="user.email">
                                    <option :value="user.email" x-text="`${user.name} (${user.email})`"></option>
                                </template>
                            </select>
                            <input type="email" placeholder="O añadir nuevo correo..." x-model="newRecipientManual" @keydown.enter.prevent="addRecipient(newRecipientManual); newRecipientManual=''" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <button @click="addRecipient(newRecipient || newRecipientManual)" type="button" class="px-4 py-2 bg-gray-200 rounded-md text-sm">Añadir</button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Asunto:</label>
                        <input type="text" x-model="emailData.subject" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-50">
                    </div>

                    <div class="space-y-4">
<div>
    <label for="emailEditor" class="block text-sm font-medium text-gray-700">Cuerpo del Mensaje:</label>
    <div
        id="emailEditor"
        x-ref="emailEditor"
        contenteditable="true"
        x-html="emailData.body"
        class="mt-1 p-4 border rounded-md min-h-[250px] text-sm font-sans focus:ring-2 focus:ring-blue-500 focus:border-blue-500 overflow-y-auto"
    ></div>
</div>

                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Firma:</label>
                        <input type="text" x-model="signature" @input.debounce.500ms="saveSignature()" placeholder="Pega aquí la URL pública de tu imagen de firma..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                </div>

                <div class="flex justify-end gap-4 border-t p-4 bg-gray-50">
                    <button @click="isEmailModalOpen = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">Cancelar</button>
                    <button @click="sendEmail()" class="px-4 py-2 bg-green-600 text-white rounded-md">Enviar Correo</button>
                </div>
            </div>
        </div>

    </div>

<style>
@keyframes flash { 0%, 100% { background-color: white; } 50% { background-color: #fef3c7; } }
.flashing-row { animation: flash 1.5s infinite; }
.resizer { position: absolute; top: 0; right: 0; width: 5px; height: 100%; cursor: col-resize; user-select: none; }
th { position: relative; }
#planning-table-container { overflow: visible !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if (session('success'))
        sessionStorage.setItem('planning_flash_success', '{{ session('success') }}');
    @endif
    @if (session('error'))
        sessionStorage.setItem('planning_flash_error', '{{ session('error') }}');
    @endif
});    
function planningManager() {
    return {
        plannings: [],
        pagination: { links: [] },
        isLoading: true,
        isGuiaDetailModalOpen: false,
        guiaInModal: null,
        guiaInModalLoading: false,
        guiaModalTotals: {
            cajas: 0,
            pzs: 0,
            clientes: 0,
            so_count: 0,
            factura_count: 0,
            maniobras: 0
        },
        actualCapacity: 'N/A',
        suggestedCapacity: 'N/A',
        editableCapacity: '',
        capacityOptions: ['1 Ton', '1.5 Ton', '3.5 Ton', '4.5 Ton', 'Torthon', 'Rabón', 'Mudancero', 'Trailer 48"', 'Trailer 53"', 'Automovil', 'Motocicleta', 'Paqueteria', 'Contenedor 20"', 'Contenedor 40"', 'Contenedor 48"', 'Contenedor 53"'],        
        isEditGuiaModalOpen: false,
        guiaToEdit: { id: null, number: '' },        
        filters: { 
            search: new URLSearchParams(window.location.search).get('search') || '', 
            status: new URLSearchParams(window.location.search).get('status') || '', 
            origen: new URLSearchParams(window.location.search).get('origen') || '', 
            destino: new URLSearchParams(window.location.search).get('destino') || '', 
            date_created_from: new URLSearchParams(window.location.search).get('date_created_from') || '', 
            date_created_to: new URLSearchParams(window.location.search).get('date_created_to') || '', 
            page: new URLSearchParams(window.location.search).get('page') || 1,
            guia_adv: new URLSearchParams(window.location.search).get('guia_adv') || '',
            so_number_adv: new URLSearchParams(window.location.search).get('so_number_adv') || '',
            factura_adv: new URLSearchParams(window.location.search).get('factura_adv') || '',
            razon_social_adv: new URLSearchParams(window.location.search).get('razon_social_adv') || '',
            direccion_adv: new URLSearchParams(window.location.search).get('direccion_adv') || '',
            fecha_entrega_adv: new URLSearchParams(window.location.search).get('fecha_entrega_adv') || '',
            fecha_carga_adv: new URLSearchParams(window.location.search).get('fecha_carga_adv') || '',
            origen_adv: new URLSearchParams(window.location.search).get('origen_adv') || '',
            destino_adv: new URLSearchParams(window.location.search).get('destino_adv') || '',
            estado_adv: new URLSearchParams(window.location.search).get('estado_adv') || '',
            transporte_adv: new URLSearchParams(window.location.search).get('transporte_adv') || '',
            operador_adv: new URLSearchParams(window.location.search).get('operador_adv') || '',
            placas_adv: new URLSearchParams(window.location.search).get('placas_adv') || '',
            tipo_ruta_adv: new URLSearchParams(window.location.search).get('tipo_ruta_adv') || '',
            servicio_adv: new URLSearchParams(window.location.search).get('servicio_adv') || '',
            canal_adv: new URLSearchParams(window.location.search).get('canal_adv') || '',
            custodia_adv: new URLSearchParams(window.location.search).get('custodia_adv') || '',
            urgente_adv: new URLSearchParams(window.location.search).get('urgente_adv') || '',
            devolucion_adv: new URLSearchParams(window.location.search).get('devolucion_adv') || ''
        },
        selectedPlannings: [],
        selectionTotals: {
            subtotal: 0,
            cajas: 0,
            pzs: 0,
            clientes: 0,
            so_count: 0,
            factura_count: 0
        },
        rowsPerPage: localStorage.getItem('planning_rowsPerPage') || 20,
        sorts: [],
        isColumnModalOpen: false,
        allColumns: @json($allColumns),
        visibleColumns: {},
        columnOrder: [],
        columnWidths: {},
        isScalesModalOpen: false,
        isAddToGuiaModalOpen: false,
        isAdvancedFilterModalOpen: false,
        selectedPlanning: {},
        scalesCount: 1,
        scales: [],
        warehouses: @json($warehouses),
        guiaSearch: '',
        guiaSearchResults: [],
        isEmailModalOpen: false,
        allRecipients: [],
        newRecipient: '',
        newRecipientManual: '',
        signature: '',
        emailData: {
            recipients: [],
            subject: '',
            body: ''
        },
        editableBody: '', 

        init() {
            try {
                const urlSorts = new URLSearchParams(window.location.search).get('sorts');
                this.sorts = urlSorts ? JSON.parse(urlSorts) : [];
            } catch (e) {
                this.sorts = [];
            }            
            this.loadColumnSettings();
            this.fetchPlannings();

            this.$watch('selectedPlannings', (selectedIds) => {
                const selectedRecords = this.plannings.filter(p => selectedIds.includes(String(p.id)));
                
                this.selectionTotals.subtotal = selectedRecords.reduce((sum, rec) => sum + (parseFloat(rec.subtotal) || 0), 0);
                this.selectionTotals.cajas = selectedRecords.reduce((sum, rec) => sum + (parseInt(rec.cajas) || 0), 0);
                this.selectionTotals.pzs = selectedRecords.reduce((sum, rec) => sum + (parseInt(rec.pzs) || 0), 0);
                
                const uniqueClientes = new Set(selectedRecords.map(rec => rec.razon_social));
                this.selectionTotals.clientes = uniqueClientes.size;

                this.selectionTotals.so_count = selectedRecords.length;
                this.selectionTotals.factura_count = selectedRecords.length;
            });

            this.$watch('filters', Alpine.debounce(() => { 
                this.filters.page = 1; 
                this.fetchPlannings(); 
            }, 300));

            this.$watch('visibleColumns', (val) => {
                const visibleKeys = Object.keys(val).filter(key => val[key]);
                const newColumns = visibleKeys.filter(key => !this.columnOrder.includes(key));
                if (newColumns.length > 0) {
                    this.columnOrder = [...this.columnOrder, ...newColumns];
                }
                this.saveColumnSettings();
            }, { deep: true });

            this.$nextTick(() => { this.initSortable(); this.initResizers(); });
            this.$el.addEventListener('toggle-all-plannings', (e) => { this.toggleAllPlannings(e.detail); });

            const flashSuccess = sessionStorage.getItem('planning_flash_success');
            if (flashSuccess) {
                this.showFlashMessage(flashSuccess, 'success');
                sessionStorage.removeItem('planning_flash_success');
            }
            const flashError = sessionStorage.getItem('planning_flash_error');
            if (flashError) {
                this.showFlashMessage(flashError, 'error');
                sessionStorage.removeItem('planning_flash_error');
            }

            this.loadEmailDefaults();
            fetch('{{ route("api.email-recipients") }}')
                .then(res => res.json())
                .then(data => this.allRecipients = data);            

        },

        showFlashMessage(message, type = 'success') {
            const id = type === 'success' ? 'flash-success' : 'flash-error';
            const messageId = type === 'success' ? 'flash-success-message' : 'flash-error-message';
            const el = document.getElementById(id);
            const msgEl = document.getElementById(messageId);
            if (el && msgEl) {
                msgEl.innerText = message;
                el.style.display = 'flex';
                setTimeout(() => {
                    el.style.display = 'none';
                }, 5000);
            }
        },

        openGuiaDetailModal(planning) {
            if (!planning.guia) return;
            this.isGuiaDetailModalOpen = true;
            this.guiaInModalLoading = true;
            fetch(`/rutas/asignaciones/${planning.guia.id}/details`)
                .then(res => {
                    if (!res.ok) throw new Error('El servidor respondió con un error.');
                    return res.json();
                })
                .then(data => {
                    this.guiaInModal = data;
                    this.guiaInModalLoading = false;
                    
                    if (data && data.guia && data.guia.plannings) {
                        const records = data.guia.plannings;
                        this.guiaModalTotals.cajas = records.reduce((sum, rec) => sum + (parseInt(rec.cajas) || 0), 0);
                        this.guiaModalTotals.pzs = records.reduce((sum, rec) => sum + (parseInt(rec.pzs) || 0), 0);
                        const uniqueClientes = new Set(records.map(rec => rec.razon_social));
                        this.guiaModalTotals.clientes = uniqueClientes.size;
                        this.guiaModalTotals.so_count = records.length;
                        this.guiaModalTotals.factura_count = records.length;
                        this.guiaModalTotals.maniobras = data.total_maniobras || 0;
                        this.actualCapacity = data.capacidad_actual || 'No definida';

                        // Lógica de sugerencia
                        const cajas = this.guiaModalTotals.cajas;
                        if (cajas <= 30) this.suggestedCapacity = '1 Ton';
                        else if (cajas <= 60) this.suggestedCapacity = '1.5 Ton';
                        else if (cajas <= 90) this.suggestedCapacity = '3.5 Ton';
                        else if (cajas <= 120) this.suggestedCapacity = '4.5 Ton';
                        else if (cajas <= 240) this.suggestedCapacity = 'Torthon';
                        else if (cajas <= 360) this.suggestedCapacity = 'Rabón';
                        else if (cajas <= 480) this.suggestedCapacity = 'Trailer 48"';
                        else this.suggestedCapacity = 'Trailer 53"';
                        
                        this.editableCapacity = data.capacidad_actual || this.suggestedCapacity;
                    }
                })
                .catch(() => {
                    this.showFlashMessage('Error al cargar los detalles de la guía.', 'error');
                    this.isGuiaDetailModalOpen = false;
                    this.guiaInModalLoading = false;
                });
        },

        openEditGuiaModal(id, number, fromDetailModal = false) {
            if (id && number) {
                if (fromDetailModal) { this.isGuiaDetailModalOpen = false; }
                this.guiaToEdit.id = id;
                this.guiaToEdit.number = number;
                this.isEditGuiaModalOpen = true;
            }
        },

        saveCapacity() {
            if (!this.guiaInModal || !this.guiaInModal.guia.id) return;

            fetch('{{ route("customer-service.planning.bulk-update-capacity") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    guia_id: this.guiaInModal.guia.id,
                    capacidad: this.editableCapacity
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.showFlashMessage(data.message, 'success');
                    this.fetchPlannings(); 
                    this.closeAllModals();
                } else {
                    this.showFlashMessage(data.message || 'Ocurrió un error.', 'error');
                }
            })
            .catch(error => {
                console.error("Error al guardar capacidad:", error);
                this.showFlashMessage('Error de conexión al guardar la capacidad.', 'error');
            });
        },        

        closeAllModals() {
            this.isScalesModalOpen = false;
            this.isAddToGuiaModalOpen = false;
            this.isAdvancedFilterModalOpen = false;
            this.isEditGuiaModalOpen = false;
            this.isGuiaDetailModalOpen = false;
        },      

        submitGuiaEdit() {
            fetch(`/rutas/asignaciones/${this.guiaToEdit.id}/update-number`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ guia_number: this.guiaToEdit.number })
            })
            .then(res => {
                if (!res.ok) throw new Error('Error del servidor al actualizar.');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    this.showFlashMessage(data.message, 'success');
                    this.plannings.forEach(p => {
                        if (p.guia && p.guia.id === this.guiaToEdit.id) {
                            p.guia.guia = data.new_guia_number;
                        }
                    });
                    this.closeAllModals();
                } else {
                    this.showFlashMessage(data.message || 'Ocurrió un error.', 'error');
                }
            })
            .catch(error => {
                console.error("Error al guardar la guía:", error);
                this.showFlashMessage('Error de conexión al guardar la guía.', 'error');
            });
        },     

        fetchPlannings() {
            this.isLoading = true; this.selectedPlannings = [];
            const cleanFilters = Object.fromEntries(Object.entries(this.filters).filter(([_, v]) => v !== null && v !== ''));
            const params = new URLSearchParams(cleanFilters);
            if (this.sorts.length > 0) {
                params.append('sorts', JSON.stringify(this.sorts));
            }
            params.append('per_page', this.rowsPerPage);
            window.history.pushState({}, '', `${window.location.pathname}?${params}`);

            fetch(`{{ route('customer-service.planning.filter') }}?${params}`)
                .then(response => {
                    if (!response.ok) throw new Error(`Error del servidor: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    this.plannings = data.data;
                    this.pagination = data;
                    this.isLoading = false;
                })
                .catch(error => {
                    console.error('Ocurrió un error al filtrar los datos:', error);
                    this.showFlashMessage('Hubo un error al aplicar los filtros.', 'error');
                    this.plannings = [];
                    this.pagination = { links: [] };
                    this.isLoading = false;
                });
        },

        clearBasicFilters() {
            this.filters.search = '';
            this.filters.status = '';
            this.filters.origen = '';
            this.filters.destino = '';
            this.filters.date_created_from = '';
            this.filters.date_created_to = '';
        },       
        
        changePage(link) {
            if (!link.url) return;
            try {
                const url = new URL(link.url);
                this.filters.page = url.searchParams.get('page');
                this.fetchPlannings();
            } catch (e) {
                console.error("URL de paginación inválida:", link.url);
            }
        },

        getFormattedCell(planning, columnKey) {
            if (columnKey === 'guia') {
                if (planning.guia && planning.guia.id) {
                    return `<button type="button" @click='openEditGuiaModal(${planning.guia.id}, "${planning.guia.guia}")' class="text-indigo-600 hover:underline font-semibold" title="Editar Número de Guía">${planning.guia.guia}</button>`;
                }
                return 'Sin Asignar';
            }
            const value = planning[columnKey];
            if (value === null || value === undefined || value === '') return 'N/A';
            if (['fecha_carga', 'fecha_entrega', 'created_at'].includes(columnKey)) { try { const d = new Date(value); return isNaN(d.getTime()) ? value : d.toLocaleDateString('es-MX', {day:'2-digit',month:'2-digit',year:'numeric'}); } catch(e){ return value; } }
            if (columnKey === 'hora_carga' && typeof value === 'string') return value.substring(0, 5);
            if (columnKey === 'subtotal') return `$${parseFloat(value).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            return value;
        },

        changeRowsPerPage() {
            localStorage.setItem('planning_rowsPerPage', this.rowsPerPage);
            this.filters.page = 1;
            this.fetchPlannings();
        },

        sortBy(column, event) {
            const existingSortIndex = this.sorts.findIndex(s => s.column === column);
            if (event.ctrlKey || event.metaKey) {
                if (existingSortIndex > -1) {
                    this.sorts.splice(existingSortIndex, 1);
                    this.fetchPlannings();
                }
                return;
            }            
            if (event.shiftKey) {
                if (existingSortIndex > -1) {
                    this.sorts[existingSortIndex].dir = this.sorts[existingSortIndex].dir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sorts.push({ column: column, dir: 'asc' });
                }
            } else {
                if (existingSortIndex > -1) {
                    const currentDir = this.sorts[existingSortIndex].dir;
                    this.sorts = [{ column: column, dir: currentDir === 'asc' ? 'desc' : 'asc' }];
                } else {
                    this.sorts = [{ column: column, dir: 'asc' }];
                }
            }
            this.fetchPlannings();
        },

        getSortState(column) {
            const index = this.sorts.findIndex(s => s.column === column);
            if (index > -1) {
                return { dir: this.sorts[index].dir, priority: index + 1 };
            }
            return null;
        },
        
        exportToCsv() {
            const cleanFilters = Object.fromEntries(Object.entries(this.filters).filter(([_, v]) => v !== null && v !== '' && v !== 'page'));
            const params = new URLSearchParams(cleanFilters);
            if (this.sorts.length > 0) {
                params.append('sorts', JSON.stringify(this.sorts));
            }
            window.location.href = `{{ route('customer-service.planning.export-csv') }}?${params.toString()}`;
        },
     
        loadColumnSettings() {
            const defaultOrder = Object.keys(this.allColumns);
            const defaultVisible = defaultOrder.reduce((acc, key) => ({...acc, [key]: true}), {});
            this.columnOrder = JSON.parse(localStorage.getItem('planning_columnOrder')) || defaultOrder;
            this.visibleColumns = {...defaultVisible, ...JSON.parse(localStorage.getItem('planning_visibleColumns'))};
            this.columnWidths = JSON.parse(localStorage.getItem('planning_columnWidths')) || {};
        },

        saveColumnSettings() {
            localStorage.setItem('planning_visibleColumns', JSON.stringify(this.visibleColumns));
            localStorage.setItem('planning_columnOrder', JSON.stringify(this.columnOrder));
            localStorage.setItem('planning_columnWidths', JSON.stringify(this.columnWidths));
        },

        setColumnWidths(preset) {
            if (preset === 'uniform') {
                const newWidths = {};
                this.columnOrder.forEach(columnKey => {
                    if (this.visibleColumns[columnKey]) {
                        newWidths[columnKey] = '160px';
                    }
                });
                this.columnWidths = newWidths;
            } 
            else if (preset === 'auto') {
                this.columnWidths = {};
            }
            this.saveColumnSettings();
        },        

        initSortable() {
            const tableHead = this.$el.querySelector('thead tr');
            Sortable.create(tableHead, {
                animation: 150,
                handle: '.drag-handle',
                filter: '.no-drag',
                onEnd: (evt) => {
                    const movedItem = this.columnOrder.splice(evt.oldIndex-1, 1)[0];
                    this.columnOrder.splice(evt.newIndex-1, 0, movedItem);
                    this.saveColumnSettings();
                }
            });
        },

        initResizers() {
            this.$el.querySelectorAll('th .resizer').forEach(resizer => {
                let header = resizer.parentElement; let startX, startWidth;
                const onMouseMove = (e) => {
                    const newWidth = startWidth + (e.clientX - startX);
                    if (newWidth > 20) {
                        header.style.width = `${newWidth}px`;
                        this.columnWidths[header.dataset.column] = `${newWidth}px`;
                    }
                };
                const onMouseUp = () => { document.removeEventListener('mousemove', onMouseMove); document.removeEventListener('mouseup', onMouseUp); this.saveColumnSettings(); };
                resizer.addEventListener('mousedown', (e) => { e.preventDefault(); startX = e.clientX; startWidth = header.offsetWidth; document.addEventListener('mousemove', onMouseMove); document.addEventListener('mouseup', onMouseUp); });
            });
        },
        
        applyAdvancedFilters() {
            this.isAdvancedFilterModalOpen = false;
            this.filters.page = 1; 
            this.fetchPlannings();
        },

        resetAdvancedFilters() {
             Object.keys(this.filters).forEach(key => {
                if (key.endsWith('_adv')) {
                    this.filters[key] = '';
                }
            });
            this.applyAdvancedFilters();
        },

        openScalesModal(planning) {
            this.selectedPlanning = planning;
            this.isScalesModalOpen = true;
        },

        closeScalesModal() {
            this.isScalesModalOpen = false;
            this.scales = [];
            this.scalesCount = 1;
            this.selectedPlanning = {};
        },

        generateScales() {
            if (this.scalesCount > 0) {
                this.scales = Array.from({ length: this.scalesCount }, () => ({ origen: '', destino: '' }));
            }
        },

        saveScales() {
            const allScalesValid = this.scales.every(s => s.origen && s.destino);
            if (!allScalesValid) {
                this.showFlashMessage('Por favor, complete todos los campos de origen y destino.', 'error');
                return;
            }
            fetch(`/customer-service/planning/${this.selectedPlanning.id}/add-scales`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ scales: this.scales })
            })
            .then(res => {
                if (!res.ok) throw new Error('Error del servidor al guardar escalas.');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    this.showFlashMessage(data.message, 'success');
                    this.closeScalesModal();
                    this.fetchPlannings();
                } else {
                    this.showFlashMessage(data.message || 'Ocurrió un error.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showFlashMessage('Error de conexión al guardar escalas.', 'error');
            });
        },

        openAddToGuiaModal() {
            if (this.selectedPlannings.length === 0) {
                this.showFlashMessage('Por favor, selecciona al menos un registro.', 'error');
                return;
            }
            this.isAddToGuiaModalOpen = true;
        },

        searchGuias() {
            if (this.guiaSearch.length < 2) {
                this.guiaSearchResults = [];
                return;
            }
            fetch(`/rutas/asignaciones/search?term=${this.guiaSearch}`)
                .then(res => {
                    if (!res.ok) throw new Error('Error del servidor al buscar guías.');
                    return res.json();
                })
                .then(data => {
                    this.guiaSearchResults = data;
                })
                .catch(error => {
                    console.error("Error en búsqueda de guías:", error);
                    this.guiaSearchResults = [];
                });
        },     

        createGuide() {
            if (this.selectedPlannings.length === 0) { this.showFlashMessage('Por favor, selecciona al menos un registro.', 'error'); return; }
            const selectedIdsAsStrings = this.selectedPlannings.map(String);
            const selectedRecords = this.plannings.filter(p => selectedIdsAsStrings.includes(String(p.id)));
            const alreadyAssigned = selectedRecords.some(p => p.guia !== null);
            if (alreadyAssigned) { this.showFlashMessage('Una o más órdenes ya tienen guía. Desasígnalas o deselecciónalas para continuar.', 'error'); return; }
            
            const totalValue = selectedRecords.reduce((sum, record) => sum + (parseFloat(record.subtotal) || 0), 0);
            const params = new URLSearchParams();
            this.selectedPlannings.forEach(id => params.append('planning_ids[]', id));
            
            const firstSelected = selectedRecords[0];
            if (totalValue > 5000000) {
                this.showFlashMessage(`¡Atención! El valor total ($${totalValue.toLocaleString('es-MX')}) supera los $5,000,000. Se requiere custodia.`, 'success');
                params.append('custodia', 'Planus');
            } else {
                const custodiaValue = firstSelected?.custodia || 'Pendiente';
                params.append('custodia', custodiaValue);
            }
            if (firstSelected) {
                if(firstSelected.hora_carga) params.append('hora_planeada', firstSelected.hora_carga.substring(0, 5));
                if(firstSelected.origen) params.append('origen', firstSelected.origen);
                if(firstSelected.fecha_carga) params.append('fecha_asignacion', firstSelected.fecha_carga.split('T')[0]);
                if(firstSelected.telefono) params.append('telefono', firstSelected.telefono);
            }
            window.location.href = `{{ route('rutas.asignaciones.create') }}?${params.toString()}`;
        },
        
        toggleAllPlannings(checked) {
            if (checked) {
                this.selectedPlannings = this.plannings.map(p => p.id);
            } else {
                this.selectedPlannings = [];
            }
        },
        
        bulkEdit() {
            if (this.selectedPlannings.length === 0) {
                this.showFlashMessage('Por favor, selecciona al menos un registro para editar.', 'error');
                return;
            }
            const params = new URLSearchParams();
            this.selectedPlannings.forEach(id => params.append('ids[]', id));
            window.location.href = `{{ route('customer-service.planning.bulk-edit') }}?${params.toString()}`;
        },
        
        markAsDirect(planningId) {
            if (!confirm('¿Estás seguro de que esta ruta no necesita escalas y quieres aprobarla?')) return;

            fetch(`/customer-service/planning/${planningId}/mark-as-direct`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Error del servidor al marcar como directa.');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    this.showFlashMessage(data.message, 'success');
                    this.fetchPlannings();
                } else {
                    this.showFlashMessage(data.message || 'Ocurrió un error.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showFlashMessage('Error de conexión al marcar como directa.', 'error');
            });
        },
        
        loadEmailDefaults() {
            this.emailData.recipients = JSON.parse(localStorage.getItem('planning_email_recipients')) || [];
            this.signature = localStorage.getItem('planning_email_signature') || '';
        },
        
        addRecipient(email) {
            if (email && !this.emailData.recipients.includes(email)) {
                this.emailData.recipients.push(email);
                this.saveRecipients();
            }
            this.newRecipient = '';
            this.newRecipientManual = '';
        },

        removeRecipient(index) {
            this.emailData.recipients.splice(index, 1);
            this.saveRecipients();
        },

        saveRecipients() {
            localStorage.setItem('planning_email_recipients', JSON.stringify(this.emailData.recipients));
        },
        
        saveSignature() {
            localStorage.setItem('planning_email_signature', this.signature);
        },

        openEmailModal() {
            if (!this.guiaInModal) return;

            const guia = this.guiaInModal.guia;
            const plannings = guia.plannings || [];

            const clientes = [...new Set(plannings.map(p => p.razon_social))];
            const tipoCliente = clientes.length === 1 ? clientes[0] : 'CONSOLIDADA';
            const origen = guia.origen || 'N/A';
            const fecha = new Date(guia.fecha_asignacion.split('-').join('/'));
            const diaSemana = fecha.toLocaleDateString('es-MX', { weekday: 'long' }).toUpperCase();
            const fechaFormato = fecha.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
            this.emailData.subject = `RUTA ${tipoCliente} // ALMACEN ${origen} // ${diaSemana} ${fechaFormato}`;

            let htmlBody = `<p>Hola, buen día a todos.</p>`;
            htmlBody += `<p>Por favor su apoyo para programar y embarcar los siguientes pedidos y adicionar los insumos suficientes (playo, tarima, cinta adhesiva) con base en las necesidades de entrega y favor de colocar marchamo.</p>`;
            
            htmlBody += `<table style="width: 100%; border-collapse: collapse; font-family: sans-serif; margin: 20px 0;">
                            <thead style="background-color: #f2f2f2;">
                                <tr>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Cliente</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Origen</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Carga</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">SO</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Factura</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Botellas</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Cajas</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Custodia</th>
                                </tr>
                            </thead>
                            <tbody>`;
            plannings.forEach(p => {
                const carga = p.order && p.order.channel === 'POSM' ? 'POSM' : 'Producto';
                htmlBody += `<tr>
                                <td style="border: 1px solid #ddd; padding: 8px;">${p.razon_social || 'N/A'}</td>
                                <td style="border: 1px solid #ddd; padding: 8px;">${p.origen || 'N/A'}</td>
                                <td style="border: 1px solid #ddd; padding: 8px;">${carga}</td>
                                <td style="border: 1px solid #ddd; padding: 8px;">${p.so_number || 'N/A'}</td>
                                <td style="border: 1px solid #ddd; padding: 8px;">${p.factura || 'N/A'}</td>
                                <td style="border: 1px solid #ddd; padding: 8px;">${p.pzs || 0}</td>
                                <td style="border: 1px solid #ddd; padding: 8px;">${p.cajas || 0}</td>
                                <td style="border: 1px solid #ddd; padding: 8px;">${guia.custodia || 'N/A'}</td>
                            </tr>`;
            });
            htmlBody += `</tbody></table>`;

            htmlBody += `<p>Se adjuntan los datos del transporte:</p>
                        <ul style="list-style-type: none; padding-left: 0;">
                            <li><strong>Línea de transporte:</strong> ${guia.transporte || 'N/A'}</li>
                            <li><strong>Fecha de carga:</strong> ${fechaFormato}</li>
                            <li><strong>Horario de carga:</strong> ${guia.hora_planeada || 'N/A'}</li>
                            <li><strong>Tipo de unidad:</strong> ${this.actualCapacity || 'N/A'}</li>
                            <li><strong>Operador:</strong> ${guia.operador || 'N/A'}</li>
                            <li><strong>Placas:</strong> ${guia.placas || 'N/A'}</li>
                        </ul>`;
            htmlBody += `<p>De antemano gracias y quedo atento a cualquier duda o comentario.</p><p>Saludos.</p>`;

            this.emailData.body = htmlBody;
            this.isEmailModalOpen = true;
        },

        sendEmail() {
            const finalHtmlBody = this.$refs.emailEditor.innerHTML;

            fetch('{{ route("customer-service.planning.send-email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    recipients: this.emailData.recipients,
                    subject: this.emailData.subject,
                    body: finalHtmlBody,
                    signature_url: this.signature
                })
            })
            .then(res => { if (!res.ok) throw new Error('El servidor respondió con un error.'); return res.json(); })
            .then(data => {
                if (data.success) { this.showFlashMessage(data.message, 'success'); this.isEmailModalOpen = false; }
                else { this.showFlashMessage(data.message || 'Ocurrió un error.', 'error'); }
            })
            .catch(error => { console.error("Error al enviar correo:", error); this.showFlashMessage('Error de conexión.', 'error'); });
        }
    }
}
document.addEventListener('alpine:init', () => {
    Alpine.data('planningManager', planningManager);
});
</script>
</x-app-layout>