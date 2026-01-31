<x-app-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .stagger-enter { opacity: 0; transform: translateY(20px); animation: enterUp 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes enterUp { to { opacity: 1; transform: translateY(0); } }
        
        .input-arch {
            background: transparent; border: none; border-bottom: 2px solid #e5e7eb; border-radius: 0;
            padding: 0.8rem 0; font-family: 'Montserrat', sans-serif; font-weight: 600; color: #2c3856;
            transition: all 0.3s ease; width: 100%; font-size: 1.5rem;
        }
        .input-arch:focus { border-bottom-color: #ff9c00; box-shadow: none; outline: none; }
        .input-arch::placeholder { color: #d1d5db; font-weight: 400; font-size: 1.1rem; }

        .btn-nexus { 
            background: #2c3856; color: white; border-radius: 1rem; font-weight: 700;
            transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(44, 56, 86, 0.2); }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-[50vw] h-full bg-gradient-to-l from-[#f8fafc] to-transparent"></div>
            <div class="absolute bottom-0 left-0 w-[40rem] h-[40rem] bg-[#ff9c00]/5 rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-4xl mx-auto px-6 pt-12 relative z-10">
            
            <div class="flex items-center gap-4 mb-12 stagger-enter">
                <a href="{{ route('wms.inventory.index') }}" class="w-12 h-12 rounded-xl border-2 border-gray-200 flex items-center justify-center text-gray-400 hover:border-[#ff9c00] hover:text-[#ff9c00] transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span class="w-8 h-1 bg-[#ff9c00]"></span>
                        <span class="text-xs font-bold text-[#2c3856] uppercase tracking-[0.2em]">Consultas</span>
                    </div>
                    <h1 class="text-4xl font-raleway font-black text-[#2c3856]">
                        INFO <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">LPN</span>
                    </h1>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 overflow-hidden relative mb-12 p-10 md:p-12 stagger-enter" style="animation-delay: 0.1s;">
                <form action="{{ route('wms.inventory.pallet-info.find') }}" method="POST" class="w-full">
                    @csrf
                    <div class="flex flex-col items-center justify-center text-center mb-8">
                        <div class="w-16 h-16 bg-[#f3f4f6] rounded-3xl flex items-center justify-center mb-6 text-[#2c3856]">
                            <i class="fas fa-search text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-raleway font-black text-[#2c3856] mb-2">Búsqueda Rápida</h3>
                        <p class="text-gray-400 font-medium max-w-sm mx-auto">Ingresa el identificador único de la tarima (LPN) para ver su detalle completo.</p>
                    </div>

                    <div class="flex items-end gap-4 max-w-lg mx-auto">
                        <div class="relative w-full">
                            <input type="text" name="lpn" id="lpn" class="input-arch font-mono text-center uppercase tracking-widest" value="{{ $pallet->lpn ?? old('lpn') }}" placeholder="LPN-..." required autofocus>
                            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-0 h-0.5 bg-[#ff9c00] transition-all duration-300 group-focus-within:w-full"></div>
                        </div>
                        <button type="submit" class="btn-nexus h-14 px-8 shadow-lg shadow-[#2c3856]/20">
                            BUSCAR
                        </button>
                    </div>
                </form>
            </div>

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-600 p-6 rounded-r-xl mb-8 font-bold flex items-center gap-3 stagger-enter">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if($pallet)
            <div class="bg-white rounded-[3rem] shadow-2xl shadow-[#2c3856]/10 overflow-hidden stagger-enter" style="animation-delay: 0.2s;">
                
                <div class="bg-[#2c3856] p-10 md:p-12 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-[#ff9c00] rounded-full blur-[100px] opacity-20 -mr-20 -mt-20"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                        <div>
                            <p class="text-[#ff9c00] text-xs font-bold uppercase tracking-[0.2em] mb-2">License Plate Number</p>
                            <h2 class="text-5xl md:text-6xl font-mono font-black text-white tracking-tight">{{ $pallet->lpn }}</h2>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="px-4 py-2 rounded-full bg-white/10 text-white text-xs font-bold border border-white/20 uppercase tracking-wider">
                                {{ $pallet->status }}
                            </span>
                            <p class="text-white/60 text-xs">{{ $pallet->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-10 md:p-12 space-y-12">
                    
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-100 pb-2">Contenido</h4>
                        <div class="space-y-3">
                            @forelse ($pallet->items as $item)
                            <div class="flex flex-col md:flex-row md:items-center justify-between p-5 rounded-2xl bg-gray-50 border border-gray-100 hover:border-blue-100 transition-colors">
                                <div class="flex items-center gap-4 mb-4 md:mb-0">
                                    <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-[#2c3856] font-bold text-lg shadow-sm border border-gray-100">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#2c3856] text-lg">{{ $item->product->name ?? 'N/A' }}</p>
                                        <p class="text-xs font-mono text-gray-500">{{ $item->product->sku ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-8 pl-16 md:pl-0">
                                    <div class="text-right">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase">Calidad</p>
                                        <p class="font-bold text-[#2c3856]">{{ $item->quality->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase">Cantidad</p>
                                        <p class="text-3xl font-black text-[#2c3856]">{{ number_format($item->quantity) }}</p>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-8 text-gray-400 italic">Tarima vacía.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                        
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-100 pb-2">Ubicación & Control</h4>
                            <dl class="space-y-4">
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Ubicación Actual</dt>
                                    <dd class="text-lg font-bold text-[#2c3856]">
                                        @if($pallet->location)
                                            {{ $pallet->location->aisle }}-{{ $pallet->location->rack }}-{{ $pallet->location->shelf }}-{{ $pallet->location->bin }}
                                        @else
                                            <span class="text-red-400">N/A</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Tipo Ubicación</dt>
                                    <dd class="font-medium text-[#2c3856] bg-gray-100 px-2 py-0.5 rounded text-xs uppercase">{{ $pallet->location->type ?? 'N/A' }}</dd>
                                </div>
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Responsable</dt>
                                    <dd class="font-medium text-[#2c3856]">{{ $pallet->user->name ?? 'N/A' }}</dd>
                                </div>
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Última Actividad</dt>
                                    <dd class="font-medium text-[#2c3856]">{{ $pallet->updated_at->format('d/m/Y h:i A') }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-100 pb-2">Datos de Origen (Inbound)</h4>
                            <dl class="space-y-4">
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Orden de Compra</dt>
                                    <dd class="text-lg font-mono font-bold text-[#2c3856]">{{ $pallet->purchaseOrder->po_number ?? 'N/A' }}</dd>
                                </div>
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Contenedor</dt>
                                    <dd class="font-mono text-[#2c3856]">{{ $pallet->purchaseOrder->container_number ?? 'N/A' }}</dd>
                                </div>
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Pedimento A4</dt>
                                    <dd class="font-mono text-[#2c3856]">{{ $pallet->purchaseOrder->pedimento_a4 ?? 'N/A' }}</dd>
                                </div>
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Pedimento G1</dt>
                                    <dd class="font-mono text-[#2c3856]">{{ $pallet->purchaseOrder->pedimento_g1 ?? 'N/A' }}</dd>
                                </div>
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Transporte</dt>
                                    <dd class="text-[#2c3856] text-right text-xs">
                                        <div class="font-bold">{{ $pallet->purchaseOrder->latestArrival->truck_plate ?? 'Placas N/A' }}</div>
                                        <div>{{ $pallet->purchaseOrder->operator_name ?? 'Operador N/A' }}</div>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>