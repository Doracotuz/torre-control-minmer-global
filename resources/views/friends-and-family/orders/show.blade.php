<x-app-layout>
    <x-slot name="header"></x-slot>
    <div class="bg-[#E8ECF7] min-h-screen py-8 font-sans" x-data="{ showRejectModal: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('ff.orders.index') }}" class="text-gray-500 hover:text-[#2c3856] font-bold text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al listado
                </a>
                
                @if(Auth::user()->is_area_admin && $header->status === 'pending')
                    <div class="flex gap-3">
                        <button @click="showRejectModal = true" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow text-sm transition-colors">
                            <i class="fas fa-times mr-2"></i> Rechazar
                        </button>
                        <form action="{{ route('ff.orders.approve', $header->folio) }}" method="POST" onsubmit="return confirm('¿Autorizar este pedido?');">
                            @csrf
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg shadow text-sm transition-colors">
                                <i class="fas fa-check mr-2"></i> Aprobar Pedido
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
                        
                        <div class="absolute top-0 right-0 p-4">
                            @if($header->status == 'pending')
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">Pendiente de Aprobación</span>
                            @elseif($header->status == 'approved')
                                <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">
                                    <i class="fas fa-check-circle mr-1"></i> Aprobado
                                </span>
                            @elseif($header->status == 'rejected')
                                <span class="bg-red-100 text-red-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">
                                    <i class="fas fa-ban mr-1"></i> Rechazado
                                </span>
                            @endif
                        </div>

                        <h2 class="text-2xl font-bold text-[#2c3856] mb-1">Pedido #{{ $header->folio }}</h2>
                        <p class="text-sm text-gray-500 mb-6">Creado el {{ $header->created_at->format('d/m/Y H:i') }} por {{ $header->user->name ?? 'Usuario' }}</p>

                        <div class="grid grid-cols-2 gap-6 text-sm">
                            <div>
                                <h3 class="font-bold text-gray-400 text-xs uppercase mb-1">Cliente</h3>
                                <p class="font-bold text-gray-800">{{ $header->client_name }}</p>
                                <p class="text-gray-600">{{ $header->company_name }}</p>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-400 text-xs uppercase mb-1">Tipo de Pedido</h3>
                                <p class="font-bold text-gray-800 capitalize">{{ $header->order_type }}</p>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-400 text-xs uppercase mb-1">Entrega Programada</h3>
                                <p class="text-gray-800 font-medium">
                                    {{ $header->delivery_date ? $header->delivery_date->format('d/m/Y') : 'N/A' }}
                                </p>
                                <p class="text-gray-500 text-xs mt-0.5">
                                    Horario: {{ $header->delivery_date ? $header->delivery_date->format('H:i') . ' hrs' : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-400 text-xs uppercase mb-1">Localidad</h3>
                                <p class="text-gray-600">{{ $header->locality }}</p>
                            </div>
                            <div class="col-span-2">
                                <h3 class="font-bold text-gray-400 text-xs uppercase mb-1">Dirección de Entrega</h3>
                                <p class="text-gray-600">{{ $header->address }}</p>
                            </div>
                            @if($header->observations)
                            <div class="col-span-2 bg-gray-50 p-3 rounded border border-gray-200">
                                <h3 class="font-bold text-gray-400 text-xs uppercase mb-1">Observaciones</h3>
                                <p class="text-gray-600 italic">{{ $header->observations }}</p>
                            </div>
                            @endif
                        </div>

                        @if($header->status == 'approved' && $header->approver)
                            <div class="mt-6 pt-4 border-t border-gray-100 text-xs text-emerald-600 font-medium">
                                <i class="fas fa-user-check mr-1"></i> Autorizado por: {{ $header->approver->name }} el {{ $header->approved_at->format('d/m/Y H:i') }}
                            </div>
                        @endif

                        @if($header->status == 'rejected')
                            <div class="mt-6 p-4 bg-red-50 rounded-lg border border-red-100">
                                <h4 class="text-red-800 font-bold text-sm mb-1">Motivo de Rechazo:</h4>
                                <p class="text-red-600 text-sm">{{ $header->rejection_reason }}</p>
                                <div class="mt-2 text-xs text-red-400">Rechazado por: {{ $header->approver->name ?? 'Admin' }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 bg-gray-50 border-b border-gray-100 font-bold text-gray-700 text-sm uppercase">Detalle de Productos</div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50">
                                    <tr>
                                        <th class="px-4 py-3">SKU</th>
                                        <th class="px-4 py-3">Descripción</th>
                                        <th class="px-4 py-3 text-center">Cant.</th>
                                        <th class="px-4 py-3 text-right">P. Lista</th>
                                        <th class="px-4 py-3 text-center">% Desc.</th>
                                        <th class="px-4 py-3 text-right">Monto Desc.</th>
                                        <th class="px-4 py-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
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
                                            } else {
                                                $finalPrice = 0;
                                                // En remisión/préstamo no aplica concepto de descuento visual
                                                $discountPercent = 0;
                                                $discountAmount = 0;
                                                $basePrice = 0;
                                            }
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-xs">{{ $item->product->sku }}</td>
                                            <td class="px-4 py-3 text-gray-600">{{ $item->product->description }}</td>
                                            <td class="px-4 py-3 text-center font-bold">{{ $qty }}</td>
                                            <td class="px-4 py-3 text-right text-gray-500">${{ number_format($basePrice, 2) }}</td>
                                            <td class="px-4 py-3 text-center text-orange-600 font-bold">
                                                @if($discountPercent > 0)
                                                    {{ number_format($discountPercent, 1) }}%
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right text-orange-600">
                                                @if($discountAmount > 0)
                                                    -${{ number_format($discountAmount, 2) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right font-bold text-[#2c3856]">
                                                ${{ number_format($finalPrice * $qty, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 font-bold text-gray-800">
                                    <tr>
                                        <td colspan="2" class="px-4 py-3 text-right">Totales:</td>
                                        <td class="px-4 py-3 text-center">{{ $totalItems }}</td>
                                        <td colspan="3"></td>
                                        <td class="px-4 py-3 text-right text-lg">${{ number_format($totalValue, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 text-sm mb-3">Acciones</h3>
                        
                        <a href="{{ route('ff.sales.index', ['edit_folio' => $header->folio]) }}" 
                           class="w-full bg-[#2c3856] text-white font-bold py-2 px-4 rounded hover:bg-[#1e273d] transition-colors text-sm mb-2 text-center block shadow-md">
                            <i class="fas fa-edit mr-2"></i> Editar Pedido
                        </a>
                        <p class="text-xs text-gray-400 text-center">Esto abrirá la interfaz de ventas cargando este folio.</p>
                    </div>

                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 text-sm mb-3">Evidencias y Documentos</h3>
                        <div class="space-y-2">
                            @for($i=1; $i<=3; $i++)
                                @if($url = $header->getEvidenceUrl($i))
                                    <a href="{{ $url }}" target="_blank" class="flex items-center p-2 bg-blue-50 rounded hover:bg-blue-100 transition-colors group">
                                        <i class="fas fa-file-image text-blue-500 mr-2 group-hover:scale-110 transition-transform"></i>
                                        <span class="text-xs font-bold text-blue-700">Evidencia {{ $i }}</span>
                                        <i class="fas fa-external-link-alt ml-auto text-xs text-blue-400"></i>
                                    </a>
                                @endif
                            @endfor
                            @if(!$header->getEvidenceUrl(1) && !$header->getEvidenceUrl(2) && !$header->getEvidenceUrl(3))
                                <p class="text-xs text-gray-400 italic text-center py-2">No hay evidencias cargadas.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm" x-cloak>
            <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4" @click.away="showRejectModal = false">
                <h3 class="text-lg font-bold text-red-600 mb-4">Rechazar Pedido #{{ $header->folio }}</h3>
                <form action="{{ route('ff.orders.reject', $header->folio) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Motivo del rechazo <span class="text-red-500">*</span></label>
                        <textarea name="reason" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 text-sm" placeholder="Explique por qué se rechaza..." required></textarea>
                        <p class="text-xs text-gray-500 mt-1">Al rechazar, el stock reservado será devuelto automáticamente al inventario.</p>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-gray-700 font-bold text-sm hover:bg-gray-100 rounded-lg">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white font-bold text-sm hover:bg-red-700 rounded-lg shadow">Confirmar Rechazo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>