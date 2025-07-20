<?php
// Archivo: resources/views/tms/partials/operator-details.blade.php
?>
<!-- Lista de Facturas -->
<div class="space-y-4">
    @foreach($shipments->pluck('invoices')->flatten() as $invoice)
    <div class="border rounded-lg p-4">
        <p class="font-semibold">{{ $invoice->invoice_number }}</p>
        <p class="text-sm text-gray-600">{{ $invoice->box_quantity }} cajas, {{ $invoice->bottle_quantity }} botellas</p>
        <p class="text-sm">Estatus: <strong>{{ $invoice->status }}</strong></p>

        @if($routeStatus == 'En transito' && $invoice->status == 'Pendiente')
        <div class="mt-4 flex space-x-2">
            <button @click="openModal('updateInvoice', {{ $invoice->id }}, 'Entregado')" class="flex-1 bg-green-500 text-white text-sm font-bold py-2 px-4 rounded-lg">Entregado</button>
            <button @click="openModal('updateInvoice', {{ $invoice->id }}, 'No entregado')" class="flex-1 bg-red-500 text-white text-sm font-bold py-2 px-4 rounded-lg">No Entregado</button>
        </div>
        @endif
    </div>
    @endforeach
</div>

<!-- ================================================================== -->
<!-- INICIO DE LA CORRECCIÓN: Botón para Registrar Evento -->
<!-- ================================================================== -->
@if($routeStatus == 'En transito')
<button @click="openModal('registerEvent')" class="mt-6 w-full border border-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-50 transition-colors">
    <i class="fas fa-flag mr-2"></i>Registrar Evento
</button>
@endif