@php
    $planning = $order->plannings->first();
    $guia = $planning?->guia;
@endphp

<div class="border rounded-lg shadow-sm flex flex-col justify-between bg-gray-50">
    <div class="p-4">
        <div class="flex justify-between items-start mb-2">
            <div>
                <p class="text-sm text-gray-500">SO</p>
                <p class="font-bold text-lg text-gray-800">{{ $order->so_number }}</p>
            </div>
            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                Finalizada
            </span>
        </div>
        <p class="text-sm text-gray-600 truncate">{{ $order->customer_name }}</p>
    </div>

    <div class="px-4 pb-4 space-y-2 text-sm text-gray-700 border-t pt-3">
        <div class="flex items-center justify-between"><span class="flex items-center"><i class="fas fa-file-invoice w-5 text-center mr-2 text-gray-400"></i><strong>Factura:</strong></span><span>{{ $order->invoice_number ?? 'N/A' }}</span></div>
        <div class="flex items-center justify-between"><span class="flex items-center"><i class="fas fa-truck w-5 text-center mr-2 text-gray-400"></i><strong>Guía:</strong></span><span>{{ $guia->guia ?? 'No asignada' }}</span></div>
    </div>

    <form action="{{ route('audit.reopen', $order) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres reabrir esta auditoría? Volverá al estado inicial.');">
        @csrf
        <button type="submit" class="w-full text-center p-3 text-white font-bold rounded-b-lg bg-gray-600 hover:bg-gray-700 transition-colors">
            <i class="fas fa-edit mr-2"></i>Reabrir Auditoría
        </button>
    </form>
</div>