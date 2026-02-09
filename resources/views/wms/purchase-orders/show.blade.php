<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .stagger-enter { opacity: 0; transform: translateY(20px); animation: enterUp 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes enterUp { to { opacity: 1; transform: translateY(0); } }
        
        .input-arch {
            background: transparent; border: none; border-bottom: 2px solid #e5e7eb; border-radius: 0;
            padding: 0.5rem 0; font-family: 'Montserrat', sans-serif; font-weight: 600; color: #2c3856;
            transition: all 0.3s ease; width: 100%;
        }
        .input-arch:focus { border-bottom-color: #ff9c00; box-shadow: none; outline: none; }

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
            font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 800;
            padding: 0 1.5rem 0.5rem 1.5rem; text-align: left; white-space: nowrap;
        }
        .nexus-row {
            background: white; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .nexus-row td {
            padding: 1rem 1.5rem; vertical-align: middle; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6;
            background-color: white; white-space: nowrap;
        }
        .nexus-row td:first-child { border-top-left-radius: 1rem; border-bottom-left-radius: 1rem; border-left: 1px solid #f3f4f6; }
        .nexus-row td:last-child { border-top-right-radius: 1rem; border-bottom-right-radius: 1rem; border-right: 1px solid #f3f4f6; }
        .nexus-row:hover { box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.05); z-index: 10; position: relative; }

        .upload-card {
            position: relative; border: 2px dashed #e5e7eb; border-radius: 1rem; padding: 1rem;
            transition: all 0.3s; text-align: center; cursor: pointer; height: 140px;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
        }
        .upload-card:hover { border-color: #3b82f6; background-color: #eff6ff; }
        .upload-card img {
            position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; border-radius: 0.8rem;
        }

        [x-cloak] { display: none !important; }
        
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 99px; }
        .custom-scrollbar::-webkit-scrollbar-track { background-color: transparent; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-x-hidden" x-data="evidenceHandler()">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-[60vw] h-full bg-gradient-to-l from-[#f8fafc] to-transparent"></div>
            <div class="absolute bottom-0 left-0 w-[40rem] h-[40rem] bg-[#ff9c00]/5 rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-4 md:px-6 pt-6 md:pt-10 relative z-10">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 md:mb-10 gap-4 stagger-enter" style="animation-delay: 0.1s;">
                <div class="w-full md:w-auto">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-8 md:w-12 h-1 bg-[#ff9c00]"></span>
                        <span class="text-xs md:text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Detalle de Operación</span>
                    </div>
                    <h1 class="text-3xl md:text-5xl font-raleway font-black text-[#2c3856] leading-none break-all">
                        PO <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">{{ $purchaseOrder->po_number }}</span>
                    </h1>
                </div>

                <div class="w-full md:w-auto flex gap-3 mt-2 md:mt-0">
                    <a href="{{ route('wms.purchase-orders.index') }}" class="btn-ghost w-full md:w-auto px-6 py-3 h-12 flex items-center justify-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-xl mb-8 font-bold flex items-center gap-3 stagger-enter text-sm md:text-base">
                    <i class="fas fa-check-circle text-xl flex-shrink-0"></i>
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 stagger-enter" style="animation-delay: 0.2s;">
                
                <div class="lg:col-span-2 space-y-6 md:space-y-8">
                    
                    <div class="bg-white rounded-[2rem] md:rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 overflow-hidden">
                        <div class="bg-[#2c3856] p-6 md:p-8 text-white relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-64 h-64 bg-[#ff9c00] rounded-full blur-[100px] opacity-20 -mr-20 -mt-20"></div>
                            <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div>
                                    <p class="text-[#ff9c00] font-bold text-[10px] md:text-xs uppercase tracking-[0.2em] mb-1">Cliente / Área</p>
                                    <h3 class="text-xl md:text-2xl font-bold leading-tight">{{ $purchaseOrder->area->name ?? 'N/A' }}</h3>
                                </div>
                                <div class="self-start sm:self-auto">
                                    <span class="inline-block px-3 py-1 bg-white/10 border border-white/20 rounded-full font-bold text-xs md:text-sm">
                                        {{ $purchaseOrder->status_in_spanish }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6 md:p-8">
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-y-6 gap-x-4 md:gap-x-8">
                                <div><p class="text-[9px] md:text-[10px] font-bold text-gray-400 uppercase">Almacén</p><p class="font-bold text-[#2c3856] text-sm md:text-base">{{ $purchaseOrder->warehouse->name }}</p></div>
                                <div><p class="text-[9px] md:text-[10px] font-bold text-gray-400 uppercase">Fecha Esperada</p><p class="font-bold text-[#2c3856] text-sm md:text-base">{{ \Carbon\Carbon::parse($purchaseOrder->expected_date)->format('d M Y') }}</p></div>
                                <div><p class="text-[9px] md:text-[10px] font-bold text-gray-400 uppercase">Contenedor</p><p class="font-mono font-bold text-[#2c3856] text-sm md:text-base break-words">{{ $purchaseOrder->container_number ?? '-' }}</p></div>
                                <div><p class="text-[9px] md:text-[10px] font-bold text-gray-400 uppercase">Factura</p><p class="font-mono font-bold text-[#2c3856] text-sm md:text-base break-words">{{ $purchaseOrder->document_invoice ?? '-' }}</p></div>
                                <div><p class="text-[9px] md:text-[10px] font-bold text-gray-400 uppercase">Pedimento A4</p><p class="font-mono font-bold text-[#2c3856] text-sm md:text-base break-words">{{ $purchaseOrder->pedimento_a4 ?? '-' }}</p></div>
                                <div><p class="text-[9px] md:text-[10px] font-bold text-gray-400 uppercase">Pedimento G1</p><p class="font-mono font-bold text-[#2c3856] text-sm md:text-base break-words">{{ $purchaseOrder->pedimento_g1 ?? '-' }}</p></div>
                            </div>

                            <div class="mt-8 flex flex-col sm:flex-row gap-4 border-t border-gray-100 pt-6">
                                @if ($purchaseOrder->status != 'Completed' && Auth::user()->hasFfPermission('wms.purchase_orders.edit'))
                                    <a href="{{ route('wms.purchase-orders.edit', $purchaseOrder) }}" class="btn-ghost w-full sm:w-auto px-6 py-3 text-xs uppercase tracking-widest shadow-sm flex justify-center">
                                        <i class="fas fa-pencil-alt mr-2"></i> Editar
                                    </a>
                                @endif
                                @if ($purchaseOrder->status == 'Completed')
                                    <a href="{{ route('wms.purchase-orders.arrival-report-pdf', $purchaseOrder) }}" target="_blank" class="btn-nexus w-full sm:w-auto px-6 py-3 text-xs uppercase tracking-widest shadow-lg flex justify-center">
                                        <i class="fas fa-file-pdf mr-2"></i> Reporte PDF
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2rem] md:rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 p-6 md:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-1 h-6 bg-blue-500 rounded-full"></span>
                            <h4 class="text-lg font-raleway font-black text-[#2c3856]">Resumen de Recepción</h4>
                        </div>
                        
                        @php 
                            $summary = $purchaseOrder->getReceiptSummary(); 
                            $hasExcess = $summary->contains(function($line) {
                                return ($line->quantity_received - $line->quantity_ordered) > 0;
                            });
                        @endphp

                        @if($hasExcess)
                            <div class="mb-6 bg-orange-50 border-l-4 border-orange-500 p-4 rounded-r-xl flex items-start gap-4 animate-pulse-once">
                                <div class="bg-orange-100 p-2 rounded-full text-orange-600 shrink-0">
                                    <i class="fas fa-exclamation-triangle text-xl"></i>
                                </div>
                                <div>
                                    <h5 class="font-bold text-orange-800 text-sm uppercase tracking-wide">Exceso de Inventario Detectado</h5>
                                    <p class="text-xs text-orange-700 mt-1">Se han recibido más unidades de las solicitadas en una o más líneas. Las filas con excedente están resaltadas.</p>
                                </div>
                            </div>
                        @endif
                        
                        <div class="overflow-x-auto custom-scrollbar pb-2">
                            <table class="nexus-table min-w-full">
                                <thead>
                                    <tr>
                                        <th class="pl-4">Producto</th>
                                        <th class="text-center">Ordenado</th>
                                        <th class="text-center">Recibido</th>
                                        <th class="text-center">Cajas</th>
                                        <th class="text-center">Pallets</th>
                                        <th class="text-center">Dif</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($summary as $line)
                                        @php 
                                            $diff = $line->quantity_received - $line->quantity_ordered; 
                                            $isExcess = $diff > 0;
                                        @endphp
                                        
                                        <tr class="nexus-row {{ $isExcess ? 'bg-orange-50/60' : '' }}">
                                            <td class="{{ $isExcess ? 'border-l-4 border-l-orange-400' : '' }} pl-4">
                                                <p class="font-bold text-[#2c3856] text-xs max-w-[120px] md:max-w-xs truncate" title="{{ $line->product_name }}">{{ $line->product_name }}</p>
                                                <p class="font-mono text-[10px] text-gray-400 mt-1">{{ $line->sku }}</p>
                                                @if($line->is_extra ?? false)
                                                    <span class="inline-block mt-1 px-2 py-0.5 bg-purple-100 text-purple-700 rounded text-[9px] font-bold uppercase">No Planeado</span>
                                                @endif
                                            </td>
                                            <td class="text-center font-medium text-gray-600 text-xs md:text-sm">{{ number_format($line->quantity_ordered) }}</td>
                                            <td class="text-center font-black text-sm md:text-lg text-[#2c3856]">{{ number_format($line->quantity_received) }}</td>
                                            <td class="text-center font-bold text-blue-600 text-xs md:text-sm">{{ $line->cases_received }}</td>
                                            <td class="text-center font-medium text-gray-600 text-xs md:text-sm">{{ $line->pallet_count }}</td>
                                            
                                            <td class="text-center font-bold text-xs md:text-sm">
                                                @if($isExcess)
                                                    <span class="text-orange-600 bg-orange-100 px-2 py-1 rounded-lg">
                                                        +{{ number_format($diff) }}
                                                    </span>
                                                @elseif($diff < 0)
                                                    <span class="text-red-500">{{ number_format($diff) }}</span>
                                                @else
                                                    <span class="text-green-500"><i class="fas fa-check"></i></span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2rem] md:rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 p-6 md:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-1 h-6 bg-[#ff9c00] rounded-full"></span>
                            <h4 class="text-lg font-raleway font-black text-[#2c3856]">Historial de Tarimas</h4>
                        </div>
                        
                        <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                            @forelse($purchaseOrder->pallets as $pallet)
                                <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 flex flex-col md:flex-row justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-pallet text-gray-300"></i>
                                            <p class="font-mono font-bold text-[#2c3856] text-base md:text-lg">{{ $pallet->lpn }}</p>
                                        </div>
                                        <div class="text-[10px] md:text-xs text-gray-500 mt-1 pl-6">
                                            <i class="fas fa-user mr-1 text-[#ff9c00]"></i> {{ $pallet->user->name }} • 
                                            <span class="ml-1">{{ $pallet->updated_at->format('d/M H:i') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 md:pl-0 pl-6 border-l-2 md:border-l-0 border-gray-200 md:border-transparent">
                                        <ul class="text-xs space-y-2 pl-2 md:pl-0">
                                            @foreach($pallet->items as $item)
                                                <li class="flex justify-between border-b border-gray-200 pb-1 last:border-0 last:pb-0">
                                                    <span class="text-gray-600 truncate max-w-[150px] md:max-w-none">{{ $item->product->name }} <strong class="text-blue-600">[{{ $item->quality->name }}]</strong></span>
                                                    <span class="font-bold bg-white px-2 py-0.5 rounded border border-gray-200 shrink-0">x{{ $item->quantity }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-gray-400 italic py-4 text-sm">No hay tarimas registradas.</p>
                            @endforelse
                        </div>
                    </div>

                </div>

                <div class="space-y-6 md:space-y-8">
                    
                    <div class="bg-white rounded-[2rem] md:rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 p-6 md:p-8 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full blur-3xl -mr-10 -mt-10"></div>
                        <h4 class="text-lg font-raleway font-black text-[#2c3856] mb-6 relative z-10"><i class="fas fa-truck mr-2"></i>Gestión de Patio</h4>

                        @if(!$purchaseOrder->download_start_time)
                            @if(Auth::user()->hasFfPermission('wms.receiving'))
                            <form action="{{ route('wms.purchase-orders.register-arrival', $purchaseOrder) }}" method="POST" class="space-y-4 relative z-10">
                                @csrf
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Placas</label>
                                    <input type="text" name="truck_plate" required class="input-arch uppercase font-mono text-lg font-bold" placeholder="ABC-123">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Operador</label>
                                    <input type="text" name="driver_name" required class="input-arch" placeholder="Nombre completo">
                                </div>
                                <button type="submit" class="btn-nexus w-full py-3 mt-2 shadow-lg text-sm uppercase tracking-widest">Registrar Llegada</button>
                            </form>
                            @else
                            <div class="p-4 bg-gray-50 rounded-xl text-center text-gray-500 text-sm">
                                No tienes permisos para registrar llegadas.
                            </div>
                            @endif
                        @else
                            <div class="space-y-4 relative z-10">
                                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Operador</p>
                                    <p class="font-bold text-[#2c3856] text-sm">{{ $purchaseOrder->operator_name }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase mt-2">Placas</p>
                                    <p class="font-mono font-bold text-[#2c3856] text-sm">{{ $purchaseOrder->latestArrival->truck_plate ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Entrada</p>
                                    <p class="font-mono text-sm">{{ \Carbon\Carbon::parse($purchaseOrder->download_start_time)->format('d/m/Y H:i') }}</p>
                                </div>
                                @if($purchaseOrder->download_end_time)
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase">Salida</p>
                                        <p class="font-mono text-sm">{{ \Carbon\Carbon::parse($purchaseOrder->download_end_time)->format('d/m/Y H:i') }}</p>
                                    </div>
                                @else
                                    @if(Auth::user()->hasFfPermission('wms.receiving'))
                                    <form action="{{ route('wms.purchase-orders.register-departure', $purchaseOrder) }}" method="POST"
                                          onsubmit="return confirm('ADVERTENCIA DE CIERRE \n\nAl registrar la salida del operador:\n1. La Orden de Compra se marcará como COMPLETADA.\n2. Ya no podrás modificar el inventario de esta recepción.\n\n¿Estás seguro de que deseas finalizar?');">
                                        @csrf
                                        <button type="submit" class="w-full py-3 rounded-xl bg-red-50 text-red-600 font-bold hover:bg-red-100 transition-colors border border-red-100 text-sm uppercase tracking-widest flex items-center justify-center gap-2">
                                            <i class="fas fa-sign-out-alt"></i> Registrar Salida y Finalizar
                                        </button>
                                    </form>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>

                    @if ($purchaseOrder->status != 'Completed' && Auth::user()->hasFfPermission('wms.receiving'))
                        <div class="bg-gradient-to-br from-green-500 to-emerald-600 p-6 md:p-8 rounded-[2rem] md:rounded-[2.5rem] shadow-xl text-center text-white relative overflow-hidden group">
                            <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-20 transition-opacity pointer-events-none"></div>
                            
                            <h3 class="font-bold text-xl mb-2 relative z-10">Continuar Proceso</h3>
                            <p class="text-green-100 text-sm mb-6 relative z-10">Registra los productos físicos en la interfaz de recepción.</p>
                            
                            <a href="{{ route('wms.receiving.show', $purchaseOrder) }}" class="relative z-10 block w-full py-3 bg-white text-green-600 font-bold rounded-xl shadow-lg hover:scale-[1.02] transition-transform">
                                <i class="fas fa-dolly mr-2"></i> Ir a Recepción
                            </a>
                        </div>
                    @endif

                    @if ($purchaseOrder->status == 'Receiving' && Auth::user()->hasFfPermission('wms.receiving'))
                        <div class="bg-white p-6 md:p-8 rounded-[2rem] md:rounded-[2.5rem] shadow-xl border border-gray-100">
                            <h4 class="text-lg font-raleway font-black text-[#2c3856] mb-4">Cierre de Orden</h4>
                            <p class="text-xs text-gray-500 mb-6">Al cerrar la orden, el inventario se consolida y no se podrán agregar más tarimas.</p>
                            
                            <form action="{{ route('wms.purchase-orders.complete', $purchaseOrder) }}" method="POST" onsubmit="return confirm('¿Confirmar cierre de orden?');">
                                @csrf
                                @if ($purchaseOrder->received_bottles < $purchaseOrder->expected_bottles)
                                    <div class="mb-4 p-3 bg-yellow-50 text-yellow-700 text-xs rounded-xl border border-yellow-100 font-medium">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Recepción parcial.
                                    </div>
                                @endif
                                <button type="submit" class="w-full py-3 bg-[#2c3856] text-white font-bold rounded-xl hover:bg-[#1a253a] shadow-lg transition-all text-sm uppercase tracking-widest">
                                    <i class="fas fa-lock mr-2"></i> Cerrar Orden
                                </button>
                            </form>
                        </div>
                    @endif

                    <div class="bg-white p-6 md:p-8 rounded-[2rem] md:rounded-[2.5rem] shadow-xl border border-gray-100">
                        <h4 class="text-lg font-raleway font-black text-[#2c3856] mb-6">Evidencias</h4>
                        
                        <form action="{{ route('wms.purchase-orders.upload-evidence', $purchaseOrder) }}" method="POST" enctype="multipart/form-data" class="space-y-4 md:space-y-6">
                            @csrf
                            
                            <div class="grid grid-cols-2 gap-3 md:gap-4">
                                <div x-data="fileInput('marchamo')" class="upload-card">
                                    <input type="file" name="marchamo" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="updatePreview">
                                    <div x-show="!preview" class="flex flex-col items-center justify-center h-full">
                                        <i class="fas fa-stamp text-gray-300 text-2xl mb-1"></i>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase">Marchamo</p>
                                    </div>
                                    <img x-show="preview" :src="preview">
                                </div>

                                <div x-data="fileInput('puerta_cerrada')" class="upload-card">
                                    <input type="file" name="puerta_cerrada" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="updatePreview">
                                    <div x-show="!preview" class="flex flex-col items-center justify-center h-full">
                                        <i class="fas fa-door-closed text-gray-300 text-2xl mb-1"></i>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase">Cerrada</p>
                                    </div>
                                    <img x-show="preview" :src="preview">
                                </div>

                                <div x-data="fileInput('apertura_puertas')" class="upload-card">
                                    <input type="file" name="apertura_puertas" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="updatePreview">
                                    <div x-show="!preview" class="flex flex-col items-center justify-center h-full">
                                        <i class="fas fa-door-open text-gray-300 text-2xl mb-1"></i>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase">Apertura</p>
                                    </div>
                                    <img x-show="preview" :src="preview">
                                </div>

                                <div x-data="fileInput('caja_vacia')" class="upload-card">
                                    <input type="file" name="caja_vacia" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="updatePreview">
                                    <div x-show="!preview" class="flex flex-col items-center justify-center h-full">
                                        <i class="fas fa-box-open text-gray-300 text-2xl mb-1"></i>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase">Vacía</p>
                                    </div>
                                    <img x-show="preview" :src="preview">
                                </div>
                            </div>

                            <div x-data="multiFileInput('proceso_descarga')" class="upload-card h-auto py-4">
                                <input type="file" name="proceso_descarga[]" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="updateText">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-people-carry text-gray-300 text-2xl mb-1"></i>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase">Proceso Descarga</p>
                                    <p class="text-xs text-blue-500 font-bold mt-1" x-text="fileText"></p>
                                </div>
                            </div>

                            <div x-data="multiFileInput('producto_danado')" class="upload-card h-auto py-4 border-red-200 hover:border-red-400 hover:bg-red-50">
                                <input type="file" name="producto_danado[]" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="updateText">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-red-300 text-2xl mb-1"></i>
                                    <p class="text-[9px] font-bold text-red-400 uppercase">Daños (Opcional)</p>
                                    <p class="text-xs text-red-500 font-bold mt-1" x-text="fileText"></p>
                                </div>
                            </div>

                            @if(Auth::user()->hasFfPermission('wms.receiving'))
                            <button type="submit" class="btn-ghost w-full py-3 text-xs uppercase tracking-widest font-bold">
                                <i class="fas fa-cloud-upload-alt mr-2"></i> Subir Fotos
                            </button>
                            @endif
                        </form>

                        @if($purchaseOrder->evidences->isNotEmpty())
                            <div class="mt-8 grid grid-cols-3 gap-2">
                                @foreach($purchaseOrder->evidences as $evidence)
                                    <div class="aspect-square rounded-lg overflow-hidden relative group cursor-pointer border border-gray-200" @click="openModal('{{ Storage::url($evidence->file_path) }}')">
                                        <img src="{{ Storage::url($evidence->file_path) }}" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center">
                                            <p class="text-[8px] text-white uppercase mb-1 font-bold">{{ str_replace('_', ' ', $evidence->type) }}</p>
                                            <i class="fas fa-search-plus text-white"></i>
                                        </div>
                                        <form action="{{ route('wms.purchase-orders.destroy-evidence', $evidence) }}" method="POST" class="absolute top-1 right-1 z-20" onsubmit="return confirm('Eliminar?')">
                                            @csrf @method('DELETE')
                                            @if(Auth::user()->hasFfPermission('wms.receiving'))
                                            <button type="submit" class="w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"><i class="fas fa-times text-xs"></i></button>
                                            @endif
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            <div x-show="modalOpen" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 z-[60] flex items-center justify-center p-4" 
                 style="display: none;" x-cloak>
                
                <div @click="closeModal()" class="absolute inset-0 bg-[#2c3856]/90 backdrop-blur-sm"></div>
                
                <div class="relative z-10 max-w-4xl w-full">
                    <img :src="modalImage" class="max-w-full max-h-[85vh] rounded-2xl shadow-2xl mx-auto">
                    <button @click="closeModal()" class="absolute -top-10 right-0 text-white hover:text-[#ff9c00] transition-colors p-2">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        function evidenceHandler() {
            return {
                modalOpen: false, 
                modalImage: '',
                
                openModal(url) { 
                    this.modalImage = url; 
                    this.modalOpen = true; 
                    document.body.style.overflow = 'hidden';
                },
                
                closeModal() { 
                    this.modalOpen = false; 
                    document.body.style.overflow = 'auto';
                },
                
                fileInput(name) {
                    return {
                        preview: '',
                        updatePreview(e) {
                            const file = e.target.files[0];
                            if(file) {
                                const reader = new FileReader();
                                reader.onload = (e) => this.preview = e.target.result;
                                reader.readAsDataURL(file);
                            }
                        }
                    }
                },
                multiFileInput(name) {
                    return {
                        fileCount: 0,
                        fileText: 'Seleccionar...',
                        updateText(e) {
                            this.fileCount = e.target.files.length;
                            this.fileText = this.fileCount === 1 ? '1 archivo' : `${this.fileCount} archivos`;
                        }
                    }
                }
            }
        }
    </script>
</x-app-layout>