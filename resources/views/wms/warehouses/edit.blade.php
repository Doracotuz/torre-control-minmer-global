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
                <a href="{{ route('wms.warehouses.index') }}" class="w-12 h-12 rounded-full bg-white border border-gray-200 flex items-center justify-center text-[#666666] hover:text-[#ff9c00] hover:border-[#ff9c00] transition-all shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <p class="text-xs font-bold text-[#666666] uppercase tracking-[0.2em] mb-1">Edición</p>
                    <h1 class="text-4xl font-raleway font-black text-[#2c3856]">Editar Almacén</h1>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-soft border border-gray-100 p-8 md:p-12">
                <form action="{{ route('wms.warehouses.update', $warehouse) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    @include('wms.warehouses._form')

                    <div class="mt-12 flex justify-end items-center gap-4">
                        <a href="{{ route('wms.warehouses.index') }}" class="px-8 py-3 rounded-xl border border-gray-200 text-[#666666] font-bold hover:bg-gray-50 transition-all">Cancelar</a>
                        <button type="submit" class="px-10 py-3 rounded-xl bg-[#2c3856] text-white font-bold shadow-lg shadow-[#2c3856]/30 hover:bg-[#1a253a] hover:-translate-y-0.5 transition-all">Actualizar Almacén</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>