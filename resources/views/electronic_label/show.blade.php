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
            <a href="{{ route('electronic-label.records') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-[#ff9c00] mb-4 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Volver al Historial
            </a>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div>
                    <h1 class="text-2xl font-bold text-[#2c3856] font-brand flex items-center">
                        <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-md text-base mr-3">{{ $batchInfo->series }}</span>
                        Detalle del Lote
                    </h1>
                    <p class="text-gray-500 mt-2 text-sm">
                        Generado el {{ \Carbon\Carbon::parse($date)->format('d/m/Y H:i') }} • 
                        <span class="font-semibold">{{ $batchInfo->product_name }}</span>
                    </p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="{{ route('electronic-label.download-csv', ['series' => $batchInfo->series, 'date' => $date]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition-colors font-bold text-sm border border-emerald-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Exportar Lote
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden animate-entry" style="animation-delay: 0.1s;">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-[#2c3856] text-xs uppercase tracking-wider border-b border-gray-200">
                            <th class="p-4 font-bold">Folio</th>
                            <th class="p-4 font-bold">Consecutivo</th>
                            <th class="p-4 font-bold">Link de Validación (QR)</th>
                            <th class="p-4 font-bold text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach($labels as $label)
                            <tr class="hover:bg-blue-50/50 transition-colors">
                                <td class="p-4 font-mono font-bold text-[#2c3856]">
                                    {{ $label->folio }}
                                </td>
                                <td class="p-4 text-gray-500">
                                    #{{ $label->consecutive }}
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center max-w-md">
                                        <input type="text" readonly value="{{ $label->full_url }}" 
                                               class="w-full text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded px-2 py-1 focus:outline-none select-all mr-2">
                                    </div>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ $label->full_url }}" target="_blank" 
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-md text-xs font-bold transition-colors">
                                        Abrir Link
                                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-t border-gray-100 bg-gray-50">
                {{ $labels->links() }}
            </div>
        </div>
    </div>
</x-app-layout>