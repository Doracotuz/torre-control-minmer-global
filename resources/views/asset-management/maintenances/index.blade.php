@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary: #2c3856;
        --primary-light: #3d4d75;
        --accent: #ff9c00;
        --success: #10b981;
        --danger: #ef4444;
        --bg-color: #f3f4f6;
    }
    [x-cloak] { display: none !important; }
    
    .glass-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .status-pulse::before {
        content: '';
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: currentColor;
        margin-right: 6px;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(var(--color-rgb), 0.7); }
        70% { box-shadow: 0 0 0 6px rgba(var(--color-rgb), 0); }
        100% { box-shadow: 0 0 0 0 rgba(var(--color-rgb), 0); }
    }
</style>

<div class="min-h-screen bg-[#f3f4f6] pb-20" x-data="{ search: '' }">
    
    <div class="bg-[var(--primary)] pt-12 pb-24 px-4 sm:px-6 lg:px-8 rounded-b-[3rem] shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
            <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                <defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/></pattern></defs>
                <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>
        </div>

        <div class="max-w-7xl mx-auto relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 text-white">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Centro de Mantenimiento</h1>
                    <p class="mt-2 text-blue-200 text-sm md:text-base">Gestión avanzada del ciclo de vida y reparaciones de activos.</p>
                </div>
                <div class="mt-4 md:mt-0 flex gap-3">
                    <a href="{{ route('asset-management.dashboard') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-lg text-sm font-medium transition-all">
                        <i class="fas fa-arrow-left mr-2"></i> Dashboard
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/10 hover:transform hover:-translate-y-1 transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-blue-200 text-xs font-bold uppercase tracking-wider">Activos en Taller</p>
                            <h3 class="text-3xl font-bold text-white mt-1">
                                {{ $stats['active'] ?? $maintenances->whereNull('end_date')->count() }}
                            </h3>
                        </div>
                        <div class="p-3 bg-orange-500/20 rounded-xl">
                            <i class="fas fa-tools text-orange-400 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-blue-200">
                        <span class="text-orange-400 font-bold"><i class="fas fa-exclamation-circle"></i> Requieren atención</span>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/10 hover:transform hover:-translate-y-1 transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-blue-200 text-xs font-bold uppercase tracking-wider">Completados (Mes)</p>
                            <h3 class="text-3xl font-bold text-white mt-1">
                                {{ $stats['completed_month'] ?? 'N/A' }}
                            </h3>
                        </div>
                        <div class="p-3 bg-green-500/20 rounded-xl">
                            <i class="fas fa-check-circle text-green-400 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-blue-200">
                        Mantenimiento eficiente
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/10 hover:transform hover:-translate-y-1 transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-blue-200 text-xs font-bold uppercase tracking-wider">Costo Promedio</p>
                            <h3 class="text-3xl font-bold text-white mt-1">
                                ${{ number_format($stats['avg_cost'] ?? 0, 2) }}
                            </h3>
                        </div>
                        <div class="p-3 bg-blue-500/20 rounded-xl">
                            <i class="fas fa-dollar-sign text-blue-400 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-blue-200">
                        MXN por reparación
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 relative z-20">
        
        <div class="glass-panel rounded-2xl p-4 mb-6 shadow-lg flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="relative w-full md:w-96">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" placeholder="Buscar por activo, serie o proveedor..." class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-[var(--primary)] shadow-inner transition-all" x-model="search">
            </div>
            <div class="flex gap-2">
                <button class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-semibold transition-colors">
                    <i class="fas fa-filter mr-2"></i> Filtros
                </button>
                <button class="px-4 py-2 text-white bg-[var(--primary)] hover:bg-[var(--primary-light)] rounded-lg text-sm font-semibold shadow-lg hover:shadow-xl transition-all">
                    <i class="fas fa-file-download mr-2"></i> Exportar
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Activo</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado & Tipo</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tiempo Transcurrido</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Costo</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($maintenances as $maintenance)
                            <tr class="group hover:bg-blue-50/50 transition-colors duration-200 cursor-pointer" 
                                x-data="{ expanded: false }"
                                :class="{'bg-blue-50/30': expanded}">
                                
                                <td class="px-6 py-4" @click="expanded = !expanded">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-gray-100 flex items-center justify-center text-[var(--primary)] shadow-sm">
                                            @if($maintenance->asset->model->category->name == 'Laptop')
                                                <i class="fas fa-laptop"></i>
                                            @elseif($maintenance->asset->model->category->name == 'Celular')
                                                <i class="fas fa-mobile-alt"></i>
                                            @else
                                                <i class="fas fa-box"></i>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900">{{ $maintenance->asset->model->name }}</div>
                                            <div class="text-xs text-gray-500 font-mono bg-gray-100 px-2 py-0.5 rounded inline-block mt-1">
                                                {{ $maintenance->asset->asset_tag }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4" @click="expanded = !expanded">
                                    <div class="flex flex-col gap-2">
                                        <div>
                                            @if($maintenance->end_date)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                    <i class="fas fa-check mr-1.5"></i> Completado
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200 status-pulse" style="--color-rgb: 234, 179, 8">
                                                    En Taller
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 flex items-center">
                                            <i class="fas {{ $maintenance->type == 'Preventivo' ? 'fa-shield-alt text-blue-400' : 'fa-tools text-orange-400' }} mr-1.5"></i>
                                            {{ $maintenance->type }}
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 w-64" @click="expanded = !expanded">
                                    @php
                                        $start = \Carbon\Carbon::parse($maintenance->start_date);
                                        $end = $maintenance->end_date ? \Carbon\Carbon::parse($maintenance->end_date) : now();
                                        $days = $start->diffInDays($end);
                                        // Calcular porcentaje visual (tope 30 días para la barra)
                                        $percent = min(($days / 30) * 100, 100);
                                        $colorClass = $days > 15 ? 'bg-red-500' : ($days > 7 ? 'bg-yellow-500' : 'bg-blue-500');
                                    @endphp
                                    <div class="w-full">
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="font-semibold text-gray-700">
                                                {{ $start->format('d M') }}
                                            </span>
                                            <span class="font-bold {{ $days > 15 ? 'text-red-500' : 'text-gray-600' }}">
                                                {{ $days }} días
                                            </span>
                                        </div>
                                        <div class="overflow-hidden h-2 mb-0 text-xs flex rounded-full bg-gray-200">
                                            <div style="width:{{ $percent }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center {{ $colorClass }} transition-all duration-500"></div>
                                        </div>
                                        @if(!$maintenance->end_date && $days > 15)
                                            <p class="text-[10px] text-red-500 mt-1 font-semibold"><i class="fas fa-clock"></i> Retraso considerable</p>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4" @click="expanded = !expanded">
                                    @if($maintenance->cost)
                                        <span class="text-sm font-bold text-gray-900">$ {{ number_format($maintenance->cost, 2) }}</span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">--</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        {{-- Botón de "Toggle" Detalle (Visible solo en mobile o como helper) --}}
                                        <button @click.stop="expanded = !expanded" class="p-2 text-gray-400 hover:text-[var(--primary)] transition-colors rounded-full hover:bg-gray-100" title="Ver Detalles">
                                            <i class="fas fa-chevron-down transform transition-transform duration-200" :class="{'rotate-180': expanded}"></i>
                                        </button>

                                        <a href="{{ route('asset-management.maintenances.edit', $maintenance) }}" 
                                           class="p-2 text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-lg transition-all" 
                                           title="Editar / Subir Fotos">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @if($maintenance->end_date)
                                            <a href="{{ route('asset-management.maintenances.pdf', $maintenance) }}" 
                                               target="_blank" 
                                               class="p-2 text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-all" 
                                               title="Certificado PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <tr x-show="expanded" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" class="bg-gray-50/50">
                                <td colspan="5" class="px-6 py-0">
                                    <div class="p-6 border-l-4 border-[var(--primary)] ml-2 my-2 bg-white rounded-r-lg shadow-inner">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                            <div class="col-span-2">
                                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Diagnóstico / Motivo</h4>
                                                <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-3 rounded-lg border border-gray-100">
                                                    {{ $maintenance->diagnosis }}
                                                </p>
                                                
                                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mt-4 mb-2">Acciones Realizadas</h4>
                                                <p class="text-sm text-gray-600">
                                                    {{ $maintenance->actions_taken ?? 'No registrado aún...' }}
                                                </p>
                                            </div>

                                            <div>
                                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Detalles Técnicos</h4>
                                                <ul class="text-sm space-y-2 mb-4">
                                                    <li class="flex justify-between border-b border-gray-100 pb-1">
                                                        <span class="text-gray-500">Proveedor:</span>
                                                        <span class="font-medium text-gray-900">{{ $maintenance->supplier ?? 'Interno' }}</span>
                                                    </li>
                                                    @if($maintenance->substitute_asset_id)
                                                    <li class="flex justify-between border-b border-gray-100 pb-1">
                                                        <span class="text-gray-500">Equipo Sustituto:</span>
                                                        <a href="{{ route('asset-management.assets.show', $maintenance->substituteAsset) }}" class="text-blue-600 hover:underline">
                                                            {{ $maintenance->substituteAsset->asset_tag }}
                                                        </a>
                                                    </li>
                                                    @endif
                                                    <li class="flex justify-between border-b border-gray-100 pb-1">
                                                        <span class="text-gray-500">Inicio:</span>
                                                        <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($maintenance->start_date)->format('d/m/Y') }}</span>
                                                    </li>
                                                </ul>

                                                @php
                                                    $photoCount = ($maintenance->photo_1_path ? 1 : 0) + ($maintenance->photo_2_path ? 1 : 0) + ($maintenance->photo_3_path ? 1 : 0);
                                                @endphp
                                                @if($photoCount > 0)
                                                    <div class="flex items-center gap-2 mt-2">
                                                        <span class="text-xs font-semibold text-gray-500">Evidencias:</span>
                                                        <div class="flex -space-x-2">
                                                            @for($i=1; $i<=$photoCount; $i++)
                                                                <div class="w-6 h-6 rounded-full bg-gray-300 border-2 border-white flex items-center justify-center text-[8px] text-gray-600">
                                                                    <i class="fas fa-camera"></i>
                                                                </div>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-clipboard-check text-gray-400 text-2xl"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900">Sin mantenimientos registrados</h3>
                                        <p class="text-gray-500 mt-1">No se encontraron registros que coincidan con tu búsqueda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {!! $maintenances->links() !!}
            </div>
        </div>
    </div>
</div>
@endsection