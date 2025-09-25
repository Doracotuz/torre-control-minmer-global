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


    @forelse ($guias as $guia)
        @php
            $totalCajasGuia = $guia->plannings->sum('cajas');
            $totalPiezasGuia = $guia->plannings->sum('pzs');
        @endphp
        <div x-data="{ open: true }" class="bg-white rounded-xl shadow-lg mb-6 overflow-hidden border border-gray-200">
            <div @click="open = !open" class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center cursor-pointer">
                <div>
                    <span class="text-xs text-gray-500">Guía</span>
                    <h2 class="text-xl font-extrabold text-[#2c3856]">{{ $guia->guia }}</h2>
                    <p class="font-semibold text-gray-600 text-sm">
                        <i class="far fa-clock mr-1"></i>
                        @php
                            try {
                                echo \Carbon\Carbon::parse($guia->hora_planeada)->format('h:i A');
                            } catch (\Exception $e) {
                                echo $guia->hora_planeada;
                            }
                        @endphp
                    </p>
                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-600 border-t pt-2">
                        <span title="Total de Cajas en la Guía">
                            <i class="fas fa-box-open mr-1 text-gray-400"></i>
                            <strong>Cajas:</strong> {{ $totalCajasGuia }}
                        </span>
                        <span title="Total de Piezas en la Guía">
                            <i class="fas fa-boxes mr-1 text-gray-400"></i>
                            <strong>Piezas:</strong> {{ $totalPiezasGuia }}
                        </span>
                    </div>

                </div>
                <i class="fas text-gray-500" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </div>
            
            <div x-show="open" x-transition class="transition-all duration-300">
                <div class="p-4 grid grid-cols-2 gap-4 text-center border-b border-gray-200">
                    <div>
                        <p class="text-xs text-gray-500">Capacidad</p>
                        <p class="font-bold text-gray-800"><i class="fas fa-truck-loading mr-1"></i>{{ $guia->plannings->first()->capacidad ?? 'N/A' }}</p>
                    </div>
                     <div>
                        <p class="text-xs text-gray-500">Custodia</p>
                        <p class="font-bold text-gray-800"><i class="fas fa-shield-alt mr-1"></i>{{ $guia->plannings->isNotEmpty() && $guia->plannings->first()->custodia ? 'Sí' : 'No' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Operador</p>
                        <p class="font-bold text-gray-800 truncate"><i class="fas fa-user mr-1"></i>{{ $guia->operador ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Placas</p>
                        <p class="font-bold text-gray-800"><i class="fas fa-truck-pickup mr-1"></i>{{ $guia->placas ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="p-2 space-y-3">
                    @foreach ($guia->plannings as $planning)
                        @if ($order = $planning->order)
                            @php
                                $audit = $order->audits->where('location', $planning->origen)->first();
                            @endphp
                            <div class="bg-white border border-gray-200 rounded-lg p-3">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-bold text-gray-800">{{ $order->so_number }}</p>
                                        <p class="text-xs text-gray-500">{{ $order->invoice_number }}</p>
                                        <p class="text-xs font-bold text-blue-600 mt-1">ORIGEN: {{ $planning->origen }}</p>
                                    </div>
                                    @if($audit)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full text-center
                                            @switch($audit->status)
                                                @case('Pendiente Almacén') bg-yellow-100 text-yellow-800 @break
                                                @case('Pendiente Patio') bg-orange-100 text-orange-800 @break
                                                @case('Pendiente Carga') bg-blue-100 text-blue-800 @break
                                                @case('Finalizada') bg-green-100 text-green-800 @break
                                                @default bg-gray-100 text-gray-800
                                            @endswitch">
                                            {{ $audit->status }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            Auditoría no creada
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="text-xs text-gray-600 border-t pt-2 mt-2">
                                    <p><i class="fas fa-user-tie fa-fw mr-2 text-gray-400"></i><span class="font-semibold">{{ $order->customer->name ?? 'N/A' }}</span></p>
                                    <p>
                                        <i class="fas fa-calendar-check fa-fw mr-2 text-gray-400"></i>
                                        Cita: {{ \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') }} -
                                        @if($order->schedule)
                                            @php
                                                try {
                                                    echo \Carbon\Carbon::parse($order->schedule)->format('h:i A');
                                                } catch (\Exception $e) {
                                                    echo $order->schedule; // Muestra el texto original si falla
                                                }
                                            @endphp
                                        @else
                                            Sin hora
                                        @endif
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-center text-sm mt-3">
                                    <div class="bg-gray-100 rounded p-1">
                                        <span class="font-bold">{{ $planning->cajas ?? 0 }}</span>
                                        <span class="text-xs text-gray-600">Cajas</span>
                                    </div>
                                    <div class="bg-gray-100 rounded p-1">
                                        <span class="font-bold">{{ $planning->pzs ?? 0 }}</span>
                                        <span class="text-xs text-gray-600">Piezas</span>
                                    </div>
                                </div>
                                
                                @if($audit)
                                    @php
                                        $route = '';
                                        if (in_array($audit->status, ['Pendiente Almacén', 'Pendiente Patio', 'Pendiente Carga'])) {
                                            $route = match($audit->status) {
                                                'Pendiente Almacén' => route('audit.warehouse.show', $audit),
                                                'Pendiente Patio'   => route('audit.patio.show', $audit),
                                                'Pendiente Carga'   => route('audit.loading.show', $audit),
                                            };
                                        }
                                    @endphp

                                    @if($route)
                                        <a href="{{ $route }}" class="block w-full text-center bg-indigo-600 text-white font-bold py-2 px-4 rounded-md hover:bg-indigo-700 transition mt-3 text-sm">
                                            Auditar en {{ $planning->origen }} <i class="fas fa-arrow-right ml-1"></i>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-16">
            <i class="fas fa-box-open fa-4x text-gray-400 mb-4"></i>
            <p class="text-gray-500">No hay guías programadas para esta fecha o filtro.</p>
        </div>
    @endforelse
</div>
@endsection

