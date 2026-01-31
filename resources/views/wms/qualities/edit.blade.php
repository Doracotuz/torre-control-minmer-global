<x-app-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@700;800;900&display=swap');
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        .shadow-soft { box-shadow: 0 20px 40px -10px rgba(44, 56, 86, 0.08); }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative">
        
        <div class="max-w-4xl mx-auto px-6 pt-10 relative z-10">
            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('wms.qualities.index') }}" class="w-12 h-12 rounded-full bg-white border border-gray-200 flex items-center justify-center text-[#666666] hover:text-[#ff9c00] hover:border-[#ff9c00] transition-all shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <p class="text-xs font-bold text-[#666666] uppercase tracking-[0.2em] mb-1">Edición</p>
                    <h1 class="text-4xl font-raleway font-black text-[#2c3856]">Editar Calidad</h1>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-soft border border-gray-100 p-8 md:p-12">
                <form action="{{ route('wms.qualities.update', $quality) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-8 bg-[#f3f4f6] border border-[#2c3856]/10 rounded-2xl p-6 relative overflow-hidden">
                        <label for="area_id" class="block text-sm font-bold text-[#2c3856] uppercase tracking-wide mb-2">Cliente (Dueño)</label>
                        <select name="area_id" id="area_id" required class="block w-full pl-4 pr-4 py-4 rounded-xl border-[#2c3856]/20 bg-white text-[#2c3856] font-bold text-lg focus:border-[#2c3856] focus:ring-[#2c3856] transition-all cursor-pointer shadow-sm">
                            <option value="">-- Seleccionar Cliente --</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ (old('area_id', $quality->area_id) == $area->id) ? 'selected' : '' }}>{{ $area->name }}</option>
                            @endforeach
                        </select>
                        @error('area_id') <span class="text-red-500 text-xs font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-8">
                        <label for="name" class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Nombre</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $quality->name) }}" required class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all text-lg font-medium text-[#2c3856] shadow-inner">
                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-8">
                        <label for="description" class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Descripción</label>
                        <textarea name="description" id="description" rows="3" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all resize-none shadow-inner">{{ old('description', $quality->description) }}</textarea>
                    </div>

                    <div class="mt-12 flex justify-end items-center gap-4">
                        <a href="{{ route('wms.qualities.index') }}" class="px-8 py-3 rounded-xl border border-gray-200 text-[#666666] font-bold hover:bg-gray-50 transition-all">Cancelar</a>
                        <button type="submit" class="px-10 py-3 rounded-xl bg-[#2c3856] text-white font-bold shadow-lg shadow-[#2c3856]/30 hover:bg-[#1a253a] hover:-translate-y-0.5 transition-all">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>