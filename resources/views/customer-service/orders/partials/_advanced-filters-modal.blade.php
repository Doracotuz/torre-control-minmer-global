<div x-show="isAdvancedFilterModalOpen" @keydown.escape.window="isAdvancedFilterModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" style="display: none;">
    <div @click.outside="isAdvancedFilterModalOpen = false" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-3xl">
        <h3 class="text-xl font-bold text-[#2c3856] mb-6">Filtros Avanzados</h3>

        <div class="space-y-6 max-h-[60vh] overflow-y-auto pr-4">
            
            <fieldset class="border-t border-gray-200 pt-4">
                <legend class="text-lg font-semibold text-gray-700 px-2 -mt-7 bg-white w-auto">Datos del Pedido y Cliente</legend>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Orden de Compra</label>
                        <input type="text" x-model.debounce.300ms="filters.purchase_order_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">BT de OC</label>
                        <input type="text" x-model.debounce.300ms="filters.bt_oc" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Razón Social</label>
                        <input type="text" x-model.debounce.300ms="filters.customer_name_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Canal</label>
                        <select x-model="filters.channel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Todos</option>
                            <option value="Corporate">Corporate</option>
                            <option value="Especialista">Especialista</option>
                            <option value="Moderno">Moderno</option>
                            <option value="On">On</option>
                            <option value="On trade">On trade</option>
                            <option value="POSM">POSM</option>
                            <option value="Private">Private</option>
                        </select>
                    </div>
                </div>
            </fieldset>

            <fieldset class="border-t border-gray-200 pt-4">
                <legend class="text-lg font-semibold text-gray-700 px-2 -mt-7 bg-white w-auto">Datos de Facturación y Logística</legend>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nº Factura</label>
                        <input type="text" x-model.debounce.300ms="filters.invoice_number_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Factura</label>
                        <input type="date" x-model="filters.invoice_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Almacén Origen</label>
                        <input type="text" x-model.debounce.300ms="filters.origin_warehouse" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Localidad Destino</label>
                        <input type="text" x-model.debounce.300ms="filters.destination_locality" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Entrega</label>
                        <input type="date" x-model="filters.delivery_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ejecutivo</label>
                        <input type="text" x-model.debounce.300ms="filters.executive" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
            </fieldset>

            <fieldset class="border-t border-gray-200 pt-4">
                <legend class="text-lg font-semibold text-gray-700 px-2 -mt-7 bg-white w-auto">Fechas de Evidencia</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Recepción de Evidencia</label>
                        <input type="date" x-model="filters.evidence_reception_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Corte de Evidencias</label>
                        <input type="date" x-model="filters.evidence_cutoff_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
            </fieldset>

        </div>

        <div class="flex justify-end gap-4 mt-8">
            <button type="button" @click="clearAdvancedFilters()" class="px-4 py-2 bg-gray-200 rounded-md">Limpiar Filtros</button>
            <button type="button" @click="isAdvancedFilterModalOpen = false" class="px-4 py-2 bg-blue-600 text-white rounded-md">Aplicar y Cerrar</button>
        </div>
    </div>
</div>