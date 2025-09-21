<div>
    <div class="hidden md:block bg-white overflow-hidden shadow-xl sm:rounded-lg">
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
                                    :title="getFormattedCell(order, columnKey)">
                                    
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

                                    <template x-if="columnKey === 'is_oversized'">
                                        <span x-text="order.is_oversized ? 'Sí' : 'No'" 
                                            class="inline-block px-2 rounded-full"
                                            :class="order.is_oversized ? 'font-semibold text-yellow-800 bg-yellow-100' : 'text-gray-600'"></span>
                                    </template>

                                    <template x-if="!['status', 'is_oversized'].includes(columnKey)">
                                        <span class="block overflow-hidden text-ellipsis whitespace-nowrap" 
                                            x-html="getFormattedCell(order, columnKey)">
                                        </span>
                                    </template>
                                </td>
                            </template>

                            <td class="px-2 py-1 whitespace-nowrap text-center text-sm font-medium border border-gray-200">
                                <div class="flex items-center justify-center space-x-3">
                                    <a :href="`/customer-service/orders/${order.id}`" class="text-gray-600 hover:text-gray-900" title="Ver Detalle"><i class="fas fa-eye"></i></a>
                                    <template x-if="order.status !== 'Cancelado'">
                                        <span>
                                            <a :href="`/customer-service/orders/${order.id}/edit`" class="text-indigo-600 hover:text-indigo-900" title="Editar"><i class="fas fa-edit"></i></a>
                                            <form :action="`/customer-service/orders/${order.id}/cancel`" method="POST" class="inline ml-3" onsubmit="return confirm('¿Estás seguro de que deseas CANCELAR este pedido?');">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-900" title="Cancelar Pedido"><i class="fas fa-ban"></i></button>
                                            </form>
                                            <template x-if="order.status === 'Pendiente'">
                                                <form :action="`/customer-service/orders/${order.id}/plan`" method="POST" class="inline ml-3" onsubmit="return confirm('¿Marcar este pedido como LISTO para enviar a planificación?');">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 bg-green-500 text-white rounded-md text-xs font-semibold hover:bg-green-600" title="Marcar como Listo">Ok</button>
                                                </form>
                                            </template>
                                        </span>
                                    </template>
                                </div>
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
    <div class="block md:hidden space-y-4">
        <template x-for="order in orders" :key="order.id">
            <div class="bg-white rounded-lg shadow-md p-4 border"
                 :class="{ 'border-blue-500 ring-2 ring-blue-200': selectedOrders.includes(order.id) }">
                
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-start">
                        <input type="checkbox" :value="order.id" x-model="selectedOrders" class="rounded mt-1">
                        <div class="ml-3">
                            <p class="font-bold text-gray-800" x-text="order.customer_name"></p>
                            <p class="text-sm text-gray-500" x-text="`SO: ${order.so_number}`"></p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap"
                          :class="{
                            'bg-red-100 text-red-800': order.status === 'Cancelado',
                            'bg-[#2c3856] text-white': order.status === 'En Planificación',
                            'bg-gray-100 text-gray-800': !['Cancelado', 'En Planificación'].includes(order.status)
                          }" x-text="order.status">
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm border-t pt-3">
                    <div><strong class="block text-gray-500">Orden Compra:</strong><span x-text="order.purchase_order || 'N/A'"></span></div>
                    <div><strong class="block text-gray-500">Factura:</strong><span x-text="order.invoice_number || 'N/A'"></span></div>
                    <div><strong class="block text-gray-500">F. Creación:</strong><span x-text="getFormattedCell(order, 'creation_date')"></span></div>
                    <div><strong class="block text-gray-500">Almacén:</strong><span x-text="order.origin_warehouse"></span></div>
                    <div class="col-span-2"><strong class="block text-gray-500">Subtotal:</strong><span class="font-semibold text-green-700" x-text="getFormattedCell(order, 'subtotal')"></span></div>
                </div>

                <div class="flex items-center justify-end space-x-3 border-t mt-3 pt-3">
                    <a :href="`/customer-service/orders/${order.id}`" class="text-gray-500 hover:text-gray-800 p-1" title="Ver Detalle"><i class="fas fa-eye"></i></a>
                    <template x-if="order.status !== 'Cancelado'">
                        <span>
                            <a :href="`/customer-service/orders/${order.id}/edit`" class="text-indigo-500 hover:text-indigo-700 p-1" title="Editar"><i class="fas fa-edit"></i></a>
                            <form :action="`/customer-service/orders/${order.id}/cancel`" method="POST" class="inline ml-2" onsubmit="return confirm('¿Cancelar este pedido?');">
                                @csrf
                                <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Cancelar Pedido"><i class="fas fa-ban"></i></button>
                            </form>
                            <template x-if="order.status === 'Pendiente'">
                                <form :action="`/customer-service/orders/${order.id}/plan`" method="POST" class="inline ml-2" onsubmit="return confirm('¿Marcar como Listo?');">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-green-500 text-white rounded-md text-xs font-semibold hover:bg-green-600">Ok</button>
                                </form>
                            </template>
                        </span>
                    </template>
                </div>
            </div>
        </template>
         <template x-if="!isLoading && orders.length === 0">
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">No se encontraron pedidos.</div>
        </template>
    </div>
    </div>