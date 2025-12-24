<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="min-h-screen p-6 lg:p-10 font-sans text-slate-800">
        
        <link rel="preconnect" href="https://fonts.googleapis.com"> 
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> 
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Raleway:wght@700;800;900&display=swap" rel="stylesheet">
        
        <style>
            body { font-family: 'Montserrat', sans-serif; }
            .font-brand { font-family: 'Raleway', sans-serif; }
            .animate-entry { animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(40px); }
            @keyframes slideUpFade { to { opacity: 1; transform: translateY(0); } }
        </style>

        <div class="max-w-7xl mx-auto mb-8 animate-entry">
            <a href="{{ route('electronic-label.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-[#ff9c00] mb-4 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Volver al Dashboard
            </a>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-[#2c3856] font-brand">Consulta de Marbetes</h1>
                    <p class="text-gray-500 mt-1">Historial de lotes generados y administración.</p>
                </div>
                <a href="{{ route('electronic-label.create') }}" class="bg-[#2c3856] hover:bg-[#1a2236] text-white px-6 py-3 rounded-lg font-bold shadow-lg transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nuevo Lote
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="max-w-7xl mx-auto mb-6 animate-entry">
                <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm flex items-start">
                    <svg class="w-6 h-6 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div><p class="font-bold">Operación Exitosa</p><p>{{ session('success') }}</p></div>
                </div>
            </div>
        @endif

        <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden animate-entry" style="animation-delay: 0.1s;">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#2c3856] text-white text-sm uppercase tracking-wider">
                            <th class="p-6 font-brand">Fecha Generación</th>
                            <th class="p-6 font-brand">Serie / Rango</th>
                            <th class="p-6 font-brand">Producto</th>
                            <th class="p-6 font-brand text-center">Cantidad</th>
                            <th class="p-6 font-brand text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($batches as $batch)
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <td class="p-6 font-medium text-gray-600">
                                    {{ $batch->created_at->format('d/m/Y') }}
                                    <span class="block text-xs text-gray-400 font-light mt-1">{{ $batch->created_at->format('H:i A') }}</span>
                                </td>
                                <td class="p-6">
                                    <div class="flex items-center">
                                        <span class="bg-orange-100 text-orange-700 font-bold px-2 py-1 rounded text-xs mr-3 border border-orange-200">{{ $batch->series }}</span>
                                        <span class="text-gray-500">
                                            {{ str_pad($batch->start_folio, 10, '0', STR_PAD_LEFT) }} 
                                            <span class="mx-1 text-gray-300">➜</span> 
                                            {{ str_pad($batch->end_folio, 10, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <p class="font-bold text-[#2c3856]">{{ $batch->product_name }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $batch->label_type }}</p>
                                </td>
                                <td class="p-6 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 font-bold text-xs border border-blue-100">
                                        {{ number_format($batch->total) }} marbetes
                                    </span>
                                </td>
                                <td class="p-6 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('electronic-label.show-batch', ['series' => $batch->series, 'date' => $batch->created_at]) }}" 
                                           class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Ver Lista de Folios">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>

                                        <a href="{{ route('electronic-label.download-csv', ['series' => $batch->series, 'date' => $batch->created_at]) }}" 
                                           class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Descargar CSV">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </a>

                                        <form action="{{ route('electronic-label.destroy-batch', ['series' => $batch->series, 'date' => $batch->created_at]) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este lote completo? Esta acción borrará {{ number_format($batch->total) }} marbetes y no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar Lote">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-10 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p>No se han generado lotes de marbetes aún.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="p-6 border-t border-gray-100 bg-gray-50">
                {{ $batches->links() }}
            </div>
        </div>
    </div>
</x-app-layout>