<div>
    {{-- VISTA DE TABLA PARA ESCRITORIO --}}
    <div class="hidden md:block bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="w-full table-fixed">
                <thead class="bg-[#2c3856]">
                    <tr>
                        <th class="px-2 py-1 text-center text-xs font-medium text-white uppercase tracking-wider w-12 no-drag">
                            <input type="checkbox" @click="$dispatch('toggle-all-plannings', $event.target.checked)" class="rounded">
                        </th>

                        <template x-for="(columnKey, index) in columnOrder" :key="`${columnKey}-${index}`">
                            <th x-show="visibleColumns[columnKey]"
                                @click="sortBy(columnKey, $event)"
                                :class="{ 'bg-gray-700': getSortState(columnKey) }"
                                :data-column="columnKey"
                                :style="columnWidths[columnKey] ? { width: columnWidths[columnKey] } : {}"
                                class="px-2 py-1 text-center text-xs font-medium text-white uppercase tracking-wider select-none border-l border-gray-400 cursor-pointer">
                                
                                <span class="drag-handle cursor-move" x-text="allColumns[columnKey]"></span>

                                <template x-if="getSortState(columnKey)">
                                    <span>
                                        <i class="fas" :class="{ 'fa-arrow-up': getSortState(columnKey).dir === 'asc', 'fa-arrow-down': getSortState(columnKey).dir === 'desc' }"></i>
                                        <sup class="ml-1 font-bold text-xs" x-text="getSortState(columnKey).priority"></sup>
                                    </span>
                                </template>
                                
                                <div class="resizer"></div>
                            </th>
                        </template>
                        
                        <th class="px-2 py-1 text-center text-xs font-medium text-white uppercase tracking-wider border-l border-gray-400 no-drag w-[180px]">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="planning in plannings" :key="planning.id">
                        <tr class="transition-colors duration-150 hover:bg-gray-200"
                            :class="{
                                'flashing-row': planning.origen !== planning.destino && !planning.is_scale && !planning.is_direct_route, 
                                'bg-blue-50': selectedPlannings.includes(planning.id)
                            }">
                            <td class="px-2 py-1 border text-center">
                                <input type="checkbox" :value="planning.id" x-model="selectedPlannings" class="rounded">
                            </td>

                            <template x-for="columnKey in columnOrder" :key="columnKey">
                                <td x-show="visibleColumns[columnKey]" class="px-2 py-1 text-sm border" :title="getFormattedCell(planning, columnKey)">
                                    <div class="truncate">
                                        <template x-if="columnKey === 'status'">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="{
                                                    'bg-yellow-100 text-yellow-800': planning.status === 'En Espera',
                                                    'bg-green-100 text-green-800': planning.status === 'Programada',
                                                    'bg-blue-100 text-blue-800': planning.status === 'Asignado en Guía'
                                                }" x-text="planning.status">
                                            </span>
                                        </template>
                                        <template x-if="columnKey !== 'status'">
                                            <span x-html="getFormattedCell(planning, columnKey)"></span>
                                        </template>
                                    </div>
                                </td>
                            </template>
                            
                            <td class="px-2 py-1 border w-[180px]">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <template x-if="planning.guia">
                                        <button @click="openGuiaDetailModal(planning)" type="button" class="text-green-600 hover:text-green-900" title="Ver Detalle de Guía">
                                            <i class="fas fa-truck"></i>
                                        </button>
                                    </template>                                    
                                    <a :href="`/customer-service/planning/${planning.id}`" class="text-gray-600 hover:text-gray-900" title="Ver Detalle"><i class="fas fa-eye"></i></a>
                                    <a :href="`/customer-service/planning/${planning.id}/edit`" class="text-indigo-600 hover:text-indigo-900" title="Editar"><i class="fas fa-edit"></i></a>
                                    <template x-if="planning.origen !== planning.destino && !planning.is_scale && !planning.is_direct_route">
                                        <span class="flex items-center justify-center gap-2">
                                            <button @click="openScalesModal(planning)" class="px-3 py-1 bg-purple-600 text-white rounded-md text-xs font-semibold hover:bg-purple-700" title="Dividir en Escalas">Escalas</button>
                                            <button @click="markAsDirect(planning.id)" class="px-3 py-1 bg-gray-500 text-white rounded-md text-xs font-semibold hover:bg-gray-600" title="Marcar como ruta directa">No Escala</button>
                                        </span>
                                    </template>
                                    <template x-if="planning.status === 'En Espera'">
                                        <form :action="`/customer-service/planning/${planning.id}/schedule`" method="POST" class="inline" onsubmit="return confirm('¿Programar esta ruta?');"> @csrf <button type="submit" class="px-3 py-1 bg-green-500 text-white rounded-md text-xs font-semibold hover:bg-green-600">Programar</button> </form>
                                    </template>
                                    <template x-if="planning.status === 'Asignado en Guía'">
                                        <form :action="`/customer-service/planning/${planning.id}/disassociate-from-guia`" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que quieres quitar esta orden de su guía?');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded-md text-xs font-semibold hover:bg-red-700" title="Quitar de la Guía">Quitar</button>
                                        </form>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!isLoading && plannings.length === 0">
                        <tr><td :colspan="Object.values(visibleColumns).filter(v => v).length + 2" class="px-3 py-4 text-center text-gray-500">No se encontraron registros.</td></tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- VISTA DE TARJETAS PARA MÓVIL --}}
    <div class="block md:hidden space-y-4">
        <template x-for="planning in plannings" :key="planning.id">
            <div class="bg-white rounded-lg shadow-md p-4 border"
                 :class="{ 'border-blue-500 ring-2 ring-blue-200': selectedPlannings.includes(planning.id) }">
                
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-start">
                        <input type="checkbox" :value="planning.id" x-model="selectedPlannings" class="rounded mt-1">
                        <div class="ml-3">
                            {{-- CORRECCIÓN: Usamos x-html para mostrar el ícono de custodia --}}
                            <p class="font-bold text-gray-800" x-text="planning.razon_social"></p>
                            <p class="text-sm text-gray-500" x-text="`SO: ${planning.so_number || 'N/A'}`"></p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap"
                          :class="{
                            'bg-yellow-100 text-yellow-800': planning.status === 'En Espera',
                            'bg-green-100 text-green-800': planning.status === 'Programada',
                            'bg-blue-100 text-blue-800': planning.status === 'Asignado en Guía'
                          }" x-text="planning.status">
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm border-t pt-3">
                    <div><strong class="block text-gray-500">Factura:</strong><span x-text="planning.factura || 'N/A'"></span></div>
                    <div><strong class="block text-gray-500">Guía:</strong><span x-html="getFormattedCell(planning, 'guia')"></span></div>
                    <div><strong class="block text-gray-500">Origen:</strong><span x-text="planning.origen || 'N/A'"></span></div>
                    <div><strong class="block text-gray-500">Destino:</strong><span x-text="planning.destino || 'N/A'"></span></div>
                    <div class="col-span-2"><strong class="block text-gray-500">F. Entrega:</strong><span x-text="getFormattedCell(planning, 'fecha_entrega')"></span></div>
                </div>

                <div class="flex items-center justify-end space-x-2 border-t mt-3 pt-3">
                    <template x-if="planning.guia">
                        <button @click="openGuiaDetailModal(planning)" type="button" class="text-green-600 hover:text-green-900 p-1" title="Ver Detalle de Guía">
                            <i class="fas fa-truck"></i>
                        </button>
                    </template>                    
                    <a :href="`/customer-service/planning/${planning.id}`" class="text-gray-500 hover:text-gray-800 p-1" title="Ver Detalle"><i class="fas fa-eye"></i></a>
                    <a :href="`/customer-service/planning/${planning.id}/edit`" class="text-indigo-500 hover:text-indigo-700 p-1" title="Editar"><i class="fas fa-edit"></i></a>
                    <template x-if="planning.status === 'En Espera'">
                        <form :action="`/customer-service/planning/${planning.id}/schedule`" method="POST" class="inline" onsubmit="return confirm('¿Programar esta ruta?');"> @csrf <button type="submit" class="px-3 py-1 bg-green-500 text-white rounded-md text-xs font-semibold hover:bg-green-600">Programar</button> </form>
                    </template>
                </div>
            </div>
        </template>
         <template x-if="!isLoading && plannings.length === 0">
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">No se encontraron registros.</div>
        </template>
    </div>
</div>