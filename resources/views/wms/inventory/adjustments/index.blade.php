<x-app-layout>
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
        .input-arch-select { background-image: none; cursor: pointer; padding-right: 1.5rem; }

        .nexus-table { width: 100%; border-collapse: separate; border-spacing: 0 0.8rem; }
        .nexus-table thead th {
            font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: #9ca3af; font-weight: 800;
            padding: 0 1.5rem 1rem 1.5rem; text-align: left;
        }
        .nexus-row {
            background: white; transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .nexus-row td {
            padding: 1rem 1.5rem; vertical-align: middle; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6;
        }
        .nexus-row td:first-child { border-top-left-radius: 1rem; border-bottom-left-radius: 1rem; border-left: 1px solid #f3f4f6; }
        .nexus-row td:last-child { border-top-right-radius: 1rem; border-bottom-right-radius: 1rem; border-right: 1px solid #f3f4f6; }
        .nexus-row:hover { transform: scale(1.002); box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.05); z-index: 10; position: relative; }

        .btn-nexus { background: #2c3856; color: white; border-radius: 0.8rem; font-weight: 700; transition: all 0.2s; }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-1px); }
        
        .badge-arch { 
            font-size: 0.65rem; font-weight: 800; text-transform: uppercase; padding: 0.25rem 0.75rem; 
            border-radius: 9999px; letter-spacing: 0.05em; display: inline-block;
        }
        .badge-blue { background: #eff6ff; color: #3b82f6; }
        .badge-green { background: #f0fdf4; color: #22c55e; }
        .badge-red { background: #fef2f2; color: #ef4444; }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 left-0 w-full h-full bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10"></div>
            <div class="absolute top-[-20%] right-[-10%] w-[50rem] h-[50rem] bg-gradient-to-b from-[#2c3856]/5 to-transparent rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00]"></span>
                        <span class="text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Auditoría</span>
                    </div>
                    <h1 class="text-5xl md:text-6xl font-raleway font-black text-[#2c3856] leading-none">
                        LOG DE <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">AJUSTES</span>
                    </h1>
                </div>

                <div class="flex flex-wrap gap-3 mt-6 xl:mt-0 items-center">
                    <div class="bg-white p-1.5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-1">
                        <a href="{{ route('wms.inventory.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-gray-50 text-gray-400 hover:text-[#2c3856] transition-colors" title="Volver al Inventario">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-6 border border-gray-100 shadow-lg mb-8 stagger-enter" style="animation-delay: 0.2s;">
                <form action="{{ route('wms.inventory.adjustments.log') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                        <div>
                            <label class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Almacén</label>
                            <select name="warehouse_id" onchange="this.form.submit()" class="input-arch input-arch-select text-sm">
                                <option value="">Global</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-4">
                            <a href="{{ route('wms.inventory.adjustments.log') }}" class="flex items-center justify-center w-full py-2 text-[10px] font-bold text-gray-400 hover:text-red-500 uppercase tracking-widest transition-colors border-b border-transparent hover:border-red-200">
                                <i class="fas fa-undo mr-1"></i> Limpiar Filtros
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto pb-12 stagger-enter" style="animation-delay: 0.3s;">
                <table class="nexus-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Origen</th>
                            <th>Referencia</th>
                            <th>Producto</th>
                            <th class="text-center">Antes</th>
                            <th class="text-center">Después</th>
                            <th class="text-center">Dif</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($adjustments as $adjustment)
                            <tr class="nexus-row group">
                                <td class="text-sm font-bold text-gray-600">
                                    {{ $adjustment->created_at->format('d/m/Y') }} <br>
                                    <span class="text-[10px] text-gray-400 font-normal">{{ $adjustment->created_at->format('h:i A') }}</span>
                                </td>
                                
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500">
                                            {{ substr($adjustment->user->name ?? 'S', 0, 1) }}
                                        </div>
                                        <span class="text-xs font-bold text-[#2c3856]">{{ $adjustment->user->name ?? 'Sistema' }}</span>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge-arch bg-blue-50 text-blue-600 text-[9px]">{{ $adjustment->source ?? 'N/A' }}</span>
                                </td>

                                <td>
                                    @if ($adjustment->palletItem)
                                        <span class="font-mono text-xs font-bold text-[#2c3856]">{{ $adjustment->palletItem->pallet->lpn ?? 'N/A' }}</span>
                                    @else
                                        <span class="font-mono text-xs text-gray-400 italic">{{ $adjustment->location->code ?? 'N/A' }}</span>
                                    @endif
                                </td>

                                <td>
                                    @php $product = $adjustment->palletItem->product ?? $adjustment->product; @endphp
                                    @if ($product)
                                        <div class="flex flex-col">
                                            <span class="font-bold text-xs text-gray-700">{{ $product->name }}</span>
                                            <span class="font-mono text-[10px] text-gray-400">{{ $product->sku }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-red-400 italic">No Encontrado</span>
                                    @endif
                                </td>

                                <td class="text-center text-sm font-medium text-gray-400">
                                    {{ number_format($adjustment->quantity_before) }}
                                </td>

                                <td class="text-center">
                                    <span class="font-black text-lg text-[#2c3856]">{{ number_format($adjustment->quantity_after) }}</span>
                                </td>

                                <td class="text-center">
                                    <span class="font-bold text-sm {{ $adjustment->quantity_difference >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                        {{ $adjustment->quantity_difference > 0 ? '+' : '' }}{{ number_format($adjustment->quantity_difference) }}
                                    </span>
                                </td>

                                <td class="max-w-xs">
                                    <p class="text-xs text-gray-500 truncate" title="{{ $adjustment->reason }}">
                                        {{ $adjustment->reason }}
                                    </p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-12">
                                    <div class="inline-block p-4 rounded-full bg-gray-50 mb-3">
                                        <i class="fas fa-clipboard-list text-gray-300 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-bold text-sm">No se han registrado movimientos de ajuste.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pb-20">
                {{ $adjustments->appends(request()->query())->links() }}
            </div>

        </div>
    </div>
</x-app-layout>