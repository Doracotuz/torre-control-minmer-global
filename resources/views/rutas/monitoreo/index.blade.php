<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitoreo de Rutas Activas') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="monitoringManager()">
        <div x-show="notification.show" 
            x-transition 
            class="fixed top-5 right-5 z-[100] bg-white border-l-4 px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]"
            :class="{
                'border-[#ff9c00] text-[#2c3856]': notification.type === 'success',
                'border-red-500 text-red-800': notification.type !== 'success'
            }"
            style="display: none;">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" 
                    :class="notification.type === 'success' ? 'text-[#ff9c00]' : 'text-red-500'" 
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        :d="notification.type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'"></path>
                </svg>
                <div>
                    <strong class="font-bold" x-text="notification.type === 'success' ? '¡Éxito!' : '¡Error!'"></strong>
                    <span class="block sm:inline ml-1" x-text="notification.message"></span>
                </div>
            </div>
            <button @click="notification.show = false" class="text-gray-400 hover:text-gray-700">&times;</button>
        </div>

        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-8">

                <div class="lg:col-span-1 xl:col-span-1 space-y-6">
                    <div class="flex justify-between items-center">
                        <a href="{{ route('rutas.dashboard') }}" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-800">&larr; Volver al Dashboard</a>
                        <div class="flex gap-2">
                            <button @click="openEventModal()" :disabled="selectedGuias.length !== 1" class="px-4 py-2 bg-[#ff9c00] text-white rounded-md text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed">Evento</button>
                            <button @click="openReportModal()" :disabled="pagination.total === 0" class="px-4 py-2 bg-teal-600 text-white rounded-md text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed">Generar Reporte</button>
                        </div>
                    </div>
                    
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-[#2c3856] mb-4">Filtros</h3>
                        <div class="space-y-4">
                            <input type="text" placeholder="Guía, Operador, Ruta..." x-model.debounce.500ms="filters.search" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <select x-model="filters.estatus" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Rutas Activas (Default)</option>
                                <option value="Planeada">Planeadas</option>
                                <option value="Completada">Completadas</option>
                            </select>
                            <input type="date" x-model="filters.start_date" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <input type="date" x-model="filters.end_date" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <button type="button" @click="deselectAll()" class="w-full justify-center inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">Deseleccionar Todo</button>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md">
                        <div class="max-h-[60vh] overflow-y-auto">
                            <ul class="divide-y divide-gray-200">
                                <template x-if="isLoading"><li class="p-4 text-center text-sm text-gray-500">Cargando guías...</li></template>
                                <template x-if="!isLoading && guias.length === 0"><li class="p-4 text-center text-sm text-gray-500">No se encontraron guías.</li></template>
                                <template x-for="guia in guias" :key="guia.id">
                                    <li class="p-4 hover:bg-gray-50 flex justify-between items-center"><label class="flex items-center space-x-3 cursor-pointer"><input type="checkbox" class="h-5 w-5 rounded border-gray-300 text-[#ff9c00] focus:ring-[#ff9c00]" @change="updateSelection($el, guia.id)" :checked="selectedGuias.includes(String(guia.id))"><div><p class="text-sm font-bold text-gray-900"><span x-text="guia.guia"></span> | <span class="font-normal text-gray-600" x-text="guia.operador"></span></p><p class="text-xs text-gray-500" x-text="guia.ruta_nombre"></p></div></label><button @click="openDetailsModal(guia.id)" class="text-sm text-blue-600 hover:underline flex-shrink-0 ml-4">Detalles</button></li>
                                </template>
                            </ul>
                        </div>
                        <div x-show="!isLoading && pagination.total > 0" class="p-4 border-t flex items-center justify-between text-sm text-gray-600"><p>Mostrando <span x-text="pagination.from"></span> a <span x-text="pagination.to"></span> de <span x-text="pagination.total"></span></p><div class="flex gap-2"><button @click="changePage(pagination.prev_page_url)" :disabled="!pagination.prev_page_url" class="px-3 py-1 border rounded-md disabled:opacity-50">Ant.</button><button @click="changePage(pagination.next_page_url)" :disabled="!pagination.next_page_url" class="px-3 py-1 border rounded-md disabled:opacity-50">Sig.</button></div></div>
                    </div>
                </div>

                <div class="lg:col-span-2 xl:col-span-3">
                    <div id="monitoreo-map" class="w-full h-[85vh] rounded-lg shadow-md bg-gray-200 sticky top-8"></div>
                </div>
            </div>

<div x-show="isDetailsModalOpen" @keydown.escape.window="closeAllModals()" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" style="display: none;"><div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-6xl max-h-[90vh] flex flex-col"><div class="flex justify-between items-center border-b pb-3 mb-4"><h3 class="text-2xl font-bold text-[#2c3856]">Detalles de la Guía: <span x-text="selectedGuia?.guia" class="text-[#ff9c00]"></span></h3><div class="flex items-center gap-4"><template x-if="selectedGuia?.estatus === 'Planeada'"><button @click="openStartRouteModal()" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold hover:bg-green-700">Iniciar Ruta</button></template><button @click="closeAllModals()" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button></div></div><div class="flex-grow overflow-y-auto pr-4" x-show="selectedGuia"><div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6 bg-gray-50 p-4 rounded-lg"><div><strong class="block text-gray-500 text-sm">Operador:</strong> <span x-text="selectedGuia?.operador"></span></div><div><strong class="block text-gray-500 text-sm">Placas:</strong> <span x-text="selectedGuia?.placas"></span></div><div><strong class="block text-gray-500 text-sm">Ruta Asignada:</strong> <span x-text="selectedGuia?.ruta_nombre"></span></div><div><strong class="block text-gray-500 text-sm">Estatus:</strong> <span x-text="selectedGuia?.estatus"></span></div><div><strong class="block text-gray-500 text-sm">Inicio:</strong> <span x-text="selectedGuia?.fecha_inicio_ruta || 'N/A'"></span></div><div><strong class="block text-gray-500 text-sm">Fin:</strong> <span x-text="selectedGuia?.fecha_fin_ruta || 'N/A'"></span></div></div><div><h4 class="text-lg font-semibold text-gray-800 mb-2">Facturas Incluidas</h4><div class="border rounded-lg overflow-hidden text-sm max-h-60 overflow-y-auto mb-8"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50 sticky top-0"><tr class="text-left text-xs font-medium text-gray-500 uppercase"><th class="px-4 py-2"># Factura</th><th class="px-4 py-2">Destino</th><th class="px-4 py-2">Estatus</th></tr></thead><tbody class="bg-white divide-y divide-gray-200"><template x-for="factura in selectedGuia?.facturas || []" :key="factura.id"><tr><td class="px-4 py-2" x-text="factura.numero_factura"></td><td class="px-4 py-2" x-text="factura.destino"></td><td class="px-4 py-2" x-text="factura.estatus_entrega"></td></tr></template></tbody></table></div></div><div class="grid grid-cols-1 lg:grid-cols-2 gap-8"><div><h4 class="text-lg font-semibold text-gray-800 mb-4">Línea de Tiempo</h4><div class="border-l-2 border-blue-500 pl-6 space-y-6 relative max-h-96 overflow-y-auto pr-4"><template x-if="!selectedGuia?.eventos || selectedGuia.eventos.length === 0"><p class="text-sm text-gray-500">No hay eventos.</p></template><template x-for="evento in selectedGuia?.eventos || []" :key="evento.id"><div class="relative"><div class="absolute -left-[35px] top-1 h-6 w-6 rounded-full flex items-center justify-center" :class="getIconForEvent(evento.tipo, true)"><svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" x-html="getIconForEvent(evento.tipo, false)"></svg></div><div class="ml-4"><p class="font-bold text-gray-800" x-text="evento.subtipo"></p><p class="text-sm text-gray-500" x-text="evento.fecha_evento"></p><p class="text-sm mt-1" x-text="evento.nota || 'Sin notas.'"></p><template x-if="evento.url_evidencia && evento.url_evidencia.length > 0"><div class="mt-2"><a :href="evento.url_evidencia[0]" target="_blank" class="text-sm font-semibold text-blue-600 hover:underline">Ver Evidencia</a></div></template></div></div></template></div></div><div><h4 class="text-lg font-semibold text-gray-800 mb-4">Tabla de Eventos</h4><div class="border rounded-lg overflow-hidden max-h-96 overflow-y-auto"><table class="min-w-full divide-y divide-gray-200 text-sm"><thead class="bg-gray-100 sticky top-0"><tr class="text-left text-xs font-medium text-gray-500 uppercase"><th class="px-4 py-2">Evento</th><th class="px-4 py-2">Fecha</th></tr></thead><tbody class="bg-white divide-y divide-gray-200"><template x-if="!selectedGuia?.eventos || selectedGuia.eventos.length === 0"><tr><td colspan="2" class="p-4 text-center text-gray-500">No hay eventos.</td></tr></template><template x-for="evento in selectedGuia?.eventos || []" :key="evento.id + '_table'"><tr><td class="px-4 py-2 font-medium" x-text="evento.subtipo"></td><td class="px-4 py-2" x-text="evento.fecha_evento"></td></tr></template></tbody></table></div></div></div></div></div></div>

            <div x-show="isEventModalOpen" @keydown.escape.window="closeAllModals()" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4" style="display: none;">
                <div @click.outside="closeAllModals()" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg">
                    <h3 class="text-xl font-bold text-[#2c3856] mb-6 border-b pb-3">Registrar Nuevo Evento</h3>
                    <template x-if="selectedGuia">
                        <form id="event-form" @submit.prevent="submitEventForm" method="POST" enctype="multipart/form-data">
                        @csrf
                            <input type="hidden" name="latitud" x-model="evento.lat">
                            <input type="hidden" name="longitud" x-model="evento.lng">
                            <input type="hidden" name="municipio" id="event-municipio">
                            <div class="grid grid-cols-2 gap-6">
                                <div><label class="block text-sm font-medium">Categoría</label><select name="tipo" x-model="evento.tipo" @change="updateSubtypeOptions()" class="mt-1 w-full rounded-md border-gray-300"><option value="Sistema">Flujo</option><option value="Notificacion">Notificación</option><option value="Incidencias">Incidencias</option><option value="Entrega">Entrega</option></select></div>
                                <div><label class="block text-sm font-medium">Detalle</label><select name="subtipo" x-model="evento.subtipo" class="mt-1 w-full rounded-md border-gray-300"><template x-for="subtype in availableSubtypes" :key="subtype"><option :value="subtype" x-text="subtype"></option></template></select></div>
                                
                                <div class="col-span-2" x-show="modalRequiresInvoices()" x-transition>
                                    <label class="block text-sm font-medium text-gray-700">Facturas Afectadas</label>
                                    <div class="max-h-32 overflow-y-auto border rounded-md p-2 mt-1 space-y-1">
                                        <template x-for="factura in availableInvoicesForModal" :key="factura.id">
                                            <label class="flex items-center"><input type="checkbox" :value="factura.id" x-model="evento.factura_ids" class="rounded"><span class="ml-2 text-sm" x-text="factura.numero_factura"></span></label>
                                        </template>
                                    </div>
                                </div>
                                
                                <div class="col-span-2 text-xs text-gray-500 -mt-2"><p>Clic derecho en el mapa para obtener ubicación.</p></div>
                                <div class="col-span-1">
                                    <label class="block text-sm font-medium">Fecha del Evento</label>
                                    <input type="date" x-model="evento.fecha" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div class="col-span-1">
                                    <label class="block text-sm font-medium">Hora del Evento</label>
                                    <input type="time" x-model="evento.hora" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>                                
                                <div><label class="block text-sm font-medium">Latitud</label><input type="text" x-model="evento.lat" required class="mt-1 w-full rounded-md border-gray-300"></div>
                                <div><label class="block text-sm font-medium">Longitud</label><input type="text" x-model="evento.lng" required class="mt-1 w-full rounded-md border-gray-300"></div>
                                <div class="col-span-2"><label class="block text-sm font-medium">Nota</label><textarea name="nota" x-model="evento.nota" rows="2" class="mt-1 w-full rounded-md border-gray-300"></textarea></div>
                                <div class="col-span-2"><label class="block text-sm font-medium">Evidencia</label><input type="file" id="event_evidence_input" multiple class="mt-1 block w-full text-sm"></div>
                            </div>
                            <div class="mt-8 flex justify-end gap-4"><button type="button" @click="closeAllModals()" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button><button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-[#2c3856] text-white rounded-md disabled:opacity-50">Guardar</button></div>
                        </form>
                    </template>
                </div>
            </div>
            <div x-show="isReportModalOpen" 
                @keydown.escape.window="closeAllModals()" 
                x-transition 
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4" 
                style="display: none;">
                
                <div @click.outside="closeAllModals()" 
                    class="bg-white rounded-lg shadow-xl w-[98%] max-h-[90vh] flex flex-col">
                    
                    <div class="flex justify-between items-center border-b p-4">
                        <h3 class="text-2xl font-bold text-[#2c3856]">Generar Reporte</h3>
                        <div class="flex items-center gap-4">
                            <a :href="generateExportUrl()" 
                            target="_blank" 
                            class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold hover:bg-green-700">
                            Exportar a CSV
                            </a>
                            <button @click="isColumnSelectorOpen = true" 
                                    class="p-2 bg-gray-200 rounded-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </button>
                            <button @click="closeAllModals()" 
                                    class="text-gray-400 hover:text-gray-700 text-2xl">
                                &times;
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-4 border-b grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Fecha de Carga (Desde)</label>
                            <input type="date" 
                                x-model="reportStartDate" 
                                @change="fetchReportData()" 
                                class="mt-1 w-full rounded-md text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Fecha de Carga (Hasta)</label>
                            <input type="date" 
                                x-model="reportEndDate" 
                                @change="fetchReportData()" 
                                class="mt-1 w-full rounded-md text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Filtrar por Estatus (R)</label>
                            <div class="mt-1 p-2 border rounded-md max-h-32 overflow-y-auto space-y-1">
                                <template x-for="status in availableStatuses" :key="status">
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                            :value="status" 
                                            x-model="reportStatusFilter" 
                                            @change="fetchReportData()" 
                                            class="rounded text-[#ff9c00]">
                                        <span class="ml-2 text-sm" x-text="status"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6 flex-grow overflow-y-auto bg-gray-50">
                        <template x-if="isReportLoading">
                            <div class="text-center py-12">Generando reporte...</div>
                        </template>
                        
                        <template x-if="!isReportLoading && reportData">
                            <div class="space-y-6">

                                <div class="flex items-center justify-between">
                                    <h4 class="text-lg font-semibold text-gray-800">Resumen Gráfico</h4>
                                    <button @click="showReportCharts = !showReportCharts" class="text-sm text-blue-600 font-semibold flex items-center">
                                        <span x-text="showReportCharts ? 'Ocultar' : 'Mostrar'"></span>
                                        <svg class="w-5 h-5 ml-1 transition-transform" :class="{'rotate-180': !showReportCharts}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                </div>                                
                                <div x-show="showReportCharts" x-transition class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <div class="bg-white p-4 rounded-lg shadow-md border h-56"><canvas id="reportChartStatus"></canvas></div>
                                    <div class="bg-white p-4 rounded-lg shadow-md border h-56"><canvas id="reportChartInvoices"></canvas></div>
                                    <div class="bg-white p-4 rounded-lg shadow-md border h-56"><canvas id="reportChartRegions"></canvas></div>
                                </div>
                                
                                <div class="bg-white p-4 rounded-lg shadow-md border">
                                    <div class="max-h-[40vh] overflow-y-auto">
                                        <table class="min-w-full text-sm">
                                            <thead>
                                                <tr class="text-left text-xs font-medium uppercase">
                                                    <template x-for="column in visibleColumns" :key="column">
                                                        <th class="px-4 py-2" x-text="getColumnLabel(column)"></th>
                                                    </template>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-if="!reportData.tableData || reportData.tableData.length === 0">
                                                    <tr>
                                                        <td :colspan="visibleColumns.length" class="text-center p-4">
                                                            No hay datos para mostrar.
                                                        </td>
                                                    </tr>
                                                </template>
                                                <template x-for="(row, index) in reportData.tableData" :key="index">
                                                    <tr>
                                                        <template x-for="columnKey in visibleColumns" :key="columnKey">
                                                            <td class="px-4 py-2" x-text="row[columnKey]"></td>
                                                        </template>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div x-show="isStartRouteModalOpen" @keydown.escape.window="closeAllModals()" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4" style="display: none;">
                <div @click.outside="closeAllModals()" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-sm">
                    <h3 class="text-xl font-bold text-[#2c3856] mb-6 border-b pb-3">Iniciar Ruta: <span x-text="selectedGuia?.guia"></span></h3>
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600">Para iniciar esta ruta, por favor, ingresa las coordenadas y la hora de inicio.</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Latitud</label>
                            <input type="text" x-model="startRouteCoords.lat" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Longitud</label>
                            <input type="text" x-model="startRouteCoords.lng" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                            <input type="date" x-model="startRouteCoords.date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Hora de Inicio</label>
                            <input type="time" x-model="startRouteCoords.time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-4">
                        <button type="button" @click="closeAllModals()" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button>
                        <button type="button" @click="startRouteFromMonitor(startRouteCoords.lat, startRouteCoords.lng, startRouteCoords.date, startRouteCoords.time)" :disabled="isSubmitting || !startRouteCoords.lat || !startRouteCoords.lng || !startRouteCoords.date || !startRouteCoords.time" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold disabled:opacity-50">Confirmar Inicio</button>
                    </div>
                </div>
            </div>
            </div>

            <div x-show="isColumnSelectorOpen" @keydown.escape.window="isColumnSelectorOpen = false" x-transition class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 p-4" style="display: none;"><div @click.outside="isColumnSelectorOpen = false" class="bg-white rounded-lg shadow-xl w-full max-w-2xl"><div class="flex justify-between items-center border-b p-4"><h3 class="text-lg font-bold">Configurar Columnas</h3><button @click="isColumnSelectorOpen = false" class="text-gray-400">&times;</button></div><div class="p-6"><div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm"><template x-for="column in allColumns" :key="column.key"><label class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100"><input type="checkbox" x-model="visibleColumns" :value="column.key" class="rounded"><span x-text="column.label"></span></label></template></div></div><div class="p-4 bg-gray-50 border-t text-right"><button @click="isColumnSelectorOpen = false" class="px-4 py-2 bg-[#2c3856] text-white rounded-md text-sm">Cerrar</button></div></div></div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places,drawing&callback=initMonitoreoMap" async defer></script>
    
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('monitoringManager', () => ({
            isLoading: true,
            selectedGuias: JSON.parse(sessionStorage.getItem('selectedGuias')) || [],
            isEventModalOpen: false,
            isDetailsModalOpen: false,
            isReportModalOpen: false,
            isColumnSelectorOpen: false,
            isStartRouteModalOpen: false,
            selectedGuia: null,
            guias: [],
            pagination: {},
            filters: { search: '', estatus: '', start_date: '', end_date: '', page: 1 },
            isReportLoading: false,
            reportData: null,
            reportDate: '',
            charts: {},
            availableRegions: [],
            reportRegionFilter: '',
            reportStartDate: '',
            reportEndDate: '',
            availableStatuses: [],
            reportStatusFilter: [],
            startRouteCoords: { lat: '', lng: '', date: '', time: '' },
            showReportCharts: true,
            allColumns: [
                { key: 'fecha_carga', label: 'Fecha Carga' },
                { key: 'guia', label: 'Guía' },
                { key: 'ruta_nombre', label: 'Ruta Asignada' },
                { key: 'operador', label: 'Operador' },
                { key: 'placas', label: 'Placas' },
                { key: 'factura', label: 'Factura' },
                { key: 'so', label: 'SO' },
                { key: 'destino', label: 'Destino' },
                { key: 'cajas', label: 'Cajas' },
                { key: 'botellas', label: 'Botellas' },
                { key: 'estatus_r', label: 'Estatus Guía' },
                { key: 'estatus_f', label: 'Estatus Factura' },
                { key: 'entregada', label: 'Fecha Entrega' },
                { key: 'custodia', label: 'Custodia' },
                { key: 'hora_planeada', label: 'Hora Planeada' },
            ],
            visibleColumns: [],
            evento: { tipo: 'Sistema', subtipo: '', lat: '', lng: '', nota: '', factura_ids: [], fecha: '', hora: '' },
            isSubmitting: false,
            notification: { show: false, message: '', type: 'success' },

            eventSubtypes: {
                'Sistema': ['Llegada a carga', 'Fin de carga', 'En ruta', 'Llegada a cliente', 'Proceso de entrega'],
                'Entrega': ['Entregada', 'No entregada'],
                'Notificacion': ['Alimentos', 'Combustible', 'Sanitario', 'Pernocta', 'Otro'],
                'Incidencias': ['Rechazo', 'Percance', 'Tráfico', 'Falla mecánica', 'Incidencia con autoridad', 'Otro']
            },
            availableSubtypes: [],

            init() {
                const savedColumns = localStorage.getItem('visibleReportColumns');
                const now = new Date();
                const currentDate = now.toISOString().split('T')[0];
                const currentTime = now.toTimeString().split(' ')[0].substring(0, 5);
                this.startRouteCoords.date = currentDate;
                this.startRouteCoords.time = currentTime;                
                this.visibleColumns = savedColumns ? JSON.parse(savedColumns) : ['fecha_carga', 'guia', 'operador', 'factura', 'destino', 'estatus_r', 'estatus_f', 'entregada'];
                this.$watch('visibleColumns', (value) => localStorage.setItem('visibleReportColumns', JSON.stringify(value)));
                this.applyFilters();
                this.loadAvailableRegions();
                this.loadAvailableStatuses();
                if (typeof monitoreoMap !== 'undefined') monitoreoMap.addListener('rightclick', (event) => this.handleMapRightClick(event.latLng));
                else setTimeout(() => { if (typeof monitoreoMap !== 'undefined') monitoreoMap.addListener('rightclick', (event) => this.handleMapRightClick(event.latLng)); }, 1000);
                this.$watch('filters', () => { this.filters.page = 1; this.applyFilters(); });
                this.$watch('selectedGuias', (newSelection) => { sessionStorage.setItem('selectedGuias', JSON.stringify(newSelection)); this.redrawMap(); });
            },
            applyFilters() { this.isLoading = true; const params = new URLSearchParams(this.filters).toString(); fetch(`{{ route('rutas.monitoreo.filter') }}?${params}`).then(r => r.json()).then(d => { this.guias = d.paginator.data; this.pagination = d.paginator; window.guiasData = d.guiasJson; this.isLoading = false; this.redrawMap(); }); },
            changePage(url) { if (!url) return; this.filters.page = new URL(url).searchParams.get('page'); },
            redrawMap() { Object.keys(activeRenderers).forEach(id => removeMonitoreoRoute(id)); this.selectedGuias.forEach(id => { if (window.guiasData && window.guiasData[id]) drawMonitoreoRoute(id); }); },
            openDetailsModal(guiaId) { this.selectedGuia = window.guiasData[guiaId] || null; this.isDetailsModalOpen = true; },
            closeAllModals() { this.isDetailsModalOpen = false; this.isEventModalOpen = false; this.isReportModalOpen = false; this.isColumnSelectorOpen = false; this.isStartRouteModalOpen = false; },
            updateSelection(checkbox, guiaId) { guiaId = String(guiaId); if (checkbox.checked) { this.selectedGuias = [...new Set([...this.selectedGuias, guiaId])]; } else { this.selectedGuias = this.selectedGuias.filter(id => id !== guiaId); } },
            deselectAll() { this.selectedGuias = []; },
            getIconForEvent(type, getBgClass) { const i = { 'Sistema': { bg: 'bg-blue-500', svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />` }, 'Entrega': { bg: 'bg-green-500', svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />` }, 'Notificacion': { bg: 'bg-yellow-500', svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />` }, 'Incidencias': { bg: 'bg-red-500', svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.42-.38-2.75-.99-3.921m-16.14 0a11.959 11.959 0 0116.14 0" />` }}; const s = i[type] || i['Sistema']; return getBgClass ? s.bg : s.svg; },
            openEventModal() {
                if (this.selectedGuias.length !== 1) { alert("Selecciona solo una guía."); return; }
                const guiaId = this.selectedGuias[0];
                this.selectedGuia = window.guiasData[guiaId];
                
                const now = new Date();
                const currentDate = now.toISOString().split('T')[0];
                const currentTime = now.toTimeString().split(' ')[0].substring(0, 5);

                this.evento = { 
                    tipo: 'Sistema', 
                    subtipo: '', 
                    lat: '', 
                    lng: '', 
                    nota: '', 
                    factura_ids: [],
                    fecha: currentDate,
                    hora: currentTime
                };
                this.updateSubtypeOptions();
                this.isEventModalOpen = true;
            },

            openStartRouteModal() {
                this.isStartRouteModalOpen = true;
                const now = new Date();
                const currentDate = now.toISOString().split('T')[0];
                const currentTime = now.toTimeString().split(' ')[0].substring(0, 5);
                this.startRouteCoords.lat = '19.619794982524613';
                this.startRouteCoords.lng = '-99.16364845466741';
                this.startRouteCoords.date = currentDate;
                this.startRouteCoords.time = currentTime;
            },

            handleMapRightClick(latLng) {
                if(this.selectedGuias.length !== 1) {
                    alert("Por favor, selecciona solo una guía para añadir un evento desde el mapa.");
                    return;
                }
                const guiaId = this.selectedGuias[0];
                this.selectedGuia = window.guiasData[guiaId];

                this.evento = {
                    tipo: 'Sistema',
                    subtipo: '',
                    lat: latLng.lat().toFixed(6),
                    lng: latLng.lng().toFixed(6),
                    nota: '',
                    factura_ids: []
                };
                
                this.updateSubtypeOptions();

                this.isEventModalOpen = true;
            },

            updateSubtypeOptions() {
                this.availableSubtypes = this.eventSubtypes[this.evento.tipo] || [];
                this.evento.subtipo = this.availableSubtypes[0] || '';
            },
            modalRequiresInvoices() {
                const subtypes = ['Llegada a cliente', 'Proceso de entrega', 'Entregada', 'No entregada'];
                return subtypes.includes(this.evento.subtipo);
            },
            get availableInvoicesForModal() {
                if (!this.selectedGuia) return [];
                const subtype = this.evento.subtipo;
                if (subtype === 'Llegada a cliente') return this.selectedGuia.facturas.filter(f => f.estatus_entrega === 'En tránsito');
                if (subtype === 'Proceso de entrega') return this.selectedGuia.facturas.filter(f => f.estatus_entrega === 'En cliente');
                if (subtype === 'Entregada' || subtype === 'No entregada') return this.selectedGuia.facturas.filter(f => f.estatus_entrega === 'Entregando');
                return this.selectedGuia.facturas;
            },
            getSelectedGuiaFacturas() { if (!this.selectedGuia) return []; return this.selectedGuia.facturas.filter(f => f.estatus_entrega === 'Pendiente'); },
            loadAvailableRegions() { fetch(`{{ route('rutas.monitoreo.regions') }}`).then(r => r.json()).then(d => this.availableRegions = d); },
            loadAvailableStatuses() { fetch(`{{ route('rutas.monitoreo.get-statuses') }}`).then(res => res.json()).then(data => this.availableStatuses = data); },
            openReportModal() { this.isReportModalOpen = true; this.reportStartDate = ''; this.reportEndDate = ''; this.reportStatusFilter = []; this.reportRegionFilter = ''; this.fetchReportData(); },
            generateExportUrl() { const params = new URLSearchParams({ ...this.filters, region: this.reportRegionFilter, report_start_date: this.reportStartDate, report_end_date: this.reportEndDate }); this.reportStatusFilter.forEach(status => params.append('report_statuses[]', status)); return `{{ route('rutas.monitoreo.export.report') }}?${params.toString()}`; },
            fetchReportData() {
                this.isReportLoading = true; this.reportData = null; this.reportDate = new Date().toLocaleString('es-MX');
                const params = new URLSearchParams({ ...this.filters, region: this.reportRegionFilter, report_start_date: this.reportStartDate, report_end_date: this.reportEndDate, });
                this.reportStatusFilter.forEach(status => params.append('report_statuses[]', status));
                fetch(`{{ route('rutas.monitoreo.report') }}?${params.toString()}`).then(r => r.json()).then(d => { this.reportData = d; this.isReportLoading = false; this.$nextTick(() => this.renderReportCharts()); });
            },
            renderReportCharts() { if(this.charts.status) this.charts.status.destroy(); if(this.charts.invoices) this.charts.invoices.destroy(); if(this.charts.regions) this.charts.regions.destroy(); const c1 = document.getElementById('reportChartStatus').getContext('2d'); this.charts.status = new Chart(c1, { type: 'doughnut', data: { labels: this.reportData.charts.guiasPorEstatus.labels, datasets: [{ data: this.reportData.charts.guiasPorEstatus.data }] }, options: { plugins: { title: { display: true, text: 'Guías por Estatus' } } } }); const c2 = document.getElementById('reportChartInvoices').getContext('2d'); this.charts.invoices = new Chart(c2, { type: 'pie', data: { labels: this.reportData.charts.facturasPorEstatus.labels, datasets: [{ data: this.reportData.charts.facturasPorEstatus.data }] }, options: { plugins: { title: { display: true, text: 'Facturas por Estatus' } } } }); const c3 = document.getElementById('reportChartRegions').getContext('2d'); this.charts.regions = new Chart(c3, { type: 'bar', data: { labels: this.reportData.charts.facturasPorRegion.labels, datasets: [{ label: 'Facturas', data: this.reportData.charts.facturasPorRegion.data }] }, options: { indexAxis: 'y', plugins: { title: { display: true, text: 'Facturas por Región' } } } }); },
            getColumnLabel(key) { const c = this.allColumns.find(c => c.key === key); return c ? c.label : ''; },
            startRouteFromMonitor(lat, lng, date, time) {
                if (!this.selectedGuia) return;
                this.isSubmitting = true;

                const combinedDateTime = `${date} ${time}:00`;

                let municipio = 'N/A';
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ location: { lat: parseFloat(lat), lng: parseFloat(lng) } })
                    .then(({ results }) => {
                        if (results[0]) {
                            for (const component of results[0].address_components) {
                                if (component.types.includes("locality")) {
                                    municipio = component.long_name;
                                    break;
                                }
                            }
                        }
                        return municipio;
                    })
                    .catch(() => {
                        municipio = "Error Geocoding";
                        return municipio;
                    })
                    .finally(() => {
                        fetch(`/rutas/monitoreo/${this.selectedGuia.id}/start`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                latitud: lat,
                                longitud: lng,
                                municipio: municipio,
                                fecha_inicio_ruta: combinedDateTime
                            })
                        })
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) {
                                this.showNotification(d.message, 'success');
                                this.closeAllModals();
                                this.applyFilters();
                            } else {
                                this.showNotification(d.message || 'Error.', 'error');
                            }
                        })
                        .catch(() => this.showNotification('Error de conexión.', 'error'))
                        .finally(() => this.isSubmitting = false);
                    });
            },
            submitEventForm() {
                this.isSubmitting = true;
                
                if (this.evento.lat && this.evento.lng) {
                    this.sendEventData(this.evento.lat, this.evento.lng);
                } else {
                    this.getLocationAndSend();
                }
            },

            /**
             * @param {HTMLFormElement} form
             */
            getLocationAndSend() {
                if (!navigator.geolocation) {
                    alert('La geolocalización no está disponible.');
                    this.isSubmitting = false;
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.sendEventData(position.coords.latitude, position.coords.longitude);
                    },
                    () => {
                        alert('No se pudo obtener la ubicación. Activa el GPS y otorga los permisos.');
                        this.isSubmitting = false;
                    },
                    { enableHighAccuracy: true }
                );
            },

            async sendEventData(lat, lng) {
                let municipio = 'N/A';
                if (typeof google !== 'undefined' && google.maps) {
                    const geocoder = new google.maps.Geocoder();
                    try {
                        const { results } = await geocoder.geocode({ location: { lat, lng } });
                        if (results[0]) {
                            for (const component of results[0].address_components) {
                                if (component.types.includes("locality")) {
                                    municipio = component.long_name; break;
                                }
                            }
                        }
                    } catch (e) {
                        municipio = "Error Geocoding";
                    }
                }

                const formData = new FormData();
                const files = document.getElementById('event_evidence_input').files;

                if (this.evento.fecha && this.evento.hora) {
                    const combinedDateTime = `${this.evento.fecha} ${this.evento.hora}:00`;
                    formData.append('fecha_evento', combinedDateTime);
                }                

                formData.append('tipo', this.evento.tipo);
                formData.append('subtipo', this.evento.subtipo);
                formData.append('nota', this.evento.nota);
                formData.append('latitud', lat);
                formData.append('longitud', lng);
                formData.append('municipio', municipio);
                this.evento.factura_ids.forEach(id => formData.append('factura_ids[]', id));
                for (let i = 0; i < files.length; i++) {
                    formData.append('evidencia[]', files[i]);
                }

                fetch(`/rutas/monitoreo/${this.selectedGuia.id}/events`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.showNotification(data.message, 'success');
                        this.closeAllModals();
                        this.applyFilters();
                    } else {
                        this.showNotification(data.message || 'Ocurrió un error.', 'error');
                    }
                })
                .catch(() => this.showNotification('Error de conexión. Inténtalo de nuevo.', 'error'))
                .finally(() => this.isSubmitting = false);
            },

                showNotification(message, type = 'success') {
                    this.notification.message = message;
                    this.notification.type = type;
                    this.notification.show = true;
                    setTimeout(() => {
                        this.notification.show = false;
                    }, 5000);
                }

        }));
    });
    </script>
</x-app-layout>