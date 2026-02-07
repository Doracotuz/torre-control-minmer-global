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
            padding: 0.8rem 0; font-family: 'Montserrat', sans-serif; font-weight: 600; color: #2c3856;
            transition: all 0.3s ease; width: 100%; font-size: 1.5rem;
        }
        .input-arch:focus { border-bottom-color: #2c3856; box-shadow: none; outline: none; }
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
            <div class="absolute bottom-0 left-0 w-[40rem] h-[40rem] bg-[#2c3856]/5 rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-4xl mx-auto px-6 pt-12 relative z-10">
            
            <div class="flex items-center gap-4 mb-12 stagger-enter">
                <a href="{{ route('wms.inventory.index') }}" class="w-12 h-12 rounded-xl border-2 border-gray-200 flex items-center justify-center text-gray-400 hover:border-[#2c3856] hover:text-[#2c3856] transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span class="w-8 h-1 bg-[#2c3856]"></span>
                        <span class="text-xs font-bold text-[#2c3856] uppercase tracking-[0.2em]">Consultas</span>
                    </div>
                    <h1 class="text-4xl font-raleway font-black text-[#2c3856]">
                        INFO <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#2c3856] to-blue-800">UBICACIÓN</span>
                    </h1>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 overflow-hidden relative mb-12 p-10 md:p-12 stagger-enter" style="animation-delay: 0.1s;">
                <form action="{{ route('wms.inventory.location-info.find') }}" method="POST" class="w-full">
                    @csrf
                    <div class="flex flex-col items-center justify-center text-center mb-8">
                        <div class="w-16 h-16 bg-[#f3f4f6] rounded-3xl flex items-center justify-center mb-6 text-[#2c3856]">
                            <i class="fas fa-search-location text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-raleway font-black text-[#2c3856] mb-2">Búsqueda de Ubicación</h3>
                        <p class="text-gray-400 font-medium max-w-sm mx-auto">Ingresa el código de la ubicación (ej. A-01-01-01) para ver su contenido.</p>
                    </div>

                    <div class="flex items-end gap-4 max-w-lg mx-auto">
                        <div class="relative w-full">
                            <input type="text" name="location_code" id="location_code" class="input-arch font-mono text-center uppercase tracking-widest" value="{{ $location->code ?? old('location_code') }}" placeholder="X-XX-XX-XX" required autofocus>
                            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-0 h-0.5 bg-[#2c3856] transition-all duration-300 group-focus-within:w-full"></div>
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

            @if($location)
            <div class="bg-white rounded-[3rem] shadow-2xl shadow-[#2c3856]/10 overflow-hidden stagger-enter" style="animation-delay: 0.2s;">
                
                <div class="bg-[#2c3856] p-10 md:p-12 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-blue-400 rounded-full blur-[100px] opacity-20 -mr-20 -mt-20"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                        <div>
                            <p class="text-blue-300 text-xs font-bold uppercase tracking-[0.2em] mb-2">Ubicación</p>
                            <h2 class="text-5xl md:text-6xl font-mono font-black text-white tracking-tight">{{ $location->code }}</h2>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="px-4 py-2 rounded-full bg-white/10 text-white text-xs font-bold border border-white/20 uppercase tracking-wider">
                                {{ $location->type }}
                            </span>
                            <p class="text-white/60 text-xs">{{ $location->warehouse->name ?? 'Sin Almacén' }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-10 md:p-12 space-y-12">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-100 pb-2">Detalles de Ubicación</h4>
                            <dl class="space-y-4">
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Pasillo</dt>
                                    <dd class="text-lg font-bold text-[#2c3856]">{{ $location->aisle }}</dd>
                                </div>
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Rack</dt>
                                    <dd class="text-lg font-bold text-[#2c3856]">{{ $location->rack }}</dd>
                                </div>
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Nivel</dt>
                                    <dd class="text-lg font-bold text-[#2c3856]">{{ $location->shelf }}</dd>
                                </div>
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Posición</dt>
                                    <dd class="text-lg font-bold text-[#2c3856]">{{ $location->bin }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-100 pb-2">Resumen de Contenido</h4>
                            <dl class="space-y-4">
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Total Tarimas</dt>
                                    <dd class="text-2xl font-black text-[#2c3856]">{{ $pallets->count() }}</dd>
                                </div>
                                <div class="flex justify-between items-end">
                                    <dt class="text-sm font-medium text-gray-500">Total Piezas</dt>
                                    <dd class="text-lg font-bold text-[#2c3856]">{{ number_format($pallets->sum(fn($p) => $p->items->sum('quantity'))) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-100 pb-2">Tarimas en esta Ubicación</h4>
                        <div class="space-y-4">
                            @forelse ($pallets as $pallet)
                            <div class="p-6 rounded-2xl bg-gray-50 border border-gray-100 hover:border-blue-200 transition-all duration-300 group">
                                <div class="flex flex-col md:flex-row justify-between gap-4 mb-4">
                                    <div>
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('wms.inventory.pallet-info.find', ['lpn' => $pallet->lpn]) }}" class="text-xl font-mono font-black text-[#2c3856] hover:text-blue-600 underline decoration-dotted underline-offset-4">
                                                {{ $pallet->lpn }}
                                            </a>
                                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-green-100 text-green-700 border border-green-200">
                                                {{ $pallet->status }}
                                            </span>
                                        </div>
                                        <div class="flex flex-col gap-1 mt-1">
                                            <p class="text-xs text-gray-500">
                                                PO: <span class="font-mono font-bold">{{ $pallet->purchaseOrder->po_number ?? 'N/A' }}</span>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                Cliente: <span class="font-bold text-[#ff9c00]">{{ $pallet->purchaseOrder->area->name ?? 'N/A' }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] uppercase font-bold text-gray-400">Actualizado</p>
                                        <p class="text-xs font-medium text-[#2c3856]">{{ $pallet->updated_at->diffForHumans() }}</p>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    @foreach($pallet->items as $item)
                                    <div class="flex items-center justify-between text-sm py-2 border-t border-gray-100/50">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-box text-gray-300"></i>
                                            <div>
                                                <span class="font-bold text-[#2c3856]">{{ $item->product->sku ?? 'N/A' }}</span>
                                                <span class="text-gray-400 mx-1">·</span>
                                                <span class="text-gray-500 text-xs">{{ Str::limit($item->product->name ?? 'N/A', 30) }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold uppercase">{{ $item->quality->name ?? 'N/A' }}</span>
                                            <span class="font-mono font-bold text-[#2c3856]">{{ number_format($item->quantity) }} pzs</span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                <i class="fas fa-box-open text-4xl text-gray-300 mb-3"></i>
                                <p class="text-gray-400 font-medium">Esta ubicación está vacía.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
