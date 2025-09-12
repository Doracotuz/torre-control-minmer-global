@extends('layouts.audit-layout')

@section('content')
<div x-data="{ openFilters: false, completedOpen: false }" class="bg-gray-100 min-h-screen">
    
    <div class="bg-white shadow-md p-4 sticky top-0 z-10">
        <h1 class="text-xl font-bold text-center text-[#2c3856]">Dashboard de Auditoría</h1>
    </div>

    <div class="p-4">
        {{-- Incluimos los filtros que ya funcionan --}}
        @include('audit.partials.filters')

        {{-- --- LÓGICA RESTAURADA PARA 3 SECCIONES --- --}}
        @php
            // SECCIÓN 1: Auditorías pendientes de Almacén.
            $pendientesAlmacen = $audits->filter(
                fn($audit) => $audit->status === 'Pendiente Almacén'
            );

            // SECCIÓN 2: Auditorías que terminaron almacén pero aún no tienen guía asignada.
            $esperandoGuia = $audits->filter(
                fn($audit) => $audit->status === 'Pendiente Patio' && !$audit->guia
            );

            // SECCIÓN 3: Tareas agrupadas por guía para auditoría de patio y carga.
            $agrupadoPorGuia = $audits->filter(
                fn($audit) => $audit->guia && in_array($audit->status, ['Pendiente Patio', 'Pendiente Carga'])
            )->groupBy(
                fn($audit) => $audit->guia->id
            );
        @endphp
        {{-- --- FIN DE LA LÓGICA --- --}}

        {{-- SECCIÓN 1: PENDIENTES DE ALMACÉN --}}
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-700 mb-3"><i class="fas fa-warehouse text-blue-500 mr-2"></i>Pendientes de Almacén</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($pendientesAlmacen as $audit)
                    @include('audit.partials.audit-card', ['audit' => $audit])
                @empty
                    <p class="text-center text-gray-500 py-4 col-span-full">No hay tareas pendientes en esta sección.</p>
                @endforelse
            </div>
        </div>

        {{-- SECCIÓN 2: ESPERANDO ASIGNACIÓN DE GUÍA --}}
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-700 mb-3"><i class="fas fa-clock text-orange-500 mr-2"></i>Esperando Asignación de Guía</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                 @forelse($esperandoGuia as $audit)
                    @include('audit.partials.audit-card', ['audit' => $audit])
                @empty
                    <p class="text-center text-gray-500 py-4 col-span-full">No hay órdenes en espera.</p>
                @endforelse
            </div>
        </div>
        
        {{-- SECCIÓN 3: AUDITORÍAS DE GUÍA (POR GUÍA) --}}
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-700 mb-3"><i class="fas fa-truck text-green-500 mr-2"></i>Auditorías de Guía (Patio y Carga)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                 @forelse($agrupadoPorGuia as $auditsInGuia)
                    @include('audit.partials.guia-card', ['auditsInGuia' => $auditsInGuia])
                @empty
                    <p class="text-center text-gray-500 py-4 col-span-full">No hay guías pendientes de auditoría.</p>
                @endforelse
            </div>
        </div>

        {{-- Sección de Órdenes Terminadas (usa la variable $completedGuides del controlador) --}}
        @include('audit.partials.completed-orders', ['completedGuides' => $completedGuides])

        <div class="mt-8">
            {{ $audits->appends(request()->except('completedPage'))->links() }}
        </div>
    </div>
</div>
@endsection