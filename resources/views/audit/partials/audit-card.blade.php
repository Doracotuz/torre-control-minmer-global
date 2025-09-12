@php
    $planning = $order->plannings->first();
    $guia = $planning?->guia;
@endphp
<div class="border rounded-lg shadow-sm flex flex-col justify-between bg-white">
    {{-- SECCIÓN SUPERIOR: DATOS PRINCIPALES --}}
    <div class="p-4">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500">SO</p>
                <p class="font-bold text-xl text-[#2c3856]">{{ $order->so_number }}</p>
            </div>
            {{-- La guía solo se muestra si ya está asignada --}}
            @if($guia)
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-800" title="Guía Asignada">
                    <i class="fas fa-truck mr-1"></i>{{ $guia->guia }}
                </span>
            @endif
        </div>
        <p class="text-sm text-gray-600 mt-2">{{ $order->customer_name }}</p>
    </div>

    {{-- --- INICIA SECCIÓN DE DETALLES AÑADIDA --- --}}
    <div class="px-4 pb-4 space-y-2 text-sm text-gray-700 border-t pt-3">
        <div class="flex items-center">
            <i class="fas fa-calendar-alt w-5 text-center mr-2 text-gray-400"></i>
            <strong>F. Carga:</strong>
            <span class="ml-2">{{ $planning?->fecha_carga ? \Carbon\Carbon::parse($planning->fecha_carga)->format('d/m/Y') : 'No asignada' }}</span>
        </div>
        <div class="flex items-center">
            <i class="fas fa-barcode w-5 text-center mr-2 text-gray-400"></i>
            <strong># SKUs:</strong>
            <span class="ml-2">{{ $order->details->count() }}</span>
        </div>
        <div class="flex items-center">
            <i class="fas fa-box-open w-5 text-center mr-2 text-gray-400"></i>
            {{-- Asumimos que "botellas" se refiere al total de piezas de la orden --}}
            <strong>Total Piezas:</strong>
            <span class="ml-2">{{ number_format($order->details->sum('quantity')) }}</span>
        </div>
    </div>
    {{-- --- TERMINA SECCIÓN DE DETALLES AÑADIDA --- --}}

    {{-- SECCIÓN INFERIOR: BOTÓN DE ACCIÓN --}}
    @php
        $buttonClass = 'bg-gray-400 cursor-not-allowed';
        $buttonText = 'Esperando Guía';
        $route = '#';
        $disabled = true;

        switch ($order->audit_status) {
            case 'Pendiente Almacén':
                $buttonClass = 'bg-blue-600 hover:bg-blue-700';
                $buttonText = 'Auditar Almacén';
                $route = route('audit.warehouse.show', $order);
                $disabled = false;
                break;
            case 'Pendiente Patio':
                if ($guia) {
                    $buttonClass = 'bg-orange-600 hover:bg-orange-700';
                    $buttonText = 'Auditar Patio';
                    $route = route('audit.patio.show', $guia);
                    $disabled = false;
                }
                break;
            case 'Pendiente Carga':
                if ($guia) {
                    $buttonClass = 'bg-purple-600 hover:bg-purple-700';
                    $buttonText = 'Auditar Carga';
                    $route = route('audit.loading.show', $guia);
                    $disabled = false;
                }
                break;
        }
    @endphp

    <a href="{{ $route }}" 
       @if($disabled) onclick="event.preventDefault();" @endif
       class="block w-full text-center p-3 text-white font-bold rounded-b-lg transition-colors {{ $buttonClass }}">
        {{ $buttonText }}
    </a>
</div>