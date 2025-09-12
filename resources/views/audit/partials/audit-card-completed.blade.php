@php
    $orders = $guia->plannings->pluck('order')->filter();
@endphp

<div class="border rounded-lg shadow-sm flex flex-col justify-between bg-gray-50 h-full">
    <div class="p-4">
        <div class="flex justify-between items-start mb-2">
            <div>
                <p class="text-sm text-gray-500">Guía</p>
                <p class="font-bold text-lg text-gray-800">{{ $guia->guia }}</p>
            </div>
            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                Finalizada
            </span>
        </div>
        <p class="text-sm text-gray-600 truncate">
            SOs: {{ $orders->pluck('so_number')->unique()->join(', ') }}
        </p>
    </div>

    <div class="px-4 pb-4 space-y-2 text-sm text-gray-700 border-t pt-3">
        <div class="flex items-center justify-between">
            <span class="flex items-center"><i class="fas fa-users w-5 text-center mr-2 text-gray-400"></i><strong>Clientes:</strong></span>
            <span class="truncate">{{ $orders->pluck('customer_name')->unique()->join(', ') }}</span>
        </div>
    </div>

    {{-- CORRECCIÓN: El formulario ahora envía la guía a la ruta de 'reopen' --}}
    <form action="{{ route('audit.reopen', $guia) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres reabrir la auditoría para esta guía? Todas las órdenes volverán al estado inicial.');">
        @csrf
        <button type="submit" class="w-full text-center p-3 text-white font-bold rounded-b-lg bg-gray-600 hover:bg-gray-700 transition-colors">
            <i class="fas fa-edit mr-2"></i>Reabrir Auditoría
        </button>
    </form>
</div>