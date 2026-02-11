<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .nexus-card { background: white; border-radius: 1.5rem; box-shadow: 0 10px 30px -5px rgba(44, 56, 86, 0.05); border: 1px solid #f3f4f6; }
        .nexus-table { width: 100%; border-collapse: separate; border-spacing: 0 0.8rem; }
        .nexus-table thead th { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 800; padding: 0 1.5rem 0.5rem 1.5rem; text-align: left; }
        .nexus-row { background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.02); transition: all 0.2s; }
        .nexus-row td { padding: 1rem 1.5rem; vertical-align: middle; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; background-color: white; }
        .nexus-row td:first-child { border-top-left-radius: 1rem; border-bottom-left-radius: 1rem; border-left: 1px solid #f3f4f6; }
        .nexus-row td:last-child { border-top-right-radius: 1rem; border-bottom-right-radius: 1rem; border-right: 1px solid #f3f4f6; }
        
        .btn-ghost { background: transparent; color: #2c3856; border: 2px solid #e5e7eb; border-radius: 1rem; font-weight: 700; transition: all 0.2s; padding: 0.5rem 1rem; }
        .btn-ghost:hover { border-color: #2c3856; background: #2c3856; color: white; }
        
        .btn-nexus { background: #2c3856; color: white; border-radius: 1rem; padding: 0.75rem 1.5rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(44, 56, 86, 0.2); }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(44, 56, 86, 0.3); }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative">
        <div class="max-w-7xl mx-auto px-4 md:px-6 pt-6 md:pt-10">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
                <div>
                    <h1 class="text-3xl md:text-5xl font-raleway font-black text-[#2c3856]">
                        Solicitud <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">{{ $serviceRequest->folio }}</span>
                    </h1>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                            {{ $serviceRequest->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $serviceRequest->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $serviceRequest->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $serviceRequest->status === 'invoiced' ? 'bg-blue-100 text-blue-700' : '' }}">
                            {{ ucfirst(__($serviceRequest->status)) }}
                        </span>
                        <span class="text-gray-400 text-sm font-medium"> • {{ $serviceRequest->user->name }}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                     <a href="{{ route('wms.service-requests.index') }}" class="btn-ghost flex items-center gap-2 text-xs uppercase">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <a href="{{ route('wms.service-requests.pdf', $serviceRequest) }}" target="_blank" class="btn-ghost flex items-center gap-2 text-xs uppercase text-red-600 border-red-200 hover:bg-red-50 hover:text-red-700 hover:border-red-300">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 space-y-6">
                    <div class="nexus-card p-6">
                        <h3 class="text-lg font-raleway font-black text-[#2c3856] mb-4">Detalles Generales</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Cliente</p>
                                <p class="font-bold text-[#2c3856]">{{ $serviceRequest->area->name }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Almacén</p>
                                <p class="font-bold text-[#2c3856]">{{ $serviceRequest->warehouse->name }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Creado</p>
                                    <p class="font-mono text-sm font-bold text-gray-600">{{ $serviceRequest->requested_at->format('d/m/Y') }}</p>
                                    <p class="font-mono text-[10px] text-gray-400">{{ $serviceRequest->requested_at->format('H:i') }}</p>
                                </div>
                                @if($serviceRequest->completed_at)
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Completado</p>
                                    <p class="font-mono text-sm font-bold text-gray-600">{{ $serviceRequest->completed_at->format('d/m/Y') }}</p>
                                    <p class="font-mono text-[10px] text-gray-400">{{ $serviceRequest->completed_at->format('H:i') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        @if($serviceRequest->status === 'pending')
                        <div class="mt-6 pt-6 border-t border-gray-100 space-y-3">
                            <form action="{{ route('wms.service-requests.update', $serviceRequest) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="w-full py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl shadow-lg shadow-green-500/30 transition-all uppercase tracking-widest text-xs" onclick="return confirm('¿Marcar como completado?');">
                                    <i class="fas fa-check mr-2"></i> Completar
                                </button>
                            </form>
                            <form action="{{ route('wms.service-requests.update', $serviceRequest) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="w-full py-3 bg-red-50 text-red-600 hover:bg-red-100 font-bold rounded-xl border border-red-100 transition-all uppercase tracking-widest text-xs" onclick="return confirm('¿Cancelar solicitud?');">
                                    <i class="fas fa-times mr-2"></i> Cancelar
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="nexus-card p-6 md:p-8" x-data="{ showVasModal: false }">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-lg font-raleway font-black text-[#2c3856]">Servicios y Consumibles</h4>
                            @if($serviceRequest->status === 'pending')
                            <button @click="showVasModal = true" class="btn-ghost text-xs uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                            @endif
                        </div>

                        <div class="overflow-x-auto pb-2">
                            <table class="nexus-table min-w-full">
                                <thead>
                                    <tr>
                                        <th class="pl-4">Concepto</th>
                                        <th class="text-center">Cant.</th>
                                        <th class="text-right">Precio Unit.</th>
                                        <th class="text-right">Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($serviceRequest->valueAddedServices as $assignment)
                                        <tr class="nexus-row">
                                            <td class="pl-4">
                                                <p class="font-bold text-[#2c3856] text-xs">{{ $assignment->service->description }}</p>
                                                <p class="font-mono text-[10px] text-gray-400 mt-1">{{ $assignment->service->code }}</p>
                                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $assignment->service->type == 'service' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                                    {{ $assignment->service->type }}
                                                </span>
                                            </td>
                                            <td class="text-center font-bold text-[#2c3856] text-sm">{{ $assignment->quantity }}</td>
                                            <td class="text-right font-mono text-gray-600 text-xs">${{ number_format($assignment->cost_snapshot, 2) }}</td>
                                            <td class="text-right font-bold text-[#2c3856] text-sm">${{ number_format($assignment->quantity * $assignment->cost_snapshot, 2) }}</td>
                                            <td class="text-right pr-4">
                                                @if($serviceRequest->status === 'pending')
                                                <form action="{{ route('wms.value-added-services.detach', $assignment) }}" method="POST" onsubmit="return confirm('¿Quitar servicio?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-400 hover:text-red-600 transition-colors"><i class="fas fa-times"></i></button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-8 text-gray-400 italic text-xs">
                                                <i class="fas fa-box-open text-2xl mb-2 opacity-30"></i><br>
                                                No hay servicios asignados.
                                            </td>
                                        </tr>
                                    @endforelse
                                    
                                    @if($serviceRequest->valueAddedServices->isNotEmpty())
                                    <tr class="nexus-row bg-gray-50/50">
                                        <td colspan="3" class="text-right font-bold text-gray-500 text-xs uppercase pr-4 py-3">Total:</td>
                                        <td class="text-right font-black text-[#2c3856] text-lg py-3">${{ number_format($serviceRequest->valueAddedServices->sum(fn($a) => $a->quantity * $a->cost_snapshot), 2) }}</td>
                                        <td></td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div x-show="showVasModal" x-cloak style="display: none;" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
                            <div class="fixed inset-0 bg-[#2c3856]/80 backdrop-blur-sm transition-opacity" @click="showVasModal = false"></div>
                            
                            <div class="relative z-10 bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden transform transition-all">
                                <div class="bg-[#2c3856] px-6 py-4 flex justify-between items-center">
                                    <h3 class="text-white font-raleway font-bold">Agregar Servicio</h3>
                                    <button @click="showVasModal = false" class="text-white/70 hover:text-white"><i class="fas fa-times"></i></button>
                                </div>
                                <form action="{{ route('wms.value-added-services.assign') }}" method="POST" class="p-6 space-y-4">
                                    @csrf
                                    <input type="hidden" name="assignable_type" value="service_request">
                                    <input type="hidden" name="assignable_id" value="{{ $serviceRequest->id }}">
                                    
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Servicio / Consumible</label>
                                        <select name="value_added_service_id" required class="w-full rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 text-sm p-3 bg-gray-50">
                                            <option value="">-- Seleccionar --</option>
                                            @foreach($services as $service)
                                                <option value="{{ $service->id }}">
                                                    {{ $service->description }} (${{ number_format($service->cost, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Cantidad</label>
                                        <input type="number" name="quantity" value="1" min="1" required class="w-full rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 text-sm font-bold p-3 bg-gray-50">
                                    </div>

                                    <div class="pt-2">
                                        <button type="submit" class="w-full py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-lg shadow-purple-500/30 transition-all uppercase tracking-widest text-xs">
                                            Asignar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
