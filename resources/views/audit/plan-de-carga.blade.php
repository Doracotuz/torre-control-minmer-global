@extends('layouts.audit-layout')

@section('content')
<div class="p-2 md:p-4 bg-gray-50 min-h-screen" x-data>
    
    <div class="bg-white shadow-md rounded-lg p-4 mb-5">
        <form action="{{ route('audit.carga-plan.show') }}" method="GET" class="space-y-4">
            <div class="flex flex-wrap justify-between items-center gap-4">
                <h1 class="text-xl font-bold text-[#2c3856]">
                    <i class="fas fa-calendar-alt mr-2"></i>Plan de Carga
                </h1>
                <a href="{{ route('audit.index') }}" class="bg-gray-600 text-white font-semibold py-1 px-2 rounded-lg hover:bg-gray-700 transition duration-300 text-xs">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Regresar
                </a>                
            </div>
            <div class="flex flex-col md:flex-row md:items-end md:gap-4 space-y-3 md:space-y-0">
                <div class="flex-1">
                    <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha</label>
                    <input type="date" id="fecha" name="fecha" value="{{ $selectedDate }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div class="flex-1">
                    <label for="origen" class="block text-sm font-medium text-gray-700">Origen</label>
                    <select name="origen" id="origen" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Todos los orígenes</option>
                        @foreach ($origenes as $origen)
                            <option value="{{ $origen }}" @selected($selectedOrigen == $origen)>{{ $origen }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full md:w-auto bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700 transition">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-3 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['totalGuias'] }}</p>
            <p class="text-xs text-gray-500 font-semibold">Guías a Embarcar</p>
        </div>
        <div class="bg-white p-3 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['totalDocumentos'] }}</p>
            <p class="text-xs text-gray-500 font-semibold">Documentos</p>
        </div>
        <div class="bg-white p-3 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['totalCajas']) }}</p>
            <p class="text-xs text-gray-500 font-semibold">Cajas Totales</p>
        </div>
        <div class="bg-white p-3 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['totalPiezas']) }}</p>
            <p class="text-xs text-gray-500 font-semibold">Piezas Totales</p>
        </div>
    </div>

    @forelse ($displayItems as $item)
        @if ($item->type === 'group')
            {{-- ============================================= --}}
            {{-- VISTA PARA GUÍAS AGRUPADAS (PATIO O CARGA)   --}}
            {{-- ============================================= --}}
            @php
                $guia = $item->guia;
                // Lógica para obtener las auditorías de forma más segura
                $auditsInGuia = $guia->plannings->flatMap(function ($planning) {
                    return $planning->order ? $planning->order->audits : null;
                })->filter()->unique('id');

                // Obtenemos la primera auditoría para usarla como referencia
                $firstAudit = $auditsInGuia->first();

                $buttonClass = 'bg-red-500 cursor-not-allowed';
                $buttonText = 'Error: Sin Auditorías';
                $route = '#';

                // SOLO si encontramos una auditoría válida, calculamos la ruta y el botón
                if ($firstAudit) {
                    $todasListasParaPatio = !$auditsInGuia->contains('status', 'Pendiente Almacén');
                    $todasListasParaCarga = $todasListasParaPatio && !$auditsInGuia->contains('status', 'Pendiente Patio');

                    if ($todasListasParaCarga) {
                        $buttonClass = 'bg-purple-600 hover:bg-purple-700';
                        $buttonText = 'Auditar Carga';
                        $route = route('audit.loading.show', $firstAudit);
                    } elseif ($todasListasParaPatio) {
                        $buttonClass = 'bg-orange-600 hover:bg-orange-700';
                        $buttonText = 'Auditar Patio';
                        $route = route('audit.patio.show', $firstAudit);
                    } else {
                        // Fallback por si la lógica del controlador falla, aunque no debería pasar
                        $buttonClass = 'bg-gray-400 cursor-not-allowed';
                        $buttonText = 'Sincronizando...';
                    }
                }
            @endphp
            <div class="bg-white rounded-xl shadow-lg mb-6 overflow-hidden border border-gray-200">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <span class="text-xs text-gray-500">Guía (Agrupada)</span>
                    <h2 class="text-xl font-extrabold text-[#2c3856]">{{ $guia->guia }}</h2>
                    <p class="font-semibold text-gray-600 text-sm">
                        <i class="far fa-clock mr-1"></i>
                        {{ \Carbon\Carbon::parse($guia->hora_planeada)->format('h:i A') }}
                    </p>
                    <div class="grid grid-cols-2 gap-x-4 mt-2 text-xs text-gray-600 border-t pt-2">
                        <span><i class="fas fa-box-open mr-1 text-gray-400"></i><strong>Cajas:</strong> {{ $guia->plannings->sum('cajas') }}</span>
                        <span><i class="fas fa-boxes mr-1 text-gray-400"></i><strong>Piezas:</strong> {{ $guia->plannings->sum('pzs') }}</span>
                        <span class="truncate col-span-2" title="{{ $guia->plannings->pluck('order.customer.name')->unique()->join(', ') }}"><i class="fas fa-users mr-1 text-gray-400"></i><strong>Clientes:</strong> {{ $guia->plannings->pluck('order.customer.name')->unique()->join(', ') }}</span>
                        <span class="truncate col-span-2" title="{{ $guia->plannings->pluck('order.so_number')->join(', ') }}"><i class="fas fa-file-invoice mr-1 text-gray-400"></i><strong>SOs:</strong> {{ $guia->plannings->pluck('order.so_number')->join(', ') }}</span>
                    </div>
                </div>
                <div class="p-4">
                    <a href="{{ $route }}" class="block w-full text-center text-white font-bold py-2 px-4 rounded-md transition {{ $buttonClass }}">
                        {{ $buttonText }} <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>

        @elseif ($item->type === 'individual')
            {{-- ================================================== --}}
            {{-- VISTA PARA ÓRDENES INDIVIDUALES (PEND. ALMACÉN)  --}}
            {{-- ================================================== --}}
            @php
                $planning = $item->planning;
                $order = $planning->order;
                $guia = $item->guia;
                $audit = $order->audits->where('location', $planning->origen)->first();
            @endphp
            <div class="bg-white border border-gray-200 rounded-lg p-3 mb-4 shadow">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="text-xs text-gray-500">Guía: {{ $guia->guia }}</p>
                        <p class="font-bold text-gray-800">{{ $order->so_number }}</p>
                        <p class="text-xs font-bold text-blue-600 mt-1">ORIGEN: {{ $planning->origen }}</p>
                    </div>
                    @if($audit)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 text-center">
                            {{ $audit->status }}
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Auditoría no creada</span>
                    @endif
                </div>
                <div class="text-xs text-gray-600 border-t pt-2 mt-2">
                    <p><i class="fas fa-user-tie fa-fw mr-2 text-gray-400"></i><span class="font-semibold">{{ $order->customer->name ?? 'N/A' }}</span></p>
                </div>
                <div class="grid grid-cols-2 gap-2 text-center text-sm mt-3">
                    <div class="bg-gray-100 rounded p-1"><span class="font-bold">{{ $planning->cajas ?? 0 }}</span> <span class="text-xs text-gray-600">Cajas</span></div>
                    <div class="bg-gray-100 rounded p-1"><span class="font-bold">{{ $planning->pzs ?? 0 }}</span> <span class="text-xs text-gray-600">Piezas</span></div>
                </div>
                @if($audit && $audit->status === 'Pendiente Almacén')
                    <a href="{{ route('audit.warehouse.show', $audit) }}" class="block w-full text-center bg-indigo-600 text-white font-bold py-2 px-4 rounded-md hover:bg-indigo-700 transition mt-3 text-sm">
                        Auditar en {{ $planning->origen }} <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                @endif
            </div>
        @endif
    @empty
        <div class="text-center py-16">
            <i class="fas fa-box-open fa-4x text-gray-400 mb-4"></i>
            <p class="text-gray-500">No hay guías programadas para esta fecha o filtro.</p>
        </div>
    @endforelse
</div>
@endsection