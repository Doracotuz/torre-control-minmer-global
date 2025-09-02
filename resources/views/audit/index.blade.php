@extends('layouts.audit-layout')

@section('content')
<div x-data="{ openFilters: false, activeSection: 'almacen' }" class="bg-gray-100 min-h-screen">
    
    <div class="bg-white shadow-md p-4 sticky top-0 z-10">
        <h1 class="text-xl font-bold text-center text-[#2c3856]">Dashboard de Auditoría</h1>
    </div>

    <div class="p-4">
        <div class="mb-4">
            <button @click="openFilters = !openFilters" class="w-full flex justify-between items-center px-4 py-3 bg-white rounded-lg shadow font-semibold text-gray-700">
                <span><i class="fas fa-filter mr-2"></i>Filtros de Búsqueda</span>
                <i class="fas" :class="openFilters ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
            <div x-show="openFilters" x-transition class="bg-white p-4 mt-2 rounded-lg shadow-lg">
                <form method="GET" action="{{ route('audit.index') }}" class="space-y-4">
                    <input type="text" name="search" placeholder="Buscar por SO, Factura o Guía..." value="{{ request('search') }}" class="w-full rounded-md border-gray-300 shadow-sm">
                    <select name="status" class="w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Todos los Estatus</option>
                        <option value="Pendiente" @selected(request('status') == 'Pendiente')>Pendiente</option>
                        <option value="En Planificación" @selected(request('status') == 'En Planificación')>En Planificación</option>
                        <option value="Planeada" @selected(request('status') == 'Planeada')>Guía Planeada</option>
                        <option value="En Cortina" @selected(request('status') == 'En Cortina')>Guía en Cortina</option>
                    </select>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Fecha de inicio</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full mt-1 rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Fecha de fin</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full mt-1 rounded-md border-gray-300 shadow-sm">
                    </div>
                    <button type="submit" class="w-full px-4 py-3 bg-[#2c3856] text-white rounded-lg font-bold">Aplicar Filtros</button>
                </form>
            </div>
        </div>

        @php
            $warehouseAudits = $auditableOrders->filter(function($order) {
                return in_array($order->status, ['Pendiente', 'En Planificación']) || !($order->plannings->first()->guia ?? null);
            });

            $groupedByGuia = $auditableOrders->filter(function($order) {
                return $order->plannings->first()->guia ?? null;
            })->groupBy(function($order) {
                return $order->plannings->first()->guia->id;
            });
        @endphp

        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-4">
            <button @click="activeSection = activeSection === 'almacen' ? '' : 'almacen'" class="w-full p-4 text-left font-bold text-lg flex justify-between items-center">
                <span><i class="fas fa-warehouse text-blue-500 mr-2"></i> Auditorías de Almacén</span>
                <span class="bg-blue-500 text-white text-sm rounded-full px-3 py-1">{{ $warehouseAudits->count() }}</span>
            </button>
            <div x-show="activeSection === 'almacen'" class="p-4 border-t space-y-4">
                @forelse($warehouseAudits as $order)
                    @php
                        $planning = $order->plannings->first();
                    @endphp
                    <div class="border rounded-lg shadow-sm">
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm text-gray-500">SO</p>
                                    <p class="font-bold text-xl text-[#2c3856]">{{ $order->so_number }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ $order->status }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">{{ $order->customer_name }}</p>
                            
                            <div class="mt-3 pt-3 border-t border-gray-200 space-y-2 text-sm text-gray-700">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-alt w-5 text-center mr-2 text-gray-400"></i>
                                    <strong>F. Carga:</strong>
                                    <span class="ml-2">{{ $planning?->fecha_carga?->format('d/m/Y') ?? 'No asignada' }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-file-alt w-5 text-center mr-2 text-gray-400"></i>
                                    <strong>Guía:</strong>
                                    <span class="ml-2">{{ $planning?->guia?->guia ?? 'Sin Asignar' }}</span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('audit.warehouse.show', $order) }}" class="block w-full text-center p-3 bg-blue-600 text-white font-bold rounded-b-lg hover:bg-blue-700">
                            Iniciar Auditoría
                        </a>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-4">No hay tareas pendientes.</p>
                @endforelse
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <button @click="activeSection = activeSection === 'guias' ? '' : 'guias'" class="w-full p-4 text-left font-bold text-lg flex justify-between items-center">
                <span><i class="fas fa-truck text-purple-500 mr-2"></i> Auditorías de Guía</span>
                <span class="bg-purple-500 text-white text-sm rounded-full px-3 py-1">{{ $groupedByGuia->count() }}</span>
            </button>
            <div x-show="activeSection === 'guias'" class="p-4 border-t space-y-4">
                @forelse($groupedByGuia as $guiaId => $ordersInGuia)
                    @php 
                        $guia = $ordersInGuia->first()->plannings->first()->guia;
                        $planning = $guia->plannings->first();
                    @endphp
                    <div class="border rounded-lg shadow-sm">
                        <div class="p-4">
                             <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm text-gray-500">Guía</p>
                                    <p class="font-bold text-xl text-[#2c3856]">{{ $guia->guia }}</p>
                                </div>
                                @if($guia->estatus == 'Planeada')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">{{ $guia->estatus }}</span>
                                @elseif($guia->estatus == 'En Cortina')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-teal-100 text-teal-800">{{ $guia->estatus }}</span>
                                @endif
                            </div>

                            <div class="mt-3 pt-3 border-t border-gray-200 space-y-2 text-sm text-gray-700">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-alt w-5 text-center mr-2 text-gray-400"></i>
                                    <strong>F. Carga:</strong>
                                    <span class="ml-2">{{ $planning?->fecha_carga?->format('d/m/Y') ?? 'No asignada' }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-user-circle w-5 text-center mr-2 text-gray-400"></i>
                                    <strong>Operador:</strong>
                                    <span class="ml-2">{{ $guia->operador ?? 'N/A' }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-id-card w-5 text-center mr-2 text-gray-400"></i>
                                    <strong>Placas:</strong>
                                    <span class="ml-2">{{ $guia->placas ?? 'N/A' }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-shield-alt w-5 text-center mr-2 text-gray-400"></i>
                                    <strong>Custodia:</strong>
                                    <span class="ml-2">{{ $guia->custodia ?? 'No' }}</span>
                                </div>
                            </div>
                            
                            <div class="mt-3 pt-3 border-t border-gray-200 text-xs text-gray-500">
                                <p><strong>Contiene SOs:</strong> {{ $ordersInGuia->pluck('so_number')->join(', ') }}</p>
                            </div>
                        </div>
                        @if($guia->estatus == 'Planeada')
                            <a href="{{ route('audit.patio.show', $guia) }}" class="block w-full text-center p-3 bg-purple-600 text-white font-bold rounded-b-lg hover:bg-purple-700">
                                Auditar Arribo (Patio)
                            </a>
                        @elseif($guia->estatus == 'En Cortina')
                            <a href="{{ route('audit.loading.show', $guia) }}" class="block w-full text-center p-3 bg-teal-600 text-white font-bold rounded-b-lg hover:bg-teal-700">
                                Auditar Carga
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-4">No hay tareas pendientes.</p>
                @endforelse
            </div>
        </div>

        <div class="mt-8">
            {{ $auditableOrders->links() }}
        </div>
    </div>
</div>
@endsection