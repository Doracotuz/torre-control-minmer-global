<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="overflow-x-auto">
        <table class="w-full table-fixed">
            <thead class="bg-[#2c3856]">
                <tr>
                    <th class="px-2 py-1 text-center text-xs font-medium text-white uppercase tracking-wider w-12">
                        <input type="checkbox" @click="$dispatch('toggle-all-orders', $event.target.checked)" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </th>

                    <template x-for="(columnKey, index) in columnOrder" :key="`${columnKey}-${index}`">
                        <th x-show="visibleColumns[columnKey]"
                            :data-column="columnKey"
                            :style="columnWidths[columnKey] ? { width: columnWidths[columnKey] } : {}"
                            class="px-2 py-1 text-center text-xs font-medium text-white uppercase tracking-wider select-none border-l border-gray-200">
                            <span class="drag-handle" x-text="allColumns[columnKey]"></span>
                            <div class="resizer no-drag"></div>
                        </th>
                    </template>
                    <th class="px-2 py-1 text-center text-xs font-medium text-white uppercase tracking-wider no-drag border-l border-gray-200 actions-column">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white">
                <template x-for="order in orders" :key="order.id">
                    <tr class="hover:bg-gray-100" :class="{'bg-blue-50': selectedOrders.includes(order.id)}">
                        <td class="px-2 py-1 border text-center border-gray-200">
                            <input type="checkbox" :value="order.id" x-model="selectedOrders" class="rounded border-gray-300 text-indigo-600 shadow-sm">
                        </td>

                        <template x-for="columnKey in columnOrder" :key="columnKey">
                            <td x-show="visibleColumns[columnKey]" 
                                class="px-2 py-1 text-sm text-gray-500 border border-gray-200"
                                :title="order[columnKey]">
                                
                                <template x-if="columnKey === 'status'">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="{
                                            'bg-red-100 text-red-800': order.status === 'Cancelado',
                                            'bg-[#2c3856] text-white': order.status === 'En Planificación',
                                            'bg-gray-100 text-gray-800': !['Cancelado', 'En Planificación'].includes(order.status)
                                        }"
                                        x-text="order.status">
                                    </span>
                                </template>
                                <template x-if="columnKey !== 'status'">
                                    <span class="block overflow-hidden text-ellipsis whitespace-nowrap" 
                                          x-text="getFormattedCell(order, columnKey)">
                                    </span>
                                </template>
                            </td>
                        </template>

                        <td class="px-2 py-1 whitespace-nowrap text-center text-sm font-medium border border-gray-200">

                            <a :href="`/customer-service/orders/${order.id}`" class="text-gray-600 hover:text-gray-900" title="Ver Detalle"><i class="fas fa-eye"></i></a>
                            <template x-if="order.status !== 'Cancelado'">
                                <span class="ml-4">
                                    <a :href="`/customer-service/orders/${order.id}/edit`" class="text-indigo-600 hover:text-indigo-900" title="Editar"><i class="fas fa-edit"></i></a>
                                    <form :action="`/customer-service/orders/${order.id}/cancel`" method="POST" class="inline ml-4" onsubmit="return confirm('¿Estás seguro de que deseas CANCELAR este pedido?');">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Cancelar Pedido"><i class="fas fa-ban"></i></button>
                                    </form>
                                    <template x-if="order.status === 'Pendiente'">
                                        <form :action="`/customer-service/orders/${order.id}/plan`" method="POST" class="inline ml-4" onsubmit="return confirm('¿Marcar este pedido como LISTO para enviar a planificación?');">
                                            @csrf
                                            <button type="submit" class="px-1 py-1 bg-green-500 text-white rounded-md text-xs font-semibold hover:bg-green-600" title="Marcar como Listo">Ok</button>
                                        </form>
                                    </template>
                                </span>
                            </template>
                        </td>
                    </tr>
                </template>
                <template x-if="!isLoading && orders.length === 0">
                    <tr>
                        <td :colspan="Object.values(visibleColumns).filter(v => v).length + 2" class="px-2 py-4 text-center text-gray-500 border border-gray-200">
                            No se encontraron pedidos con los filtros aplicados.
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>