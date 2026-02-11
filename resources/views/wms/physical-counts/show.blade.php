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
            font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 800;
            padding: 0 0.5rem 0.5rem 0.5rem; text-align: left;
        }
        .nexus-row {
            background: white; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .nexus-row td {
            padding: 1rem 0.5rem; vertical-align: middle; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6;
            background-color: white;
        }
        .nexus-row td:first-child { border-top-left-radius: 1rem; border-bottom-left-radius: 1rem; border-left: 1px solid #f3f4f6; padding-left: 1.5rem; }
        .nexus-row td:last-child { border-top-right-radius: 1rem; border-bottom-right-radius: 1rem; border-right: 1px solid #f3f4f6; padding-right: 1.5rem; }
        .nexus-row:hover { box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.05); z-index: 10; position: relative; }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden" x-data="adjustmentHandler()">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-[50vw] h-full bg-gradient-to-l from-[#f8fafc] to-transparent"></div>
            <div class="absolute bottom-0 left-0 w-[40rem] h-[40rem] bg-[#ff9c00]/5 rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-4 md:px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00]"></span>
                        <span class="text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Control de Auditoría</span>
                    </div>
                    <h1 class="text-3xl md:text-5xl font-raleway font-black text-[#2c3856] leading-none mb-2">
                        {{ $session->name }}
                    </h1>
                    <div class="flex flex-wrap gap-2 text-xs md:text-sm font-medium text-gray-500">
                        <span class="bg-white px-3 py-1 rounded-full shadow-sm border border-gray-100 flex items-center">
                            <i class="fas fa-warehouse mr-1 text-[#ff9c00]"></i> {{ $session->warehouse->name ?? 'N/A' }}
                        </span>
                        @if($session->area)
                        <span class="bg-white px-3 py-1 rounded-full shadow-sm border border-gray-100 flex items-center">
                            <i class="fas fa-briefcase mr-1 text-blue-500"></i> {{ $session->area->name }}
                        </span>
                        @endif
                    </div>
                </div>

                <div class="mt-6 xl:mt-0 flex flex-col md:flex-row gap-3 w-full xl:w-auto">
                    @php
                        $nextTask = $session->tasks->whereIn('status', ['pending'])->first() ?? 
                                    $session->tasks->where('status', 'discrepancy')->filter(fn($t) => $t->records->count() < 3)->first();
                    @endphp
                    
                    @if($nextTask)
                        <a href="{{ route('wms.physical-counts.tasks.perform', $nextTask) }}" class="btn-nexus w-full md:w-auto px-8 py-3 text-sm uppercase tracking-wider shadow-lg shadow-[#2c3856]/20 bg-green-600 hover:bg-green-700">
                            <i class="fas fa-play mr-2"></i> Continuar Conteo
                        </a>
                    @endif

                    @endif

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="btn-ghost w-full md:w-auto px-6 py-3 text-xs uppercase tracking-wider text-center flex items-center gap-2">
                            <i class="fas fa-file-pdf"></i> Documentación <i class="fas fa-chevron-down ml-1"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute top-full right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50 flex flex-col" x-cloak>
                            <a href="{{ route('wms.physical-counts.print-sheet', $session) }}" target="_blank" class="px-4 py-3 text-xs font-bold text-[#2c3856] hover:bg-gray-50 text-left">
                                <i class="fas fa-clipboard-list mr-2 text-blue-500"></i> Hoja de Conteo
                            </a>
                            <a href="{{ route('wms.physical-counts.print-act', $session) }}" target="_blank" class="px-4 py-3 text-xs font-bold text-[#2c3856] hover:bg-gray-50 text-left">
                                <i class="fas fa-file-contract mr-2 text-red-500"></i> Acta de Inventario
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('wms.physical-counts.index') }}" class="btn-ghost w-full md:w-auto px-6 py-3 text-xs uppercase tracking-wider text-center">
                        <i class="fas fa-arrow-left mr-2"></i> Volver
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-xl mb-8 font-bold flex items-center gap-3 stagger-enter text-sm">
                    <i class="fas fa-check-circle text-lg"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl mb-8 font-bold flex items-center gap-3 stagger-enter text-sm">
                    <i class="fas fa-exclamation-circle text-lg"></i>
                    {{ session('error') }}
                </div>
            @endif

            @php
                $total = $session->tasks->count();
                $pending = $session->tasks->where('status', 'pending')->count();
                $discrepancy = $session->tasks->where('status', 'discrepancy')->count();
                $resolved = $session->tasks->where('status', 'resolved')->count();
                $progress = $total > 0 ? (($resolved + $discrepancy) / $total) * 100 : 0;
            @endphp

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 p-6 md:p-8 mb-8 stagger-enter" style="animation-delay: 0.2s;">
                <div class="flex justify-between items-end mb-4">
                    <h3 class="text-sm md:text-lg font-raleway font-black text-[#2c3856]">Progreso de la Sesión</h3>
                    <span class="text-xl md:text-2xl font-bold text-[#ff9c00]">{{ round($progress) }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2 md:h-3 mb-6 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#2c3856] to-[#4f6494] h-2 md:h-3 rounded-full transition-all duration-1000" style="width: {{ $progress }}%"></div>
                </div>
                <div class="grid grid-cols-4 gap-2 md:gap-6 text-center">
                    <div class="p-2 md:p-4 bg-gray-50 rounded-xl md:rounded-2xl">
                        <p class="text-[8px] md:text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total</p>
                        <p class="text-lg md:text-3xl font-black text-[#2c3856]">{{ $total }}</p>
                    </div>
                    <div class="p-2 md:p-4 bg-blue-50 rounded-xl md:rounded-2xl">
                        <p class="text-[8px] md:text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">Pend</p>
                        <p class="text-lg md:text-3xl font-black text-blue-600">{{ $pending }}</p>
                    </div>
                    <div class="p-2 md:p-4 bg-red-50 rounded-xl md:rounded-2xl">
                        <p class="text-[8px] md:text-[10px] font-bold text-red-400 uppercase tracking-widest mb-1">Dif</p>
                        <p class="text-lg md:text-3xl font-black text-red-600">{{ $discrepancy }}</p>
                    </div>
                    <div class="p-2 md:p-4 bg-green-50 rounded-xl md:rounded-2xl">
                        <p class="text-[8px] md:text-[10px] font-bold text-green-400 uppercase tracking-widest mb-1">OK</p>
                        <p class="text-lg md:text-3xl font-black text-green-600">{{ $resolved }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 p-6 md:p-8 stagger-enter" style="animation-delay: 0.3s;" x-data="{ filter: 'all' }">
                
                <div class="flex flex-wrap gap-2 mb-6 overflow-x-auto pb-2">
                    <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-[#2c3856] text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'" class="px-4 py-2 rounded-xl text-[10px] md:text-xs font-bold uppercase tracking-widest transition-all whitespace-nowrap">Todas</button>
                    <button @click="filter = 'pending'" :class="filter === 'pending' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'" class="px-4 py-2 rounded-xl text-[10px] md:text-xs font-bold uppercase tracking-widest transition-all whitespace-nowrap">Pendientes</button>
                    <button @click="filter = 'discrepancy'" :class="filter === 'discrepancy' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'" class="px-4 py-2 rounded-xl text-[10px] md:text-xs font-bold uppercase tracking-widest transition-all whitespace-nowrap">Discrepancias</button>
                    <button @click="filter = 'resolved'" :class="filter === 'resolved' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'" class="px-4 py-2 rounded-xl text-[10px] md:text-xs font-bold uppercase tracking-widest transition-all whitespace-nowrap">Resueltas</button>
                </div>
                
                <div class="grid grid-cols-1 gap-4 md:hidden">
                    @foreach ($session->tasks as $task)
                        @php
                            $lastRecord = $task->records->last();
                            $diff = $lastRecord ? ($lastRecord->counted_quantity - $task->expected_quantity) : 0;
                        @endphp
                        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm relative overflow-hidden" x-show="filter === 'all' || filter === '{{ $task->status }}'" x-transition>
                            <div class="absolute top-0 right-0 p-3">
                                <span class="px-2 py-1 rounded text-[9px] font-bold uppercase tracking-wide 
                                    @if($task->status == 'discrepancy') bg-red-100 text-red-700 
                                    @elseif($task->status == 'resolved') bg-green-100 text-green-700 
                                    @else bg-gray-100 text-gray-600 @endif">
                                    {{ $task->status_in_spanish }}
                                </span>
                            </div>

                            <div class="pr-20 mb-2">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Ubicación</p>
                                <p class="text-xl font-mono font-black text-[#2c3856]">
                                    {{ $task->location ? "{$task->location->aisle}-{$task->location->rack}-{$task->location->shelf}-{$task->location->bin}" : 'N/A' }}
                                </p>
                            </div>

                            <div class="mb-3 bg-gray-50 p-3 rounded-xl">
                                <p class="text-xs font-bold text-[#2c3856]">{{ $task->product->name }}</p>
                                <p class="text-[10px] text-gray-400 font-mono">{{ $task->product->sku }}</p>
                                <p class="text-[10px] font-mono text-[#ff9c00] font-bold mt-1">{{ $task->pallet->lpn ?? 'N/A' }}</p>
                            </div>

                            <div class="flex justify-between items-center border-t border-gray-100 pt-3">
                                <div>
                                    <p class="text-[9px] text-gray-400 font-bold uppercase">Sistema</p>
                                    <p class="font-black text-gray-600">{{ $task->expected_quantity }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[9px] text-gray-400 font-bold uppercase">Último</p>
                                    <p class="font-black text-[#2c3856]">{{ $lastRecord ? $lastRecord->counted_quantity : '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] text-gray-400 font-bold uppercase">Dif</p>
                                    <p class="font-black @if($lastRecord && $diff != 0) text-red-600 @elseif($lastRecord) text-green-600 @else text-gray-400 @endif">
                                        {{ $lastRecord ? ($diff > 0 ? '+'.$diff : $diff) : '-' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-100">
                                @if ($task->status == 'pending' || ($task->status == 'discrepancy' && $task->records->count() < 3))
                                    <a href="{{ route('wms.physical-counts.tasks.perform', $task) }}" class="btn-nexus w-full py-3 text-[10px] uppercase tracking-widest shadow-sm">
                                        {{ $task->records->count() > 0 ? 'Re-contar' : 'Contar' }}
                                    </a>
                                @elseif ($task->status == 'discrepancy')
                                    <button @click="openModal({{ $task->id }})" type="button" class="btn-ghost w-full py-3 text-[10px] uppercase tracking-widest text-red-600 border-red-200 hover:bg-red-50 hover:border-red-300">Resolver Discrepancia</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="nexus-table">
                        <thead>
                            <tr>
                                <th>Estatus</th>
                                <th>Ubicación</th>
                                <th>LPN</th>
                                <th>Producto</th>
                                <th class="text-center bg-gray-50 rounded-l-lg text-[#2c3856]">Sistema</th>
                                <th class="text-center text-blue-600">C1</th>
                                <th class="text-center text-purple-600">C2</th>
                                <th class="text-center text-orange-600">C3</th>
                                <th class="text-center bg-gray-50 rounded-r-lg">Dif</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($session->tasks as $task)
                                @php
                                    $c1 = $task->records->firstWhere('count_number', 1);
                                    $c2 = $task->records->firstWhere('count_number', 2);
                                    $c3 = $task->records->firstWhere('count_number', 3);
                                    $lastRecord = $task->records->last();
                                    $diff = $lastRecord ? ($lastRecord->counted_quantity - $task->expected_quantity) : 0;
                                @endphp
                                <tr class="nexus-row" x-show="filter === 'all' || filter === '{{ $task->status }}'" x-transition>
                                    <td>
                                        <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide 
                                            @if($task->status == 'discrepancy') bg-red-100 text-red-700 
                                            @elseif($task->status == 'resolved') bg-green-100 text-green-700 
                                            @else bg-gray-100 text-gray-600 @endif">
                                            {{ $task->status_in_spanish }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="font-mono font-bold text-[#2c3856]">
                                            {{ $task->location ? "{$task->location->aisle}-{$task->location->rack}-{$task->location->shelf}-{$task->location->bin}" : 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="font-mono text-[#ff9c00] font-bold text-xs">{{ $task->pallet->lpn ?? 'N/A' }}</td>
                                    <td>
                                        <div class="text-xs font-bold text-[#2c3856]">{{ Str::limit($task->product->name, 25) }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $task->product->sku }}</div>
                                    </td>
                                    <td class="text-center font-black text-gray-600 bg-gray-50 rounded-l-lg">{{ $task->expected_quantity }}</td>
                                    
                                    <td class="text-center font-bold text-blue-600">{{ $c1 ? $c1->counted_quantity : '-' }}</td>
                                    <td class="text-center font-bold text-purple-600">{{ $c2 ? $c2->counted_quantity : '-' }}</td>
                                    <td class="text-center font-bold text-orange-600">{{ $c3 ? $c3->counted_quantity : '-' }}</td>
                                    
                                    <td class="text-center font-black text-sm bg-gray-50 rounded-r-lg @if($lastRecord && $diff != 0) text-red-600 @elseif($lastRecord) text-green-600 @endif">
                                        {{ $lastRecord ? ($diff > 0 ? '+'.$diff : $diff) : '-' }}
                                    </td>
                                    
                                    <td class="text-right">
                                        @if ($task->status == 'pending' || ($task->status == 'discrepancy' && $task->records->count() < 3))
                                            <a href="{{ route('wms.physical-counts.tasks.perform', $task) }}" class="btn-nexus px-4 py-2 text-[10px] uppercase tracking-widest shadow-sm">
                                                {{ $task->records->count() > 0 ? 'Re-contar' : 'Contar' }}
                                            </a>
                                        @elseif ($task->status == 'discrepancy')
                                            <button @click="openModal({{ $task->id }})" type="button" class="btn-ghost px-4 py-2 text-[10px] uppercase tracking-widest text-red-600 border-red-200 hover:bg-red-50 hover:border-red-300">Ajustar</button>
                                        @elseif ($task->status == 'resolved')
                                            <span class="text-green-500 text-lg"><i class="fas fa-check-circle"></i></span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#2c3856]/80 backdrop-blur-sm" style="display: none;" x-cloak>
            <div @click.away="resetModal()" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden" x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div>
                        <h2 class="text-2xl font-raleway font-black text-[#2c3856]">Resolver Discrepancia</h2>
                        <p class="text-xs text-gray-500 mt-1">Confirma el ajuste de inventario para esta ubicación.</p>
                    </div>
                    <button @click="resetModal()" class="w-10 h-10 rounded-full bg-white text-gray-400 hover:text-red-500 shadow-sm flex items-center justify-center transition-colors text-xl">&times;</button>
                </div>
                
                <div class="p-8 overflow-y-auto">
                    <div x-show="isLoading" class="text-center py-10">
                        <i class="fas fa-circle-notch fa-spin text-4xl text-[#ff9c00]"></i>
                        <p class="mt-4 text-sm font-bold text-gray-400 uppercase tracking-widest">Analizando LPNs...</p>
                    </div>
                    
                    <div x-show="!isLoading && !selectedLpnItem">
                        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 mb-6 flex gap-3 items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mt-1"></i>
                            <div>
                                <p class="text-sm font-bold text-yellow-800">Múltiples Tarimas Detectadas</p>
                                <p class="text-xs text-yellow-700 mt-1">Hay más de una tarima con este producto en la ubicación. Selecciona a cual aplicar el ajuste.</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <template x-for="item in candidateLpns" :key="item.id">
                                <button @click="selectLpnItem(item)" class="w-full text-left p-5 border border-gray-100 rounded-2xl hover:border-[#ff9c00] hover:shadow-md transition-all group bg-white">
                                    <div class="flex justify-between items-center mb-2">
                                        <p class="font-mono font-black text-xl text-[#2c3856] group-hover:text-[#ff9c00] transition-colors" x-text="item.pallet.lpn"></p>
                                        <div class="text-right">
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Actual</p>
                                            <p class="font-black text-lg text-[#2c3856]" x-text="item.quantity"></p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 text-[10px] uppercase font-bold text-gray-500">
                                        <span class="bg-gray-100 px-2 py-1 rounded" x-text="item.quality.name"></span>
                                        <span class="bg-gray-100 px-2 py-1 rounded" x-text="item.pallet.purchase_order ? item.pallet.purchase_order.pedimento_a4 : 'S/P'"></span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <template x-if="selectedLpnItem">   
                        <div x-transition>
                            <form :action="`/wms/physical-counts/tasks/${taskToAdjust.id}/adjust`" method="POST">
                                @csrf
                                <div class="mb-6 bg-blue-50 p-6 rounded-2xl border border-blue-100">
                                    <div class="flex justify-between items-center mb-4">
                                        <p class="text-xs font-bold text-blue-400 uppercase tracking-widest">Ajustando LPN</p>
                                        <p class="font-mono font-black text-xl text-[#2c3856]" x-text="selectedLpnItem.pallet.lpn"></p>
                                    </div>
                                    <div class="flex justify-between items-center pt-4 border-t border-blue-100">
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">Cantidad Sistema</p>
                                            <p class="font-bold text-lg text-gray-400 line-through" x-text="selectedLpnItem.quantity"></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-blue-600 font-bold uppercase tracking-widest mb-1">Nueva Cantidad</p>
                                            <p class="text-4xl font-black text-blue-600" x-text="adjustmentAmount"></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <input type="hidden" name="pallet_item_id" :value="selectedLpnItem.id">

                                <div class="mt-4">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Motivo del Ajuste</label>
                                    <textarea name="reason" rows="3" class="input-arch text-sm resize-none" required>Ajuste por discrepancia en Conteo Cíclico.</textarea>
                                </div>
                                <div class="mt-8 flex justify-end gap-4">
                                    <button type="button" @click="selectedLpnItem = null" class="btn-ghost px-6 py-3 text-xs uppercase tracking-widest">Volver</button>
                                    <button type="submit" class="btn-nexus px-8 py-3 text-xs uppercase tracking-widest bg-red-600 hover:bg-red-700 shadow-lg shadow-red-200">Confirmar Ajuste</button>
                                </div>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        function adjustmentHandler() {
            return {
                isModalOpen: false, isLoading: false, candidateLpns: [],
                selectedLpnItem: null, taskToAdjust: null,
                tasks: @json($session->tasks->keyBy('id')),
                adjustmentAmount: 0,

                async openModal(taskId) {
                    this.resetModal();
                    this.isModalOpen = true;
                    this.isLoading = true;
                    this.taskToAdjust = this.tasks[taskId];
                    
                    try {
                        const response = await fetch(`/wms/physical-counts/tasks/${taskId}/candidate-lpns`);
                        const data = await response.json();
                        if (!response.ok) throw new Error('Error al cargar LPNs.');
                        
                        this.candidateLpns = data;
                        if (data.length === 1) {
                            this.selectLpnItem(data[0]);
                        } else if (data.length === 0) {
                             throw new Error('Error: No se encontró LPN para ajustar.');
                        }
                    } catch (error) {
                        alert(error.message);
                        this.isModalOpen = false;
                    } finally {
                        this.isLoading = false;
                    }
                },
                selectLpnItem(item) {
                    this.selectedLpnItem = item;
                    this.calculateAdjustment();
                },
                calculateAdjustment() {
                    if (!this.taskToAdjust || !this.selectedLpnItem) return;
                    const totalCounted = this.taskToAdjust.records[this.taskToAdjust.records.length - 1].counted_quantity;
                    
                    let otherPalletsTotal = 0;
                    this.candidateLpns.forEach(candidate => {
                        if (candidate.id !== this.selectedLpnItem.id) {
                            otherPalletsTotal += candidate.quantity;
                        }
                    });
                    
                    this.adjustmentAmount = totalCounted - otherPalletsTotal;
                    
                    if(this.adjustmentAmount < 0) this.adjustmentAmount = 0;
                },
                resetModal() {
                    this.isModalOpen = false; this.isLoading = false; this.candidateLpns = [];
                    this.selectedLpnItem = null; this.taskToAdjust = null; this.adjustmentAmount = 0;
                }
            }
        }
    </script>
</x-app-layout>