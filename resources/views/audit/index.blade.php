@extends('layouts.audit-layout')

@section('content')
<div x-data="{ openFilters: false, completedOpen: false }" class="bg-gray-100 min-h-screen">
    
    <div class="bg-white shadow-md p-4 sticky top-0 z-10">
        <h1 class="text-xl font-bold text-center text-[#2c3856]">Dashboard de Auditoría</h1>
    </div>

    <div class="p-4">
        <div class="flex justify-between items-center mb-4">
            
            <a href="{{ route('audit.carga-plan.show') }}" class="bg-[#2c3856] text-white font-bold py-1 px-2 rounded hover:bg-indigo-700 transition duration-300 text-xs">
                <i class="fas fa-calendar-alt mr-2"></i>Ver Plan de Carga
            </a>
        </div>        
        @include('audit.partials.filters')

        @php
            $pendientesAlmacen = $audits->filter(
                fn($audit) => $audit->status === 'Pendiente Almacén'
            );

            $esperandoGuia = $audits->filter(
                fn($audit) => $audit->status === 'Pendiente Patio' && !$audit->guia
            );

            $agrupadoPorGuia = $audits->filter(
                fn($audit) => $audit->guia && in_array($audit->status, ['Pendiente Patio', 'Pendiente Carga'])
            )->groupBy(
                fn($audit) => $audit->guia->id
            );
        @endphp

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

        @include('audit.partials.completed-orders', ['completedGuides' => $completedGuides])

        <div class="mt-8">
            {{ $audits->appends(request()->except('completedPage'))->links() }}
        </div>
    </div>
</div>
@endsection