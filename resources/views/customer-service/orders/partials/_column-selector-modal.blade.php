<div x-show="isColumnModalOpen" @keydown.escape.window="isColumnModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" style="display: none;">
    <div @click.outside="isColumnModalOpen = false" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-2xl">
        <h3 class="text-xl font-bold text-[#2c3856] mb-4">Seleccionar Columnas Visibles</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.purchase_order" class="rounded"> <span class="ml-2">Orden de Compra</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.bt_oc" class="rounded"> <span class="ml-2">BT de OC</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.creation_date" class="rounded"> <span class="ml-2">F. Creación</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.so_number" class="rounded"> <span class="ml-2">SO</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.authorization_date" class="rounded"> <span class="ml-2">F. Autorización</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.invoice_number" class="rounded"> <span class="ml-2">Factura</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.invoice_date" class="rounded"> <span class="ml-2">F. Factura</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.customer_name" class="rounded"> <span class="ml-2">Razón Social</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.origin_warehouse" class="rounded"> <span class="ml-2">Almacén Origen</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.total_bottles" class="rounded"> <span class="ml-2">No. Botellas</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.total_boxes" class="rounded"> <span class="ml-2">No. Cajas</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.subtotal" class="rounded"> <span class="ml-2">Subtotal</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.channel" class="rounded"> <span class="ml-2">Canal</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.delivery_date" class="rounded"> <span class="ml-2">F. Entrega</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.schedule" class="rounded"> <span class="ml-2">Horario</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.client_contact" class="rounded"> <span class="ml-2">Cliente</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.shipping_address" class="rounded"> <span class="ml-2">Dirección</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.destination_locality" class="rounded"> <span class="ml-2">Localidad Destino</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.executive" class="rounded"> <span class="ml-2">Ejecutivo</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.status" class="rounded"> <span class="ml-2">Estatus</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.observations" class="rounded"> <span class="ml-2">Observaciones</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.evidence_reception_date" class="rounded"> <span class="ml-2">Recepción Evidencia</span></label></div>
            <div><label class="inline-flex items-center"><input type="checkbox" x-model="visibleColumns.evidence_cutoff_date" class="rounded"> <span class="ml-2">Corte Evidencias</span></label></div>
        </div>
        <div class="mt-6 flex justify-end">
            <button type="button" @click="isColumnModalOpen = false" class="px-4 py-2 bg-blue-600 text-white rounded-md">Cerrar</button>
        </div>
    </div>
</div>