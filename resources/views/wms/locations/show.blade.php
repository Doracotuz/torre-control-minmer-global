<x-app-layout>
    <x-slot name="header"></x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@700;800;900&display=swap');
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        .shadow-soft { box-shadow: 0 20px 40px -10px rgba(44, 56, 86, 0.08); }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative">
        
        <div class="max-w-4xl mx-auto px-6 pt-10 relative z-10">
            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('wms.locations.index') }}" class="w-12 h-12 rounded-full bg-white border border-gray-200 flex items-center justify-center text-[#666666] hover:text-[#ff9c00] hover:border-[#ff9c00] transition-all shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <p class="text-xs font-bold text-[#666666] uppercase tracking-[0.2em] mb-1">Detalle</p>
                    <h1 class="text-4xl font-raleway font-black text-[#2c3856]">{{ $location->code }}</h1>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-soft border border-gray-100 overflow-hidden">
                <div class="p-8 md:p-12 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-raleway font-black text-[#2c3856]">
                                {{ $location->aisle }}-{{ $location->rack }}-{{ $location->shelf }}-{{ $location->bin }}
                            </h3>
                            <p class="text-[#666666] font-medium mt-1">Coordenada Física Completa</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-4 py-2 bg-[#e6fffa] text-[#2c3856] text-sm font-bold rounded-lg border border-[#2c3856]/10">
                                {{ $location->translated_type }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="p-8 md:p-12">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div>
                                <p class="text-xs font-bold text-[#666666] uppercase tracking-wide mb-1">Almacén</p>
                                <p class="text-lg font-bold text-[#2c3856]">{{ $location->warehouse->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-[#666666] uppercase tracking-wide mb-1">Secuencia de Picking</p>
                                <p class="text-lg font-bold text-[#2c3856]">{{ $location->pick_sequence ?? 'No definida' }}</p>
                            </div>
                        </div>
                        <div class="bg-[#f9fafb] rounded-2xl p-6 border border-gray-100">
                            <h4 class="text-sm font-bold text-[#2c3856] mb-4">Desglose de Coordenadas</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold">Pasillo</p>
                                    <p class="text-xl font-black text-[#ff9c00]">{{ $location->aisle }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold">Rack</p>
                                    <p class="text-xl font-black text-[#ff9c00]">{{ $location->rack }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold">Nivel</p>
                                    <p class="text-xl font-black text-[#ff9c00]">{{ $location->shelf }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold">Bin</p>
                                    <p class="text-xl font-black text-[#ff9c00]">{{ $location->bin }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 flex justify-end gap-4">
                        <a href="{{ route('wms.locations.index') }}" class="px-8 py-3 rounded-xl border border-gray-200 text-[#666666] font-bold hover:bg-gray-50 transition-all">Volver</a>
                        <a href="{{ route('wms.locations.edit', $location->id) }}" class="px-10 py-3 rounded-xl bg-[#2c3856] text-white font-bold shadow-lg shadow-[#2c3856]/30 hover:bg-[#1a253a] hover:-translate-y-0.5 transition-all">Editar Ubicación</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>