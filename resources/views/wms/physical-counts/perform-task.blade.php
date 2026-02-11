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
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden flex items-center justify-center">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 left-0 w-[50vw] h-full bg-gradient-to-r from-[#f8fafc] to-transparent"></div>
            <div class="absolute top-[-10%] right-[-10%] w-[30rem] h-[30rem] bg-[#2c3856]/5 rounded-full blur-[100px]"></div>
        </div>

        <div class="max-w-lg w-full px-6 relative z-10 stagger-enter">
            
            <div class="text-center mb-8">
                <div class="flex justify-center items-center gap-3 mb-4">
                    <span class="inline-flex items-center px-3 py-1 bg-green-50 text-green-700 border border-green-200 rounded-full text-[10px] font-bold uppercase tracking-widest">
                        <i class="fas fa-circle text-[6px] mr-2 animate-pulse"></i> Tarea Activa
                    </span>
                    
                    @php
                        $countNumber = $task->records->count() + 1;
                        $countLabel = match($countNumber) {
                            1 => '1er Conteo',
                            2 => '2do Conteo',
                            3 => '3er Conteo',
                            default => $countNumber . '° Conteo'
                        };
                        
                        $badgeColor = match($countNumber) {
                            1 => 'from-blue-500 to-blue-600 shadow-blue-500/30',
                            2 => 'from-orange-400 to-orange-600 shadow-orange-500/30',
                            default => 'from-red-500 to-red-700 shadow-red-500/30'
                        };
                    @endphp
                    
                    <span class="inline-flex items-center px-4 py-1 bg-gradient-to-r {{ $badgeColor }} text-white rounded-full text-xs font-black uppercase tracking-widest shadow-lg transform hover:scale-105 transition-transform duration-300">
                        <i class="fas fa-layer-group mr-2 text-[10px] opacity-80"></i>
                        {{ $countLabel }}
                    </span>
                </div>
                
                <h1 class="text-4xl font-raleway font-black text-[#2c3856] leading-tight">
                    Conteo <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">Físico</span>
                </h1>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-[#2c3856]/10 border border-gray-100 overflow-hidden">
                <form action="{{ route('wms.physical-counts.tasks.record', $task) }}" method="POST">
                    @csrf
                    
                    <div class="bg-[#2c3856] p-8 text-center text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-[#ff9c00] rounded-full blur-3xl opacity-20 -mr-10 -mt-10"></div>
                        <p class="text-[10px] font-bold opacity-60 uppercase tracking-widest mb-1">Ubicación</p>
                        <p class="text-4xl md:text-5xl font-mono font-black tracking-tight relative z-10 break-all">
                            {{ $task->location->aisle }}-{{ $task->location->rack }}-{{ $task->location->shelf }}-{{ $task->location->bin }}
                        </p>
                    </div>
                    
                    <div class="p-8 space-y-8">
                        @if(session('success'))
                            <div class="bg-green-50 text-green-700 p-3 rounded-xl text-center text-sm font-bold">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="text-center">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">LPN (Tarima)</p>
                            <p class="text-xl md:text-2xl font-mono font-bold text-[#ff9c00]">
                                {{ $task->pallet->lpn ?? 'N/A' }}
                            </p>
                            @if($task->pallet && $task->pallet->purchaseOrder && $task->pallet->purchaseOrder->area)
                                <p class="text-[10px] font-bold text-[#2c3856] mt-1 bg-blue-50 inline-block px-2 py-0.5 rounded">
                                    {{ $task->pallet->purchaseOrder->area->name }}
                                </p>
                            @endif
                        </div>

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 text-center">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Producto</p>
                            <p class="font-bold text-lg text-[#2c3856] leading-tight mb-1">{{ $task->product->name }}</p>
                            <p class="font-mono text-sm text-gray-500">{{ $task->product->sku }}</p>
                        </div>

                        <div>
                            <label for="counted_quantity" class="block text-center text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">
                                Cantidad Contada
                            </label>
                            <input type="number" name="counted_quantity" id="counted_quantity"
                                   min="0" required autofocus
                                   pattern="[0-9]*" inputmode="numeric"
                                   class="block w-full text-center text-6xl font-black text-[#2c3856] border-b-4 border-gray-200 focus:border-[#ff9c00] focus:ring-0 bg-transparent py-4 transition-colors placeholder-gray-200"
                                   placeholder="0">
                        </div>
                    </div>

                    <div class="bg-gray-50 px-8 py-6 grid grid-cols-2 gap-4 border-t border-gray-100">
                        <a href="{{ route('wms.physical-counts.show', $task->physical_count_session_id) }}" 
                           class="btn-ghost w-full py-4 text-xs uppercase tracking-widest text-center flex items-center justify-center">
                            Cancelar
                        </a>
                        <button type="submit" class="btn-nexus w-full py-4 text-xs uppercase tracking-widest">
                            <i class="fas fa-check mr-2"></i> Confirmar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>