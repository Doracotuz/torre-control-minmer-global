<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .stagger-enter { opacity: 0; transform: translateY(20px); animation: enterUp 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes enterUp { to { opacity: 1; transform: translateY(0); } }
        
        .btn-nexus { 
            background: #2c3856; color: white; border-radius: 1rem; font-weight: 700;
            transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(44, 56, 86, 0.2); }
        
        .btn-ghost {
            background: transparent; color: #2c3856; border: 2px solid #e5e7eb; border-radius: 1rem; font-weight: 700;
        }
        .btn-ghost:hover { border-color: #2c3856; background: #2c3856; color: white; }

        .nexus-table { width: 100%; border-collapse: separate; border-spacing: 0 0.8rem; }
        .nexus-table thead th {
            font-size: 0.9/8rem; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 800;
            padding: 0 1.5rem 0.5rem 1.5rem; text-align: left;
        }
        .nexus-row {
            background: white; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .nexus-row td {
            padding: 1rem 1.5rem; vertical-align: middle; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6;
            background-color: white;
        }
        .nexus-row td:first-child { border-top-left-radius: 1rem; border-bottom-left-radius: 1rem; border-left: 1px solid #f3f4f6; }
        .nexus-row td:last-child { border-top-right-radius: 1rem; border-bottom-right-radius: 1rem; border-right: 1px solid #f3f4f6; }
        .nexus-row:hover { box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.05); z-index: 10; position: relative; }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-[50vw] h-full bg-gradient-to-l from-[#f8fafc] to-transparent"></div>
            <div class="absolute bottom-0 left-0 w-[40rem] h-[40rem] bg-[#ff9c00]/5 rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00]"></span>
                        <span class="text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Detalle de Operación</span>
                    </div>
                    <h1 class="text-5xl font-raleway font-black text-[#2c3856] leading-none">
                        SO <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">{{ $salesOrder->so_number }}</span>
                    </h1>
                </div>

                <div class="flex gap-3 mt-6 xl:mt-0">
                    <a href="{{ route('wms.sales-orders.index') }}" class="btn-ghost px-6 py-3 h-12 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-xl mb-8 font-bold flex items-center gap-3 stagger-enter">
                    <i class="fas fa-check-circle text-xl"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl mb-8 font-bold flex items-center gap-3 stagger-enter">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 stagger-enter" style="animation-delay: 0.2s;">
                
                <div class="lg:col-span-2 space-y-8">
                    
                    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 overflow-hidden">
                        <div class="bg-[#2c3856] p-8 text-white relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-64 h-64 bg-[#ff9c00] rounded-full blur-[100px] opacity-20 -mr-20 -mt-20"></div>
                            <div class="relative z-10 flex justify-between items-center">
                                <div>
                                    <p class="text-[#ff9c00] font-bold text-xs uppercase tracking-[0.2em] mb-1">Cliente</p>
                                    <h3 class="text-2xl font-bold">{{ $salesOrder->customer_name }}</h3>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs uppercase tracking-widest opacity-60 mb-1">Estado</p>
                                    <span class="px-4 py-1.5 bg-white/10 border border-white/20 rounded-full font-bold text-sm">
                                        {{ $salesOrder->status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-8">
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-y-6 gap-x-8">
                                <div><p class="text-[10px] font-bold text-gray-400 uppercase">Almacén</p><p class="font-bold text-[#2c3856]">{{ $salesOrder->warehouse->name }}</p></div>
                                <div><p class="text-[10px] font-bold text-gray-400 uppercase">Área</p><p class="font-bold text-[#2c3856]">{{ $salesOrder->area->name ?? 'N/A' }}</p></div>
                                <div><p class="text-[10px] font-bold text-gray-400 uppercase">Fecha y hora de entrega</p><p class="font-bold text-[#2c3856]">{{ ($salesOrder->ff_delivery_date ?? $salesOrder->order_date)->format('d M Y h:i A') }}</p></div>
                                <div><p class="text-[10px] font-bold text-gray-400 uppercase">Factura</p><p class="font-mono font-bold text-[#2c3856]">{{ $salesOrder->invoice_number ?? '-' }}</p></div>
                                <div><p class="text-[10px] font-bold text-gray-400 uppercase">Creado Por</p><p class="font-bold text-[#2c3856]">{{ $salesOrder->user->name }}</p></div>
                            </div>

                            <div class="mt-8 flex gap-4 border-t pt-6">
                                @if ($salesOrder->status == 'Pending' && Auth::user()->hasFfPermission('wms.sales_orders.edit'))
                                    <a href="{{ route('wms.sales-orders.edit', $salesOrder) }}" class="btn-ghost px-6 py-2 text-xs uppercase tracking-widest shadow-sm">
                                        <i class="fas fa-pencil-alt mr-2"></i> Editar
                                    </a>
                                    <form action="{{ route('wms.sales-orders.cancel', $salesOrder) }}" method="POST" onsubmit="return confirm('¿Confirmar cancelación?');">
                                        @csrf
                                        <button type="submit" class="btn-ghost px-6 py-2 text-xs uppercase tracking-widest text-red-600 border-red-200 hover:bg-red-50">
                                            <i class="fas fa-ban mr-2"></i> Cancelar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 p-8" x-data="{ showVasModal: false }">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <span class="w-1 h-6 bg-purple-500 rounded-full"></span>
                                <h4 class="text-lg font-raleway font-black text-[#2c3856]">Valor Agregado</h4>
                            </div>
                            @if(Auth::user()->hasFfPermission('wms.picking'))
                            <button @click="showVasModal = true" class="btn-ghost px-4 py-2 text-xs uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                            @endif
                        </div>

                        <div class="overflow-x-auto custom-scrollbar pb-2">
                            <table class="nexus-table min-w-full">
                                <thead>
                                    <tr>
                                        <th class="pl-4">Servicio / Consumible</th>
                                        <th class="text-center">Cant.</th>
                                        <th class="text-right">Costo Unit.</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-right"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($salesOrder->valueAddedServices as $assignment)
                                        <tr class="nexus-row">
                                            <td class="pl-4">
                                                <p class="font-bold text-[#2c3856] text-xs">{{ $assignment->service->description }}</p>
                                                <p class="font-mono text-[10px] text-gray-400 mt-1">{{ $assignment->service->code }}</p>
                                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $assignment->service->type == 'service' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                                    {{ $assignment->service->type == 'service' ? 'Servicio' : 'Consumible' }}
                                                </span>
                                            </td>
                                            <td class="text-center font-bold text-[#2c3856] text-sm">{{ $assignment->quantity }}</td>
                                            <td class="text-right font-mono text-gray-600 text-xs">${{ number_format($assignment->cost_snapshot, 2) }}</td>
                                            <td class="text-right font-bold text-[#2c3856] text-sm">${{ number_format($assignment->quantity * $assignment->cost_snapshot, 2) }}</td>
                                            <td class="text-right pr-4">
                                                @if(Auth::user()->hasFfPermission('wms.picking'))
                                                <form action="{{ route('wms.value-added-services.detach', $assignment) }}" method="POST" onsubmit="return confirm('¿Quitar servicio?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-400 hover:text-red-600 transition-colors"><i class="fas fa-times"></i></button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-gray-400 italic text-xs">No hay servicios agregados.</td>
                                        </tr>
                                    @endforelse
                                    @if($salesOrder->valueAddedServices->isNotEmpty())
                                        <tr class="nexus-row bg-gray-50/50">
                                            <td colspan="3" class="text-right font-bold text-gray-500 text-xs uppercase pr-4 py-3">Total Valor Agregado:</td>
                                            <td class="text-right font-black text-[#2c3856] text-lg py-3">${{ number_format($salesOrder->valueAddedServices->sum(fn($a) => $a->quantity * $a->cost_snapshot), 2) }}</td>
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
                                    <input type="hidden" name="assignable_type" value="sales_order">
                                    <input type="hidden" name="assignable_id" value="{{ $salesOrder->id }}">
                                    
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Servicio / Consumible</label>
                                        <select name="value_added_service_id" required class="w-full rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 text-sm">
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
                                        <input type="number" name="quantity" value="1" min="1" required class="w-full rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 text-sm font-bold">
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

                    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-1 h-6 bg-blue-500 rounded-full"></span>
                            <h4 class="text-lg font-raleway font-black text-[#2c3856]">
                                @if($salesOrder->pickList)
                                    Surtido (Pick List #{{ $salesOrder->pickList->id }})
                                @else
                                    Detalle de Orden
                                @endif
                            </h4>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="nexus-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Ubicación</th>
                                        <th>LPN</th>
                                        <th>Calidad</th>
                                        <th>Pedimento</th>
                                        <th class="text-right">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($salesOrder->pickList && $salesOrder->pickList->items->count() > 0)
                                        @foreach($salesOrder->pickList->items as $item)
                                            <tr class="nexus-row">
                                                <td>
                                                    <p class="font-bold text-[#2c3856] text-xs">{{ $item->product->name }}</p>
                                                    <p class="font-mono text-[10px] text-gray-400 mt-1">{{ $item->product->sku }}</p>
                                                </td>
                                                <td class="font-mono font-bold text-red-600 text-xs">{{ $item->pallet->location->code ?? 'N/A' }}</td>
                                                <td class="font-mono font-bold text-indigo-600 text-xs">{{ $item->pallet->lpn ?? 'N/A' }}</td>
                                                <td class="text-xs font-bold text-gray-600">{{ $item->quality->name ?? 'N/A' }}</td>
                                                <td class="font-mono text-[10px] text-gray-500">{{ $item->pallet->purchaseOrder->pedimento_a4 ?? 'N/A' }}</td>
                                                <td class="text-right font-black text-lg text-[#2c3856]">{{ $item->quantity_picked ?? $item->quantity_to_pick }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        @foreach($salesOrder->lines as $line)
                                            <tr class="nexus-row">
                                                <td>
                                                    <p class="font-bold text-[#2c3856] text-md">{{ $line->product->name }}</p>
                                                    <p class="font-mono text-[12px] text-gray-400 mt-1">{{ $line->product->sku }}</p>
                                                </td>
                                                <td class="text-md text-gray-400 italic">{{ $line->palletItem->pallet->location->code ?? 'Pendiente' }}</td>
                                                <td class="text-md text-gray-400 italic font-mono">{{ $line->palletItem->pallet->lpn ?? 'Automático' }}</td>
                                                <td class="text-md font-bold text-gray-600">{{ $line->quality->name }}</td>
                                                <td class="text-[12px] text-gray-400 italic font-mono">{{ $line->palletItem->pallet->purchaseOrder->pedimento_a4 ?? '-' }}</td>
                                                <td class="text-right font-black text-lg text-[#2c3856]">{{ $line->quantity_ordered }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="space-y-8">
                    
                    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 p-8 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full blur-3xl -mr-10 -mt-10"></div>
                        <h4 class="text-lg font-raleway font-black text-[#2c3856] mb-6 relative z-10"><i class="fas fa-tasks mr-2"></i>Gestión de Surtido</h4>

                        @if ($salesOrder->status == 'Pending' && Auth::user()->hasFfPermission('wms.picking'))
                            <form action="{{ route('wms.picking.generate', $salesOrder) }}" method="POST" class="relative z-10">
                                @csrf
                                <div class="p-4 bg-yellow-50 rounded-2xl border border-yellow-100 mb-4 text-xs text-yellow-800">
                                    Al generar el Pick List, el sistema reservará el inventario automáticamente.
                                </div>
                                <button type="submit" class="btn-nexus w-full py-3 shadow-lg">
                                    Generar Pick List
                                </button>
                            </form>
                        @elseif ($salesOrder->pickList)
                            <div class="space-y-4 relative z-10">
                                <div class="p-4 bg-green-50 rounded-2xl border border-green-100">
                                    <p class="text-[10px] font-bold text-green-600 uppercase">Pick List Generada</p>
                                    <p class="font-mono font-bold text-[#2c3856] text-xl">#{{ $salesOrder->pickList->id }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Status: {{ $salesOrder->pickList->status }}</p>
                                </div>
                                
                                @if($salesOrder->status === 'Pending' && !$salesOrder->pickList)
                                    
                                    <form action="{{ route('wms.picking.generate', $salesOrder) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-nexus ...">
                                            Ejecutar Surtido
                                        </button>
                                    </form>

                                @endif
                                
                                @if(Auth::user()->hasFfPermission('wms.picking'))
                                    <div class="space-y-3">
                                        @if($salesOrder->status == 'Picking')
                                            <a href="{{ route('wms.picking.show', $salesOrder->pickList) }}" class="btn-nexus w-full py-4 text-sm uppercase tracking-widest shadow-lg bg-[#ff9c00] hover:bg-orange-600 border-none text-white">
                                                <i class="fas fa-dolly-flatbed mr-2"></i> Realizar Picking
                                            </a>
                                        @endif

                                        <a href="{{ route('wms.picking.pdf', $salesOrder->pickList) }}" target="_blank" class="btn-ghost w-full py-3 flex items-center justify-center">
                                            <i class="fas fa-file-pdf mr-2"></i> Descargar PDF
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-center text-gray-400 text-sm">
                                Orden finalizada o cancelada.
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>