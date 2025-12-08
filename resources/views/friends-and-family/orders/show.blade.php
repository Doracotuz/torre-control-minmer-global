<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="bg-[#F8FAFC] min-h-screen font-sans pb-12 relative rounded-3xl" 
         x-data="{ 
            showRejectModal: false,
            toastVisible: false,
            toastMessage: '',
            copyToClipboard(text) {
                navigator.clipboard.writeText(text);
                this.toastMessage = 'Folio copiado al portapapeles';
                this.toastVisible = true;
                setTimeout(() => this.toastVisible = false, 3000);
            }
         }">

        @php
            $hasBackorder = $movements->contains('is_backorder', true);
            $backorderCount = $movements->where('is_backorder', true)->sum(fn($m) => abs($m->quantity));
        @endphp

        <div x-show="toastVisible" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-2 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="translate-y-2 opacity-0"
             class="fixed bottom-6 right-6 z-50 bg-[#2c3856] text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 border border-white/10 backdrop-blur-md">
            <i class="fas fa-check-circle text-[#ff9c00]"></i>
            <span class="text-sm font-medium" x-text="toastMessage"></span>
        </div>

        <div class="max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 pt-8">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <a href="{{ route('ff.orders.index') }}" class="w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-[#2c3856] hover:border-[#2c3856] flex items-center justify-center transition-all">
                            <i class="fas fa-arrow-left text-xs"></i>
                        </a>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Detalle de Operación</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-4xl font-black text-[#2c3856] tracking-tight">#{{ $header->folio }}</h1>
                        <button @click="copyToClipboard('{{ $header->folio }}')" class="text-slate-300 hover:text-[#ff9c00] transition-colors" title="Copiar">
                            <i class="fas fa-copy text-lg"></i>
                        </button>
                        
                        <span class="px-3 py-1 rounded-full text-xs font-bold border flex items-center gap-2
                            {{ $header->status == 'approved' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 
                              ($header->status == 'rejected' ? 'bg-rose-50 text-rose-600 border-rose-100' : 
                              'bg-amber-50 text-amber-600 border-amber-100') }}">
                            <span class="w-2 h-2 rounded-full {{ $header->status == 'approved' ? 'bg-emerald-500' : ($header->status == 'rejected' ? 'bg-rose-500' : 'bg-amber-500 animate-pulse') }}"></span>
                            {{ match($header->status) { 'pending'=>'Revisión', 'approved'=>'Aprobado', 'rejected'=>'Rechazado', default=>'Desconocido' } }}
                        </span>

                        @if($hasBackorder)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-700 border border-purple-200 flex items-center gap-2 animate-pulse">
                                <i class="fas fa-history"></i> Backorder Activo
                            </span>
                        @endif
                    </div>
                </div>

                @if(Auth::user()->is_area_admin && $header->status === 'pending')
                <div class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
                    <button @click="showRejectModal = true" class="px-5 py-2.5 rounded-xl text-rose-600 font-bold text-sm hover:bg-rose-50 transition-colors">
                        Rechazar
                    </button>
                    <form action="{{ route('ff.orders.approve', $header->folio) }}" method="POST" onsubmit="return confirm('¿Confirmar aprobación?');">
                        @csrf
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#2c3856] text-white font-bold text-sm shadow-lg shadow-blue-900/20 hover:bg-[#1e273d] hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                            Autorizar Pedido
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-white rounded-[2rem] p-8 shadow-[0_2px_20px_rgba(0,0,0,0.02)] border border-slate-100">
                        <div class="relative">
                            <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-50 -translate-y-1/2 rounded-full z-0"></div>
                            
                            <div class="absolute top-1/2 left-0 h-1 -translate-y-1/2 rounded-full z-0 transition-all duration-1000 ease-out 
                                {{ $header->status == 'pending' ? 'w-1/2 bg-amber-400' : ($header->status == 'approved' ? 'w-full bg-emerald-400' : ($header->status == 'rejected' ? 'w-full bg-rose-400' : 'w-0')) }}">
                            </div>

                            <div class="relative z-10 flex justify-between">
                                
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-[#2c3856] text-white flex items-center justify-center shadow-lg shadow-blue-900/20 ring-4 ring-white">
                                        <i class="fas fa-file-signature text-xs"></i>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs font-bold text-[#2c3856] uppercase tracking-wider">Solicitado</p>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $header->created_at->format('d/m H:i') }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center ring-4 ring-white transition-all
                                        {{ $header->status == 'pending' ? 'bg-amber-400 text-white shadow-lg shadow-amber-400/30 scale-110' : 'bg-[#2c3856] text-white' }}">
                                        <i class="fas {{ $header->status == 'pending' ? 'fa-hourglass-half fa-spin-pulse' : 'fa-check' }} text-xs"></i>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs font-bold uppercase tracking-wider {{ $header->status == 'pending' ? 'text-amber-500' : 'text-[#2c3856]' }}">
                                            Revisión
                                        </p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">Validación Admin</p>
                                    </div>
                                </div>

                                <div class="flex flex-col items-center gap-3">
                                    @if($header->status == 'approved')
                                        <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30 ring-4 ring-white scale-110">
                                            <i class="fas fa-check-double text-xs"></i>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Aprobado</p>
                                            <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $header->approved_at ? $header->approved_at->format('d/m H:i') : 'Hoy' }}</p>
                                        </div>
                                    @elseif($header->status == 'rejected')
                                        <div class="w-10 h-10 rounded-full bg-rose-500 text-white flex items-center justify-center shadow-lg shadow-rose-500/30 ring-4 ring-white scale-110">
                                            <i class="fas fa-times text-xs"></i>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-xs font-bold text-rose-600 uppercase tracking-wider">Rechazado</p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">Stock devuelto</p>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-300 flex items-center justify-center ring-4 ring-white">
                                            <i class="fas fa-flag-checkered text-xs"></i>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-xs font-bold text-slate-300 uppercase tracking-wider">Resultado</p>
                                            <p class="text-[10px] text-slate-300 mt-0.5">En espera</p>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>                    
                    
                    @if($hasBackorder)
                    <div class="bg-purple-50 rounded-2xl p-6 border border-purple-100 flex items-start gap-4 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-200 rounded-full blur-[50px] opacity-20 -mr-10 -mt-10"></div>
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0 text-lg">
                            <i class="fas fa-boxes-packing"></i>
                        </div>
                        <div class="relative z-10">
                            <h3 class="text-purple-900 font-bold text-sm uppercase tracking-wide mb-1">Entrega Pendiente (Backorder)</h3>
                            <p class="text-purple-700 text-sm">
                                Este pedido contiene <strong class="text-purple-900">{{ $backorderCount }} unidades</strong> sin stock físico al momento de la venta. 
                                El sistema notificará al vendedor cuando se realice la entrada de almacén correspondiente.
                            </p>
                        </div>
                    </div>
                    @endif

                    <div class="bg-white rounded-[2rem] p-8 shadow-[0_2px_20px_rgba(0,0,0,0.02)] border border-slate-100 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-slate-50 rounded-bl-[100px] -mr-10 -mt-10 transition-transform group-hover:scale-110"></div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10">
                            <div>
                                <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-2">Cliente Solicitante</p>
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#2c3856] flex items-center justify-center font-bold text-lg">
                                        {{ substr($header->client_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-[#2c3856] leading-tight">{{ $header->client_name }}</h3>
                                        <p class="text-sm text-slate-500">{{ $header->company_name }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex gap-8">
                                <div>
                                    <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-2">Fecha Entrega</p>
                                    <p class="text-xl font-bold text-[#2c3856] font-mono">
                                        {{ $header->delivery_date ? $header->delivery_date->format('d M, Y') : 'N/A' }}
                                    </p>
                                    <p class="text-xs font-bold text-[#ff9c00]">
                                        {{ $header->delivery_date ? $header->delivery_date->format('H:i') . ' hrs' : '' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-2">Tipo</p>
                                    <span class="inline-block px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 capitalize">
                                        {{ $header->order_type }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($header->observations)
                        <div class="mt-8 pt-6 border-t border-slate-50">
                            <p class="text-xs font-bold text-slate-400 uppercase mb-2">Notas Operativas</p>
                            <p class="text-sm text-slate-600 italic bg-slate-50 p-4 rounded-xl border border-slate-100">
                                "{{ $header->observations }}"
                            </p>
                        </div>
                        @endif
                    </div>

                    <div class="bg-white rounded-[2rem] shadow-[0_2px_20px_rgba(0,0,0,0.02)] border border-slate-100 overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center">
                            <h3 class="font-bold text-[#2c3856]">Items del Pedido</h3>
                            <span class="text-xs font-bold text-slate-400 bg-slate-50 px-3 py-1 rounded-full">{{ $totalItems }} unidades</span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="text-xs text-slate-400 font-bold bg-slate-50/50 uppercase tracking-wider text-left">
                                    <tr>
                                        <th class="px-8 py-4 rounded-tl-2xl">Producto</th>
                                        <th class="px-4 py-4 text-center">Cant.</th>
                                        <th class="px-4 py-4 text-right">P. Unit</th>
                                        <th class="px-4 py-4 text-center">Desc.</th>
                                        <th class="px-8 py-4 text-right rounded-tr-2xl">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($movements as $item)
                                        @php 
                                            $basePrice = $item->product->unit_price;
                                            $discountPercent = $item->discount_percentage ?? 0;
                                            $discountAmount = 0;
                                            $finalPrice = 0;
                                            $qty = abs($item->quantity);

                                            if ($item->order_type === 'normal') {
                                                $discountAmount = $basePrice * ($discountPercent / 100);
                                                $finalPrice = $basePrice - $discountAmount;
                                            }
                                        @endphp
                                        
                                        <tr class="group transition-colors {{ $item->is_backorder ? 'bg-purple-50/30 hover:bg-purple-50/60' : 'hover:bg-slate-50/80' }}">
                                            <td class="px-8 py-5">
                                                <div class="flex items-center gap-4">
                                                    
                                                    <div class="w-12 h-12 rounded-xl bg-white border border-slate-100 flex items-center justify-center p-1 shadow-sm relative overflow-hidden group-hover:border-slate-300 transition-colors">
                                                        @if($item->product->photo_url)
                                                            <img src="{{ $item->product->photo_url }}" 
                                                                 class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-110 mix-blend-multiply" 
                                                                 alt="Producto">
                                                        @else
                                                            <i class="fas fa-box text-slate-200 text-lg"></i>
                                                        @endif
                                                    </div>

                                                    <div class="flex flex-col">
                                                        <span class="font-bold text-slate-700 text-base">{{ $item->product->description }}</span>
                                                        <div class="flex items-center gap-2 mt-1">
                                                            <span class="text-[10px] font-mono text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">{{ $item->product->sku }}</span>
                                                            
                                                            @if($item->is_backorder)
                                                                <span class="text-[9px] font-bold text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full border border-purple-200 flex items-center gap-1">
                                                                    <i class="fas fa-history text-[8px]"></i> BACKORDER
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-5 text-center">
                                                <span class="font-bold text-slate-700 text-base">{{ $qty }}</span>
                                            </td>
                                            <td class="px-4 py-5 text-right">
                                                <span class="text-slate-400 font-mono text-xs">${{ number_format($basePrice, 2) }}</span>
                                            </td>
                                            <td class="px-4 py-5 text-center">
                                                @if($discountPercent > 0)
                                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 px-2 py-1 rounded-lg">
                                                        -{{ number_format($discountPercent, 0) }}%
                                                    </span>
                                                @else
                                                    <span class="text-slate-200 text-lg">·</span>
                                                @endif
                                            </td>
                                            <td class="px-8 py-5 text-right">
                                                <span class="font-bold text-[#2c3856] font-mono text-base">${{ number_format($finalPrice * $qty, 2) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-6">
                    
                    <div class="bg-[#2c3856] rounded-[2rem] p-8 text-white relative overflow-hidden shadow-xl shadow-blue-900/20">
                        <div class="absolute top-0 right-0 w-48 h-48 bg-[#ff9c00] rounded-full blur-[80px] opacity-20 -mr-16 -mt-16"></div>
                        
                        <p class="text-blue-200 text-xs font-bold uppercase tracking-widest mb-1">Monto Total</p>
                        <h2 class="text-4xl font-black tracking-tight mb-6 font-mono">${{ number_format($totalValue, 2) }}</h2>
                        
                        <div class="space-y-4 relative z-10">
                            <div class="flex justify-between text-sm py-3 border-t border-white/10">
                                <span class="text-blue-200">Subtotal Lista</span>
                                <span class="font-mono">${{ number_format($totalValue + $movements->sum('discount_amount'), 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm py-3 border-t border-white/10">
                                <span class="text-blue-200">Descuentos</span>
                                <span class="font-mono text-[#ff9c00]">-${{ number_format($movements->sum(function($item){ return $item->order_type == 'normal' ? ($item->product->unit_price * ($item->discount_percentage/100) * abs($item->quantity)) : 0; }), 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2rem] p-8 shadow-[0_2px_20px_rgba(0,0,0,0.02)] border border-slate-100">
                        <h3 class="font-bold text-[#2c3856] mb-4 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-rose-500"></i> Destino
                        </h3>
                        <div class="pl-6 border-l-2 border-slate-100 ml-1.5 space-y-4">
                            <div>
                                <p class="text-xs text-slate-400 uppercase font-bold mb-1">Localidad</p>
                                <p class="text-sm font-bold text-slate-700">{{ $header->locality }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase font-bold mb-1">Dirección</p>
                                <p class="text-sm text-slate-600 leading-relaxed">{{ $header->address }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2rem] p-8 shadow-[0_2px_20px_rgba(0,0,0,0.02)] border border-slate-100">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-[#2c3856]">Evidencias</h3>
                            <a href="{{ route('ff.sales.index', ['edit_folio' => $header->folio]) }}" class="text-[10px] font-bold text-blue-500 hover:underline uppercase">Editar / Subir</a>
                        </div>

                        <div class="space-y-3">
                            @php $hasEvidence = false; @endphp
                            @for($i=1; $i<=3; $i++)
                                @if($url = $header->getEvidenceUrl($i))
                                    @php $hasEvidence = true; @endphp
                                    <a href="{{ $url }}" target="_blank" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-blue-50 border border-slate-100 hover:border-blue-100 transition-all group">
                                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-blue-500 shadow-sm group-hover:scale-110 transition-transform">
                                            <i class="fas fa-image"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-xs font-bold text-slate-700 group-hover:text-blue-700">Evidencia {{ $i }}</p>
                                        </div>
                                        <i class="fas fa-external-link-alt text-slate-300 text-xs"></i>
                                    </a>
                                @endif
                            @endfor

                            @if(!$hasEvidence)
                                <div class="text-center py-8 border-2 border-dashed border-slate-100 rounded-xl">
                                    <i class="fas fa-folder-open text-slate-200 text-3xl mb-2"></i>
                                    <p class="text-xs text-slate-400 font-medium">Sin archivos adjuntos</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('ff.sales.index', ['edit_folio' => $header->folio]) }}" 
                       class="block w-full bg-white border border-slate-200 text-slate-600 font-bold py-4 rounded-xl shadow-sm hover:bg-slate-50 hover:text-[#2c3856] hover:border-[#2c3856] transition-all text-sm text-center">
                        <i class="fas fa-pencil-alt mr-2"></i> Editar Pedido
                    </a>

                </div>
            </div>
        </div>

        <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-[#2c3856]/40 backdrop-blur-sm" x-cloak 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md mx-4 relative" @click.away="showRejectModal = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="scale-95 opacity-0 translate-y-4"
                 x-transition:enter-end="scale-100 opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="scale-100 opacity-100 translate-y-0"
                 x-transition:leave-end="scale-95 opacity-0 translate-y-4">
                
                <div class="text-center mb-6">
                    <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4 text-xl">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="text-xl font-bold text-[#2c3856]">Rechazar Pedido</h3>
                    <p class="text-sm text-slate-500 mt-1">El stock reservado será liberado.</p>
                </div>

                <form action="{{ route('ff.orders.reject', $header->folio) }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Razón del rechazo</label>
                        <textarea name="reason" rows="3" class="w-full bg-slate-50 border-slate-200 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm p-4 font-medium resize-none transition-all outline-none" placeholder="Escribe el motivo aquí..." required></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="showRejectModal = false" class="flex-1 py-3 text-slate-600 font-bold text-sm bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Cancelar</button>
                        <button type="submit" class="flex-1 py-3 bg-rose-500 text-white font-bold text-sm hover:bg-rose-600 rounded-xl shadow-lg shadow-rose-200 transition-colors">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>