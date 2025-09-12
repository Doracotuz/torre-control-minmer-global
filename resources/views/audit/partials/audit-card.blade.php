@php
    // Ahora accedemos a la orden a través del objeto $audit
    $order = $audit->order;
    $planning = $order->plannings->first();
    $guia = $planning?->guia;

    // Lógica para determinar el estatus de la auditoría
    $statusText = $audit->status;
    $statusColor = 'bg-gray-200 text-gray-800';
    if($audit->status === 'Pendiente Almacén') {
        $statusColor = 'bg-blue-100 text-blue-800';
    }
    if($audit->status === 'Pendiente Patio' && !$guia) {
        $statusText = 'Esperando Guía';
        $statusColor = 'bg-orange-100 text-orange-800';
    }
    
    // Lógica del botón (ahora pasa el objeto $audit a la ruta)
    $buttonClass = 'bg-gray-400 cursor-not-allowed';
    $buttonText = 'En Espera';
    $route = '#';
    $disabled = true;

    if ($audit->status === 'Pendiente Almacén') {
        $buttonClass = 'bg-blue-600 hover:bg-blue-700';
        $buttonText = 'Auditar Almacén';
        $route = route('audit.warehouse.show', $audit); // Se pasa el objeto $audit
        $disabled = false;
    }
@endphp

<div class="border rounded-lg shadow-sm flex flex-col justify-between bg-white h-full">
    <div class="p-4">
        <div class="flex justify-between items-start mb-2">
            <div>
                <p class="text-sm text-gray-500">SO</p>
                <p class="font-bold text-xl text-[#2c3856]">{{ $order->so_number }}</p>
            </div>
            <span class="px-2 py-1 text-xs font-semibold rounded-full text-center {{ $statusColor }}">{{ $statusText }}</span>
        </div>
        <p class="text-sm text-gray-600 truncate">{{ $order->customer_name }}</p>
    </div>

    <div class="px-4 pb-4 space-y-2 text-sm text-gray-700 border-t pt-3">
        <div class="flex items-center justify-between"><span class="flex items-center"><i class="fas fa-map-marker-alt w-5 text-center mr-2 text-gray-400"></i><strong>Ubicación:</strong></span><span>{{ $audit->location }}</span></div>
        <div class="flex items-center justify-between"><span class="flex items-center"><i class="fas fa-file-invoice w-5 text-center mr-2 text-gray-400"></i><strong>Factura:</strong></span><span>{{ $order->invoice_number ?? 'N/A' }}</span></div>
        <div class="flex items-center justify-between"><span class="flex items-center"><i class="fas fa-calendar-alt w-5 text-center mr-2 text-gray-400"></i><strong>F. Carga:</strong></span><span>{{ $planning?->fecha_carga ? \Carbon\Carbon::parse($planning->fecha_carga)->format('d/m/Y') : 'N/A' }}</span></div>
        <div class="flex items-center justify-between"><span class="flex items-center"><i class="fas fa-clock w-5 text-center mr-2 text-gray-400"></i><strong>H. Carga:</strong></span><span>{{ $planning?->hora_carga ? \Carbon\Carbon::parse($planning->hora_carga)->format('H:i') : 'N/A' }}</span></div>
        <div class="flex items-center justify-between"><span class="flex items-center"><i class="fas fa-barcode w-5 text-center mr-2 text-gray-400"></i><strong># SKUs:</strong></span><span>{{ $order->details->count() }}</span></div>
        <div class="flex items-center justify-between"><span class="flex items-center"><i class="fas fa-box-open w-5 text-center mr-2 text-gray-400"></i><strong>Total Piezas:</strong></span><span>{{ number_format($order->details->sum('quantity')) }}</span></div>
    </div>
    
    <a href="{{ $route }}" @if($disabled) onclick="event.preventDefault();" @endif class="block w-full text-center p-3 text-white font-bold rounded-b-lg transition-colors {{ $buttonClass }}">
        {{ $buttonText }}
    </a>
</div>