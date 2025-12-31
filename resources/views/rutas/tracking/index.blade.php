@extends('layouts.guest-rutas')

@section('content')
<style>
    @keyframes pulse-ring {
        0% { transform: scale(0.33); opacity: 1; }
        80%, 100% { opacity: 0; }
    }
    @keyframes pulse-dot {
        0% { transform: scale(0.8); }
        50% { transform: scale(1); }
        100% { transform: scale(0.8); }
    }
    .animate-ring::before {
        content: '';
        position: absolute;
        left: 0; top: 0;
        display: block;
        width: 100%; height: 100%;
        background-color: rgba(255, 156, 0, 0.6);
        border-radius: 50%;
        animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
    .glass-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .pattern-grid {
        background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
        background-size: 24px 24px;
    }
</style>

<div class="min-h-screen bg-slate-50 pattern-grid pb-20">
    
    <div class="relative bg-[#2c3856] pb-24 overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
        <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
        
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-12">
            <div class="text-center relative z-10">
                <span class="inline-block py-1 px-3 rounded-full bg-white/10 border border-white/20 text-blue-200 text-xs font-mono tracking-widest uppercase mb-4 backdrop-blur-sm">
                    Sistema de Rastreo en Linea
                </span>
                <h2 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-2">
                    Centro de <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-400">Control</span>
                </h2>
                <p class="text-blue-200/80 text-sm md:text-base max-w-xl mx-auto font-light">
                    Monitoreo en tiempo real de la cadena de suministro y distribución.
                </p>
            </div>

            <div class="mt-10 max-w-2xl mx-auto">
                <form method="GET" action="{{ route('tracking.index') }}" class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-[#ff9c00] to-orange-600 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative flex items-center bg-white rounded-xl shadow-2xl p-2">
                        <div class="pl-4 text-gray-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input 
                            id="facturas" 
                            class="block w-full border-none focus:ring-0 text-gray-800 placeholder-gray-400 text-lg py-3 px-4 bg-transparent font-medium" 
                            type="text" 
                            name="facturas" 
                            value="{{ $searchQuery ?? '' }}" 
                            required 
                            placeholder="Ingrese ID de Factura u Orden..." 
                        />
                        <button type="submit" class="bg-[#2c3856] text-white px-8 py-3 rounded-lg font-bold text-sm hover:bg-[#1e2742] transition-all shadow-lg transform active:scale-95 flex items-center gap-2">
                            BUSCAR
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-20">
        
        @if($searchQuery)
            @if(!empty($notFoundNumbers))
                <div class="mb-8 bg-white/90 backdrop-blur border-l-4 border-red-500 p-6 rounded-r-xl shadow-lg flex items-start gap-4 transform transition-all hover:scale-[1.01]">
                    <div class="p-2 bg-red-100 rounded-full text-red-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-red-900 font-bold text-lg">Búsqueda sin resultados</h3>
                        <p class="text-red-700 mt-1">No localizamos registros para: <span class="font-mono bg-red-50 px-2 py-0.5 rounded border border-red-100 text-red-800">{{ implode(', ', $notFoundNumbers) }}</span></p>
                    </div>
                </div>
            @endif

            <div class="space-y-12">
            @forelse ($results as $item)
                @php
                    $isFactura = $item->source === 'factura';
                    $data = $isFactura ? $item : $item;
                    $id = $isFactura ? $item->numero_factura : $item->invoice_number;
                    
                    $todosLosEventos = $data->eventos;
                    if ($isFactura && $data->guia && $data->guia->eventos) {
                        $todosLosEventos = $todosLosEventos->merge($data->guia->eventos);
                    }
                    $todosLosEventos = $todosLosEventos->unique('id')->sortByDesc('created_at');

                    $progress = 10;
                    $statusText = 'Procesando';
                    $stepIndex = 1;
                    
                    if ($isFactura) {
                        if ($data->estatus_entrega == 'Entregada') {
                            $progress = 100; $statusText = 'Entregado'; $stepIndex = 4;
                        } elseif (!$todosLosEventos->isEmpty()) {
                            $progress = 66; 
                            $ultimoEvento = $todosLosEventos->first();
                            $statusText = $ultimoEvento->subtipo ?? 'En Ruta';
                            $stepIndex = 3;
                        } elseif ($data->csPlanning) {
                            $progress = 33; $statusText = 'Preparación'; $stepIndex = 2;
                        }
                    }
                @endphp

                <div class="glass-panel rounded-2xl shadow-[0_20px_50px_rgba(8,_112,_184,_0.07)] overflow-hidden ring-1 ring-slate-900/5 transition-all duration-300">
                    
                    <div class="bg-white border-b border-gray-100 p-6 md:p-8">
                        <div class="flex flex-col md:flex-row justify-between md:items-center gap-6 mb-8">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-widest py-1 px-2 rounded border border-slate-200">
                                        {{ $isFactura ? 'FACTURA COMERCIAL' : 'ORDEN DE VENTA' }}
                                    </span>
                                    @if($isFactura && $data->estatus_entrega == 'Entregada')
                                        <span class="bg-green-50 text-green-700 text-[10px] font-bold uppercase tracking-widest py-1 px-2 rounded border border-green-100 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Completado
                                        </span>
                                    @endif
                                </div>
                                <h3 class="text-4xl font-black text-slate-800 font-mono tracking-tighter">{{ $id }}</h3>
                                <p class="text-slate-500 text-sm mt-1">Cliente: <span class="font-semibold text-slate-700">{{ $isFactura ? ($data->csPlanning->order->customer_name ?? 'N/A') : $data->customer_name }}</span></p>
                            </div>

                            @php
                                $editor = $isFactura ? ($data->csPlanning->order->updater ?? null) : $data->updater;
                                $phone = $editor?->phone_number;
                            @endphp
                            @if($phone)
                                <a href="https://wa.me/521{{$phone}}" target="_blank" class="group relative inline-flex items-center justify-center px-6 py-3 font-bold text-white transition-all duration-200 bg-[#ff9c00] font-pj rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 hover:bg-orange-600 active:scale-95 shadow-lg shadow-orange-500/30">
                                    <span class="absolute top-0 right-0 w-3 h-3 -mt-1 -mr-1 rounded-full bg-green-400 animate-ping"></span>
                                    <span class="absolute top-0 right-0 w-3 h-3 -mt-1 -mr-1 rounded-full bg-green-500 border-2 border-white"></span>
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                    <span>CONTACTAR EJECUTIVO</span>
                                </a>
                            @endif
                        </div>

                        <div class="relative mt-8 mx-2">
                            <div class="absolute top-1/2 left-0 w-full h-1.5 bg-slate-100 rounded-full -translate-y-1/2 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-500 via-[#ff9c00] to-orange-500 transition-all duration-1000 ease-out rounded-full shadow-[0_0_15px_rgba(255,156,0,0.5)]" style="width: {{ $progress }}%"></div>
                            </div>
                            
                            <div class="relative z-10 flex justify-between w-full">
                                @foreach(['Orden Recibida', 'Planificación', 'En Ruta', 'Entregado'] as $index => $label)
                                    @php $isActive = $stepIndex > $index; $isCurrent = $stepIndex == $index + 1; @endphp
                                    <div class="flex flex-col items-center group cursor-default">
                                        <div class="relative">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 z-20 relative {{ $isActive || $isCurrent ? 'bg-white border-[#ff9c00] shadow-lg' : 'bg-slate-50 border-slate-200' }}">
                                                @if($index == 3 && $isActive) 
                                                    <span class="text-green-500 font-bold">✓</span>
                                                @elseif($isActive || $isCurrent)
                                                    <span class="block w-3 h-3 bg-[#ff9c00] rounded-full {{ $isCurrent ? 'animate-pulse' : '' }}"></span>
                                                @else
                                                    <span class="block w-2 h-2 bg-slate-300 rounded-full"></span>
                                                @endif
                                            </div>
                                            @if($isCurrent)
                                                <div class="absolute inset-0 animate-ring rounded-full z-10"></div>
                                            @endif
                                        </div>
                                        <div class="mt-3 text-center transition-all duration-300 {{ $isActive || $isCurrent ? 'opacity-100 transform translate-y-0' : 'opacity-50 transform translate-y-1' }}">
                                            <p class="text-xs font-bold uppercase tracking-wider {{ $isActive || $isCurrent ? 'text-slate-800' : 'text-slate-400' }}">{{ $label }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50/50 p-6 md:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
                        
                        <div class="lg:col-span-7">
                            <h4 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Bitácora de Actividad
                            </h4>

                            @if($isFactura && !$todosLosEventos->isEmpty())
                                <div class="relative pl-4 border-l-2 border-slate-200 space-y-8">
                                    @foreach($todosLosEventos as $evento)
                                        <div class="relative group">
                                            <div class="absolute -left-[23px] top-1 w-4 h-4 rounded-full border-2 border-white shadow-sm transition-colors duration-300 {{ $loop->first ? 'bg-[#ff9c00]' : 'bg-slate-300 group-hover:bg-blue-400' }}"></div>
                                            
                                            <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden">
                                                <div class="absolute top-0 left-0 w-1 h-full {{ $evento->subtipo == 'Factura Entregada' ? 'bg-green-500' : 'bg-blue-500' }}"></div>
                                                
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h5 class="font-bold text-slate-800 text-base">{{ $evento->subtipo }}</h5>
                                                        <p class="text-xs font-mono text-slate-500 mt-1">{{ $evento->fecha_evento->isoFormat('dddd D, MMMM YYYY - h:mm A') }}</p>
                                                    </div>
                                                    @if($evento->subtipo == 'Factura Entregada')
                                                        <div class="bg-green-100 text-green-700 p-1.5 rounded-lg">
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if(!empty($evento->url_evidencia))
                                                    <div class="mt-4 flex gap-3 overflow-x-auto pb-2 custom-scrollbar">
                                                        @foreach($evento->url_evidencia as $url)
                                                            <a href="{{ $url }}" target="_blank" class="block w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden border border-slate-200 relative group/img">
                                                                <img src="{{ $url }}" class="w-full h-full object-cover transition-transform duration-500 group-hover/img:scale-110" alt="Evidencia">
                                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center">
                                                                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                                </div>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center p-8 bg-white border border-dashed border-slate-300 rounded-xl">
                                    <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    </div>
                                    <p class="text-slate-500 text-sm font-medium">Esperando sincronización de ruta...</p>
                                    <p class="text-slate-400 text-xs mt-1">Los eventos aparecerán aquí cuando la unidad inicie viaje.</p>
                                </div>
                            @endif
                        </div>

                        <div class="lg:col-span-5 space-y-6">
                            
                            @if($isFactura && !$todosLosEventos->isEmpty())
                                @php $lastEvent = $todosLosEventos->first(); @endphp
                                <div class="bg-white p-2 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                                    <div class="relative rounded-xl overflow-hidden group">
                                        <img 
                                            src="https://maps.googleapis.com/maps/api/staticmap?center={{$lastEvent->latitud}},{{$lastEvent->longitud}}&zoom=14&size=600x400&maptype=roadmap&markers=color:orange%7C{{$lastEvent->latitud}},{{$lastEvent->longitud}}&key={{ $googleMapsApiKey }}&style=feature:poi|visibility:off" 
                                            alt="Mapa" 
                                            class="w-full h-64 object-cover filter saturate-150"
                                        >
                                        <a href="https://www.google.com/maps/search/?api=1&query={{$lastEvent->latitud}},{{$lastEvent->longitud}}" target="_blank" class="absolute inset-0 bg-[#2c3856]/80 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-center backdrop-blur-sm cursor-pointer">
                                            <span class="text-white font-bold text-lg mb-2">Ver en Google Maps</span>
                                            <div class="px-4 py-2 bg-white/20 rounded-full text-white text-xs border border-white/30">Click para abrir</div>
                                        </a>
                                        <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur px-3 py-1 rounded-md shadow text-xs font-bold text-slate-700">
                                            Última ubicación conocida
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="bg-[#2c3856] rounded-2xl p-6 text-white relative overflow-hidden">
                                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-[#ff9c00] rounded-full opacity-20 blur-2xl"></div>
                                
                                <h4 class="text-xs font-bold text-blue-200 uppercase tracking-widest mb-4">Detalles del Pedido</h4>
                                <ul class="space-y-4 text-sm">
                                    <li class="flex justify-between border-b border-white/10 pb-2">
                                        <span class="text-blue-200">Fecha Creación:</span>
                                        <span class="font-mono">{{ $isFactura ? ($data->csPlanning->order->creation_date ?? '-') : \Carbon\Carbon::parse($data->creation_date)->format('d/m/Y') }}</span>
                                    </li>
                                    <li class="flex justify-between border-b border-white/10 pb-2">
                                        <span class="text-blue-200">Tipo de Servicio:</span>
                                        <span class="font-semibold">{{ $isFactura ? 'Entrega Local' : 'Orden Venta' }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="text-blue-200">Estatus Interno:</span>
                                        <span class="px-2 py-0.5 rounded bg-white/10 text-[#ff9c00] font-bold text-xs">{{ $statusText }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                    
                    <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 flex justify-between items-center text-xs text-slate-400">
                        <span>ID Ref: {{ $data->id }}</span>
                        <div class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            Sistema Operativo
                        </div>
                    </div>

                </div>
            @empty
                <div class="text-center py-20">
                    <div class="inline-block p-6 rounded-full bg-white shadow-xl mb-6 ring-1 ring-slate-100">
                        <svg class="w-16 h-16 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-700">Listo para rastrear</h3>
                    <p class="text-slate-500 max-w-md mx-auto mt-2">Ingrese el número de factura para visualizar la telemetría en tiempo real y el estado logístico.</p>
                </div>
            @endforelse
            </div>
        @endif
    </div>
</div>
@endsection