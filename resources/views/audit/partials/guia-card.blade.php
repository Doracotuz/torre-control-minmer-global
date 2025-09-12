@php
    // La variable que recibimos es $auditsInGuia.
    // Accedemos a la guía a través de la primera auditoría del grupo.
    $guia = $auditsInGuia->first()->guia;

    // Regla de Sincronización: ¿Están todas las auditorías listas para el siguiente paso?
    // Ahora revisamos el campo 'status' del modelo Audit.
    $todasListasParaPatio = $auditsInGuia->every(fn($audit) => $audit->status === 'Pendiente Patio');
    $todasListasParaCarga = $auditsInGuia->every(fn($audit) => $audit->status === 'Pendiente Carga' || $audit->status === 'Finalizada');

    // Lógica para el botón de acción
    $buttonClass = 'bg-gray-400 cursor-not-allowed';
    $buttonText = 'Sincronizando Órdenes...';
    $route = '#';
    $disabled = true;

    if ($todasListasParaCarga) {
        $buttonClass = 'bg-purple-600 hover:bg-purple-700';
        $buttonText = 'Auditar Carga';
        // La ruta ahora recibe el objeto de la primera auditoría del grupo
        $route = route('audit.loading.show', $auditsInGuia->first());
        $disabled = false;
    } elseif ($todasListasParaPatio) {
        $buttonClass = 'bg-orange-600 hover:bg-orange-700';
        $buttonText = 'Auditar Patio';
        $route = route('audit.patio.show', $auditsInGuia->first());
        $disabled = false;
    }
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
        <p class="text-sm text-gray-600 truncate">Operador: {{ $guia->operador ?? 'N/A' }}</p>
    </div>

    <div class="px-4 pb-4 space-y-2 text-sm text-gray-700 border-t pt-3">
        <div class="flex items-center justify-between">
            <span class="flex items-center"><i class="fas fa-file-invoice w-5 text-center mr-2 text-gray-400"></i><strong>Órdenes (SOs):</strong></span>
            {{-- Accedemos a los datos a través de la relación anidada audit->order->so_number --}}
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