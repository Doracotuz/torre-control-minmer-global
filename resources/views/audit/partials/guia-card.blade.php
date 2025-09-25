@php
    $guia = $auditsInGuia->first()->guia;

    $todasListasParaPatio = $auditsInGuia->every(fn($audit) => $audit->status === 'Pendiente Patio');
    $todasListasParaCarga = $auditsInGuia->every(fn($audit) => $audit->status === 'Pendiente Carga' || $audit->status === 'Finalizada');

    $buttonClass = 'bg-gray-400 cursor-not-allowed';
    $buttonText = 'Sincronizando Órdenes...';
    $route = '#';
    $disabled = true;

    if ($todasListasParaCarga) {
        $buttonClass = 'bg-purple-600 hover:bg-purple-700';
        $buttonText = 'Auditar Carga';
        $route = route('audit.loading.show', $auditsInGuia->first());
        $disabled = false;
    } elseif ($todasListasParaPatio) {
        $buttonClass = 'bg-orange-600 hover:bg-orange-700';
        $buttonText = 'Auditar Patio';
        $route = route('audit.patio.show', $auditsInGuia->first());
        $disabled = false;
    }

    $customerNames = $auditsInGuia->map(fn($audit) => $audit->order->customer_name)
                                  ->unique()
                                  ->implode(', ');
    
    $invoiceNumbers = $guia->facturas->pluck('numero_factura')->implode(', ');
@endphp

<div class="border rounded-lg shadow-sm flex flex-col justify-between bg-white h-full">
    <div class="p-4">
        <div class="flex justify-between items-start mb-2">
            <div>
                <p class="text-sm text-gray-500">Guía</p>
                <p class="font-bold text-xl text-[#2c3856]">{{ $guia->guia }}</p>
            </div>
            <span class="px-2 py-1 text-xs font-semibold rounded-full text-center {{ $todasListasParaCarga ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800' }}">
                {{ $todasListasParaCarga ? 'Pendiente Carga' : 'Pendiente Patio' }}
            </span>
        </div>
    </div>

    <div class="px-4 pb-4 space-y-2 text-sm text-gray-700 border-t pt-3">
        
        <div class="flex items-center justify-between">
            <span class="flex items-center"><i class="fas fa-user-tie w-5 text-center mr-2 text-gray-400"></i><strong>Operador:</strong></span>
            <span class="truncate">{{ $guia->operador ?? 'N/A' }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="flex items-center"><i class="fas fa-id-card w-5 text-center mr-2 text-gray-400"></i><strong>Placas:</strong></span>
            <span class="truncate">{{ $guia->placas ?? 'N/A' }}</span>
        </div>
        <div class="flex items-center justify-between" title="{{ $invoiceNumbers }}">
            <span class="flex items-center"><i class="fas fa-file-invoice-dollar w-5 text-center mr-2 text-gray-400"></i><strong>Facturas:</strong></span>
            <span class="truncate">{{ !empty($invoiceNumbers) ? $invoiceNumbers : 'N/A' }}</span>
        </div>

        <div class="flex items-center justify-between" title="{{ $customerNames }}">
            <span class="flex items-center"><i class="fas fa-users w-5 text-center mr-2 text-gray-400"></i><strong>Clientes:</strong></span>
            <span class="truncate">{{ $customerNames }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="flex items-center"><i class="fas fa-file-invoice w-5 text-center mr-2 text-gray-400"></i><strong>Órdenes (SOs):</strong></span>
            <span class="truncate">{{ $auditsInGuia->pluck('order.so_number')->join(', ') }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="flex items-center"><i class="fas fa-box-open w-5 text-center mr-2 text-gray-400"></i><strong>Total Piezas:</strong></span>
            <span>{{ number_format($auditsInGuia->pluck('order.details')->flatten()->sum('quantity')) }}</span>
        </div>
    </div>
    
    <a href="{{ $route }}" 
       @if($disabled) onclick="event.preventDefault();" @endif
       class="block w-full text-center p-3 text-white font-bold rounded-b-lg transition-colors {{ $buttonClass }}">
        {{ $buttonText }}
    </a>
</div>