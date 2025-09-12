{{-- resources/views/audit/partials/guia-card.blade.php --}}

@php
    $guia = $ordersInGuia->first()->plannings->first()->guia;

    // Determinamos el estatus general de la guía para la auditoría
    $esPendientePatio = $ordersInGuia->contains(fn($o) => $o->audit_status === 'Pendiente Patio');
    $esPendienteCarga = $ordersInGuia->contains(fn($o) => $o->audit_status === 'Pendiente Carga');
    
    // Regla de Sincronización: ¿Están todas las órdenes listas para el siguiente paso?
    $todasListasParaPatio = $ordersInGuia->every(fn($o) => $o->audit_status === 'Pendiente Patio');
    $todasListasParaCarga = $ordersInGuia->every(fn($o) => $o->audit_status === 'Pendiente Carga' || $o->audit_status === 'Finalizada');

    // Lógica para el botón de acción
    $buttonClass = 'bg-gray-400 cursor-not-allowed';
    $buttonText = 'Sincronizando Órdenes...';
    $route = '#';
    $disabled = true;

    if ($todasListasParaCarga) {
        $buttonClass = 'bg-purple-600 hover:bg-purple-700';
        $buttonText = 'Auditar Carga';
        $route = route('audit.loading.show', $guia);
        $disabled = false;
    } elseif ($todasListasParaPatio) {
        $buttonClass = 'bg-orange-600 hover:bg-orange-700';
        $buttonText = 'Auditar Patio';
        $route = route('audit.patio.show', $guia);
        $disabled = false;
    }
@endphp

<div class="border rounded-lg shadow-sm flex flex-col justify-between bg-white">
    <div class="p-4">
        <div class="flex justify-between items-start mb-2">
            <div>
                <p class="text-sm text-gray-500">Guía</p>
                <p class="font-bold text-xl text-[#2c3856]">{{ $guia->guia }}</p>
            </div>
            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $todasListasParaCarga ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800' }}">
                {{ $todasListasParaCarga ? 'Pendiente Carga' : 'Pendiente Patio' }}
            </span>
        </div>
        <p class="text-sm text-gray-600 truncate">Operador: {{ $guia->operador ?? 'N/A' }}</p>
    </div>

    <div class="px-4 pb-4 space-y-2 text-sm text-gray-700 border-t pt-3">
        <div class="flex items-center justify-between">
            <span class="flex items-center"><i class="fas fa-file-invoice w-5 text-center mr-2 text-gray-400"></i><strong>Órdenes (SOs):</strong></span>
            <span class="truncate">{{ $ordersInGuia->pluck('so_number')->join(', ') }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="flex items-center"><i class="fas fa-box-open w-5 text-center mr-2 text-gray-400"></i><strong>Total Piezas:</strong></span>
            <span>{{ number_format($ordersInGuia->pluck('details')->flatten()->sum('quantity')) }}</span>
        </div>
    </div>
    
    <a href="{{ $route }}" 
       @if($disabled) onclick="event.preventDefault();" @endif
       class="block w-full text-center p-3 text-white font-bold rounded-b-lg transition-colors {{ $buttonClass }}">
        {{ $buttonText }}
    </a>
</div>