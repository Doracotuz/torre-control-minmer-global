@extends('layouts.audit-layout')
@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-[#2c3856] mb-4">Dashboard de Auditoría</h1>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <form method="GET" action="{{ route('audit.index') }}" class="flex flex-col sm:flex-row gap-4">
                <input type="date" name="start_date" value="{{ request('start_date', now()->format('Y-m-d')) }}" class="rounded-md border-gray-300 shadow-sm flex-grow">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="rounded-md border-gray-300 shadow-sm flex-grow">
                <button type="submit" class="px-4 py-2 bg-[#2c3856] text-white rounded-md font-semibold">Filtrar</button>
            </form>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold text-[#2c3856] mb-4">Cargas del Día</h2>
            <div class="space-y-3">
                @forelse($cargasDelDia as $guia)
                    @php
                        $order = $guia->plannings->first()->order ?? null;
                    @endphp
                    <div class="border rounded-lg p-4 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-gray-800">Guía: {{ $guia->guia }}</p>
                            <p class="text-sm text-gray-600">SO: {{ $order->so_number ?? 'N/A' }} | {{ $guia->operador }} - {{ $guia->placas }}</p>
                            <p class="text-sm font-semibold 
                                @if($order->status == 'Listo para Enviar') text-blue-600 @endif
                                @if($guia->estatus == 'En Cortina') text-purple-600 @endif
                            ">
                                Estatus: {{ $guia->estatus == 'Planeada' ? ($order->status ?? 'Pendiente') : $guia->estatus }}
                            </p>
                        </div>
                        <div>
                            @if($order && $order->status == 'Pendiente')
                                <a href="{{ route('audit.warehouse.show', $order->id) }}" class="px-3 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold">Auditar Almacén</a>
                            @elseif($order && $order->status == 'Listo para Enviar')
                                <a href="{{ route('audit.patio.show', $guia->id) }}" class="px-3 py-2 bg-purple-600 text-white rounded-md text-sm font-semibold">Auditar Patio</a>
                            @elseif($guia->estatus == 'En Cortina')
                                {{-- <a href="#" class="px-3 py-2 bg-teal-600 text-white rounded-md text-sm font-semibold">Auditar Carga</a> --}}
                                <span class="px-3 py-2 bg-gray-400 text-white rounded-md text-sm font-semibold cursor-not-allowed">Carga Pendiente</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500">No hay cargas programadas para la fecha seleccionada.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
