<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@700;800;900&display=swap');
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        .shadow-soft { box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.1); }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative">
        
        <div class="max-w-[1800px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 border-b border-gray-200 pb-6">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <div class="h-1 w-8 bg-[#ff9c00]"></div>
                        <p class="text-xs font-bold text-[#666666] uppercase tracking-[0.2em]">Operaciones</p>
                    </div>
                    <h1 class="text-5xl font-raleway font-black text-[#2c3856] mb-1 leading-none">
                        Centro de Control <span class="text-[#ff9c00]">LPNs</span>
                    </h1>
                    <p class="text-[#666666] text-lg font-medium">Gestión de Etiquetas de Tarima (License Plate Numbers)</p>
                </div>

                <div class="mt-6 xl:mt-0 flex gap-3">
                    <a href="{{ route('wms.lpns.export-inventory') }}" class="flex items-center gap-2 px-5 py-2.5 bg-[#2c3856] text-white font-bold rounded-full shadow-sm hover:shadow-md hover:bg-[#1a253a] transition-all">
                        <i class="fas fa-file-csv"></i> <span>Exportar Inventario</span>
                    </a>
                    <a href="{{ route('wms.dashboard') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-[#666666] font-bold rounded-full shadow-sm hover:shadow-md hover:border-[#2c3856] hover:text-[#2c3856] transition-all">
                        <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-xl shadow-sm font-medium flex items-center gap-3">
                    <i class="fas fa-check-circle text-xl"></i>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                 <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl shadow-sm font-medium flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                    <p>{{ session('error') }}</p>
                 </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white p-6 rounded-[2rem] shadow-soft border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-[#666666] uppercase tracking-wider mb-1">Total Generados</p>
                        <p class="text-4xl font-raleway font-black text-[#2c3856]">{{ number_format($totalLpns) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-[#2c3856]">
                        <i class="fas fa-barcode text-xl"></i>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-soft border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-[#666666] uppercase tracking-wider mb-1">Disponibles</p>
                        <p class="text-4xl font-raleway font-black text-green-600">{{ number_format($unusedLpns) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                        <i class="fas fa-check text-xl"></i>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-soft border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-[#666666] uppercase tracking-wider mb-1">En Uso (Inventario)</p>
                        <p class="text-4xl font-raleway font-black text-[#ff9c00]">{{ number_format($usedLpns) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-[#fff8e6] flex items-center justify-center text-[#ff9c00]">
                        <i class="fas fa-box text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <div class="space-y-8">
                    <div class="bg-white rounded-[2rem] shadow-soft border border-gray-100 p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-[#2c3856] text-white flex items-center justify-center">
                                <span class="font-bold font-raleway text-lg">1</span>
                            </div>
                            <h3 class="text-2xl font-raleway font-black text-[#2c3856]">Generar Nuevos</h3>
                        </div>
                        <p class="text-[#666666] mb-6 text-sm">Crea un lote de nuevos identificadores únicos para futuras recepciones.</p>
                        
                        <form action="{{ route('wms.lpns.generate') }}" method="POST" class="flex gap-4 items-end">
                            @csrf
                            <div class="w-full">
                                <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Cantidad</label>
                                <input type="number" name="quantity" min="1" max="100" value="20" required class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all text-[#2c3856] font-bold">
                            </div>
                            <button type="submit" class="px-8 py-3 bg-[#2c3856] text-white font-bold rounded-xl hover:bg-[#1a253a] transition-all shadow-md h-[50px]">
                                Generar
                            </button>
                        </form>
                    </div>

                    <div class="bg-white rounded-[2rem] shadow-soft border border-gray-100 p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-[#2c3856] text-white flex items-center justify-center">
                                <span class="font-bold font-raleway text-lg">2</span>
                            </div>
                            <h3 class="text-2xl font-raleway font-black text-[#2c3856]">Imprimir Lote</h3>
                        </div>
                        <p class="text-[#666666] mb-6 text-sm">Descarga el PDF con los códigos de barras de los LPNs disponibles más recientes.</p>
                        
                        <form action="{{ route('wms.lpns.print') }}" method="GET" target="_blank" class="flex gap-4 items-end">
                            <div class="w-full">
                                <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Cantidad a Imprimir</label>
                                <input type="number" name="quantity" min="1" max="100" value="20" required class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all text-[#2c3856] font-bold">
                            </div>
                            <button type="submit" class="px-8 py-3 bg-[#666666] text-white font-bold rounded-xl hover:bg-[#4d4d4d] transition-all shadow-md h-[50px] whitespace-nowrap">
                                <i class="fas fa-print mr-2"></i> PDF
                            </button>
                        </form>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="bg-white rounded-[2rem] shadow-soft border border-gray-100 p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-[#ff9c00] text-white flex items-center justify-center">
                                <i class="fas fa-redo"></i>
                            </div>
                            <h3 class="text-2xl font-raleway font-black text-[#2c3856]">Reimpresión Manual</h3>
                        </div>
                        <p class="text-[#666666] mb-6 text-sm">Ingresa los códigos específicos que necesitas volver a imprimir.</p>

                        <form action="{{ route('wms.lpns.reprint') }}" method="POST" target="_blank">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Lista de LPNs</label>
                                <textarea name="lpns" rows="6" required placeholder="LPN-ABC123&#10;LPN-DEF456&#10;LPN-GHI789" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all font-mono text-sm resize-none"></textarea>
                                <p class="text-xs text-gray-400 mt-2 text-right">Un LPN por renglón (Copiar/Pegar desde Excel)</p>
                            </div>
                            <div class="flex gap-4 items-end">
                                <div class="w-1/3">
                                    <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Copias</label>
                                    <input type="number" name="quantity" value="1" min="1" max="50" required class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all font-bold text-center">
                                </div>
                                <button type="submit" class="w-2/3 px-8 py-3 bg-[#ff9c00] text-white font-bold rounded-xl hover:bg-[#e68a00] transition-all shadow-md h-[50px]">
                                    Reimprimir Etiquetas
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white rounded-[2rem] shadow-soft border border-gray-100 p-8 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-[#2c3856] rounded-bl-full opacity-5 -mr-10 -mt-10"></div>
                        
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-green-600 text-white flex items-center justify-center">
                                    <i class="fas fa-file-csv"></i>
                                </div>
                                <h3 class="text-2xl font-raleway font-black text-[#2c3856]">Importar CSV</h3>
                            </div>
                            <a href="{{ route('wms.lpns.template') }}" class="text-xs font-bold text-[#2c3856] hover:text-[#ff9c00] transition-colors border-b border-[#2c3856] hover:border-[#ff9c00] pb-0.5">
                                Descargar Plantilla
                            </a>
                        </div>
                        
                        <form action="{{ route('wms.lpns.print-from-csv') }}" method="POST" enctype="multipart/form-data" target="_blank">
                            @csrf
                            <div class="mb-4">
                                <label class="block w-full border-2 border-dashed border-gray-200 hover:border-green-500 hover:bg-green-50 rounded-xl p-4 transition-all cursor-pointer text-center group">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-300 group-hover:text-green-500 mb-2 transition-colors"></i>
                                        <span class="text-sm font-bold text-gray-500 group-hover:text-green-600">Seleccionar Archivo CSV</span>
                                    </div>
                                    <input type="file" name="lpn_file" accept=".csv, .txt" required class="hidden">
                                </label>
                            </div>
                            <div class="flex gap-4 items-end">
                                <div class="w-1/3">
                                    <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Copias</label>
                                    <input type="number" name="quantity" value="1" min="1" max="50" required class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-green-500 focus:ring-green-500 transition-all font-bold text-center">
                                </div>
                                <button type="submit" class="w-2/3 px-8 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all shadow-md h-[50px]">
                                    Procesar CSV
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @if(session('new_batch_qty'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const qty = "{{ session('new_batch_qty') }}";
                const url = "{{ route('wms.lpns.print') }}?quantity=" + qty;
                window.open(url, '_blank');
            });
        </script>
    @endif

</x-app-layout>