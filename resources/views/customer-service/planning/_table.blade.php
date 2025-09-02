<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="overflow-x-auto">
        <table class="min-w-full table-fixed">
            <thead class="bg-[#2c3856]">
                <tr>
                    <th class="px-2 py-1 text-center text-xs font-medium text-white uppercase tracking-wider w-12 no-drag">
                        <input type="checkbox" @click="$dispatch('toggle-all-plannings', $event.target.checked)" class="rounded">
                    </th>

                    <template x-for="(columnKey, index) in columnOrder" :key="`${columnKey}-${index}`">
                        <th x-show="visibleColumns[columnKey]"
                            :data-column="columnKey"
                            :style="columnWidths[columnKey] ? { width: columnWidths[columnKey] } : {}"
                            class="px-2 py-1 text-center text-xs font-medium text-white uppercase tracking-wider select-none border-l border-gray-400">
                            <span class="drag-handle cursor-move" x-text="allColumns[columnKey]"></span>
                            <div class="resizer"></div>
                        </th>
                    </template>
                    
                    <th class="px-2 py-1 text-center text-xs font-medium text-white uppercase tracking-wider border-l border-gray-400 no-drag">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="planning in plannings" :key="planning.id">
                    <tr :class="{
                        'flashing-row': planning.origen !== planning.destino && !planning.is_scale && planning.is_direct_route != true, 
                        'bg-blue-50': selectedPlannings.includes(planning.id)
                    }">
                        <td class="px-2 py-1 border text-center">
                            <input type="checkbox" :value="planning.id" x-model="selectedPlannings" class="rounded">
                        </td>

                        <template x-for="columnKey in columnOrder" :key="columnKey">
                            <td x-show="visibleColumns[columnKey]" 
                                class="px-2 py-1 whitespace-nowrap text-sm border truncate"
                                :title="getFormattedCell(planning, columnKey)">
                                
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
                                    <span x-text="getFormattedCell(planning, columnKey)"></span>
                                </template>
                            </td>
                        </template>

                        <td class="px-2 py-1 whitespace-nowrap text-center text-sm font-medium border">
                           <a :href="`/customer-service/planning/${planning.id}`" class="text-gray-600 hover:text-gray-900 mr-2" title="Ver Detalle"><i class="fas fa-eye"></i></a>
                            <a :href="`/customer-service/planning/${planning.id}/edit`" class="text-indigo-600 hover:text-indigo-900 mr-4" title="Editar"><i class="fas fa-edit"></i></a>
                            <template x-if="planning.origen !== planning.destino && !planning.is_scale && !planning.is_direct_route">
                                <span>
                                    <button @click="openScalesModal(planning)" class="px-3 py-1 bg-purple-600 text-white rounded-md text-xs font-semibold hover:bg-purple-700" title="Dividir en Escalas">Escalas</button>
                                    <button @click="markAsDirect(planning.id)" class="ml-2 px-3 py-1 bg-gray-500 text-white rounded-md text-xs font-semibold hover:bg-gray-600" title="Marcar como ruta directa">No Escala</button>
                                </span>
                            </template>
                            <template x-if="planning.status === 'En Espera'">
                                <form :action="`/customer-service/planning/${planning.id}/schedule`" method="POST" class="inline ml-2" onsubmit="return confirm('¿Programar esta ruta?');"> @csrf <button type="submit" class="px-3 py-1 bg-green-500 text-white rounded-md text-xs font-semibold hover:bg-green-600">Programar</button> </form>
                            </template>
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