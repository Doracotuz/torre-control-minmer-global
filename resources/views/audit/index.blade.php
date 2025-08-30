@extends('layouts.audit-layout')
@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-[#2c3856] mb-4">Dashboard de Auditoría</h1>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <!-- Filtros -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <form method="GET" action="{{ route('audit.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-4">
                    <label for="search" class="block text-sm font-medium text-gray-700">Buscar SO o Factura</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Escribe aquí..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Estatus</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Todos</option>
                        <option value="Pendiente" @selected(request('status') == 'Pendiente')>Pendiente</option>
                        <option value="En Planificación" @selected(request('status') == 'En Planificación')>En Planificación</option>
                        <option value="Listo para Enviar" @selected(request('status') == 'Listo para Enviar')>Listo para Enviar</option>
                        <option value="En Cortina" @selected(request('status') == 'En Cortina')>En Cortina</option>
                    </select>
                </div>
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Fecha Desde</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date', now()->subWeek()->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">Fecha Hasta</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-[#2c3856] text-white rounded-md font-semibold h-10">Filtrar</button>
            </form>
        </div>

        <!-- Lista de Cargas -->
        <div class="space-y-6">
            @forelse($auditableOrders as $order)
                @php
                    $planning = $order->plannings->first();
                    $guia = $planning->guia ?? null;
                    $estatusGeneral = $order->status;
                    if ($guia && !in_array($guia->estatus, ['Planeada', 'En Espera'])) { $estatusGeneral = $guia->estatus; }
                    $estatusColor = 'bg-gray-500';
                    if (in_array($estatusGeneral, ['Pendiente', 'En Planificación'])) $estatusColor = 'bg-yellow-500';
                    if ($estatusGeneral == 'Listo para Enviar') $estatusColor = 'bg-blue-600';
                    if ($estatusGeneral == 'En Cortina') $estatusColor = 'bg-purple-600';
                @endphp
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="p-5">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-500">SO:</p>
                                <p class="font-bold text-xl text-gray-800">{{ $order->so_number }}</p>
                            </div>
                            <span class="px-3 py-1 text-sm font-semibold rounded-full text-white {{ $estatusColor }}">{{ $estatusGeneral }}</span>
                        </div>
                        <div class="mt-4 border-t pt-4">
                            <p class="text-sm font-semibold text-[#2c3856]">Detalles de la Carga:</p>
                            <div class="grid grid-cols-2 gap-4 mt-2 text-sm">
                                <div><p class="text-gray-500">Cliente:</p><p class="font-medium text-gray-800">{{ $order->customer_name ?? 'N/A' }}</p></div>
                                <div><p class="text-gray-500">Guía:</p><p class="font-medium text-gray-800">{{ $guia->guia ?? 'Sin Asignar' }}</p></div>
                                <div><p class="text-gray-500">Total SKUs:</p><p class="font-medium text-gray-800">{{ $order->details->count() }}</p></div>
                                <div><p class="text-gray-500">Total Botellas:</p><p class="font-medium text-gray-800">{{ $order->total_bottles }}</p></div>
                                <div class="col-span-2"><p class="text-gray-500">Fecha de Carga Programada:</p><p class="font-medium text-gray-800">{{ $planning && $planning->fecha_carga ? $planning->fecha_carga->format('d/m/Y') : 'No definida' }}</p></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-5 py-3">
                        @if(in_array($order->status, ['Pendiente', 'En Planificación']))
                            <a href="{{ route('audit.warehouse.show', $order->id) }}" class="block w-full text-center px-4 py-3 bg-blue-600 text-white rounded-lg font-bold shadow-lg hover:bg-blue-700 transition-colors"><i class="fas fa-warehouse mr-2"></i> Iniciar Auditoría de Almacén</a>
                        @elseif($order->status == 'Listo para Enviar' && $guia)
                            <a href="{{ route('audit.patio.show', $guia->id) }}" class="block w-full text-center px-4 py-3 bg-purple-600 text-white rounded-lg font-bold shadow-lg hover:bg-purple-700 transition-colors"><i class="fas fa-truck mr-2"></i> Iniciar Auditoría de Patio</a>
                        @elseif($guia && $guia->estatus == 'En Cortina')
                            <a href="{{ route('audit.loading.show', $guia->id) }}" class="block w-full text-center px-4 py-3 bg-teal-600 text-white rounded-lg font-bold shadow-lg hover:bg-teal-700 transition-colors"><i class="fas fa-box-open mr-2"></i> Iniciar Auditoría de Carga</a>
                        @else
                            <div class="text-center text-sm text-gray-500 py-2">
                                @if($order->status == 'Listo para Enviar' && !$guia)
                                    <i class="fas fa-clock mr-2"></i> Esperando asignación de Guía en Logística.
                                @else
                                    Esperando siguiente fase...
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white p-6 rounded-2xl shadow-lg text-center text-gray-500">
                    <p>No hay cargas que requieran auditoría para los filtros aplicados.</p>
                </div>
            @endforelse
        </div>

        <!-- Paginación -->
        <div class="mt-8">
            {{ $auditableOrders->links() }}
        </div>
    </div>
@endsection
