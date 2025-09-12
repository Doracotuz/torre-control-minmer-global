@extends('layouts.audit-layout')

@section('content')
<div x-data="{ openFilters: false }" class="bg-gray-100 min-h-screen">
    
    <div class="bg-white shadow-md p-4 sticky top-0 z-10">
        <h1 class="text-xl font-bold text-center text-[#2c3856]">Dashboard de Auditoría</h1>
    </div>

    <div class="p-4">
        {{-- (La sección de filtros no necesita cambios) --}}
        @include('audit.partials.filters')

        {{-- --- INICIA NUEVA LÓGICA DE AGRUPACIÓN --- --}}
        @php
            // SECCIÓN 1: Órdenes pendientes de auditoría de almacén.
            $pendientesAlmacen = $auditableOrders->filter(
                fn($order) => $order->audit_status === 'Pendiente Almacén'
            );

            // SECCIÓN 2: Órdenes que terminaron almacén pero aún no tienen guía.
            $esperandoGuia = $auditableOrders->filter(
                fn($order) => $order->audit_status === 'Pendiente Patio' && !$order->plannings->first()?->guia
            );

            // SECCIÓN 3: Tareas agrupadas por guía para auditoría de patio y carga.
            $agrupadoPorGuia = $auditableOrders->filter(
                fn($order) => $order->plannings->first()?->guia
            )->groupBy(
                fn($order) => $order->plannings->first()->guia->id
            );
        @endphp
        {{-- --- TERMINA NUEVA LÓGICA DE AGRUPACIÓN --- --}}

        {{-- SECCIÓN 1: PENDIENTES DE ALMACÉN (POR ORDEN) --}}
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-700 mb-3"><i class="fas fa-warehouse text-blue-500 mr-2"></i>Pendientes de Almacén</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($pendientesAlmacen as $order)
                    @include('audit.partials.audit-card-rich', ['order' => $order])
                @empty
                    <p class="text-center text-gray-500 py-4 col-span-full">No hay tareas pendientes en esta sección.</p>
                @endforelse
            </div>
        </div>

        {{-- SECCIÓN 2: ESPERANDO ASIGNACIÓN DE GUÍA (POR ORDEN) --}}
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-700 mb-3"><i class="fas fa-clock text-orange-500 mr-2"></i>Esperando Asignación de Guía</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                 @forelse($esperandoGuia as $order)
                    @include('audit.partials.audit-card-rich', ['order' => $order])
                @empty
                    <p class="text-center text-gray-500 py-4 col-span-full">No hay órdenes en espera.</p>
                @endforelse
            </div>
        </div>
        
        {{-- SECCIÓN 3: AUDITORÍAS DE GUÍA (POR GUÍA) --}}
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-700 mb-3"><i class="fas fa-truck text-green-500 mr-2"></i>Auditorías de Guía (Patio y Carga)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                 @forelse($agrupadoPorGuia as $ordersInGuia)
                    @include('audit.partials.guia-card', ['ordersInGuia' => $ordersInGuia])
                @empty
                    <p class="text-center text-gray-500 py-4 col-span-full">No hay guías pendientes de auditoría.</p>
                @endforelse
            </div>
        </div>

        {{-- (Sección de Órdenes Terminadas y Paginación sin cambios) --}}
        @include('audit.partials.completed-orders')
    </div>
</div>
@endsection