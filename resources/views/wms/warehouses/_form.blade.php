<div class="grid grid-cols-1 md:grid-cols-6 gap-8">
    <div class="md:col-span-4">
        <label for="name" class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Nombre del Almacén</label>
        <input type="text" name="name" id="name" value="{{ old('name', $warehouse->name ?? '') }}" required class="w-full px-4 py-3 rounded-xl border-gray-200 bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all font-bold text-[#2c3856] text-lg shadow-sm placeholder-gray-300" placeholder="Ej: Centro de Distribución Norte">
        @error('name') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
    </div>
    
    <div class="md:col-span-2">
        <label for="code" class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Código Corto</label>
        <input type="text" name="code" id="code" value="{{ old('code', $warehouse->code ?? '') }}" required class="w-full px-4 py-3 rounded-xl border-gray-200 bg-white focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all font-mono font-bold text-[#2c3856] shadow-sm placeholder-gray-300" placeholder="Ej: CD-MX">
        @error('code') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div class="md:col-span-6">
        <label for="address" class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Dirección Física</label>
        <textarea name="address" id="address" rows="3" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all resize-none shadow-inner placeholder-gray-300" placeholder="Calle, Número, Colonia, Ciudad...">{{ old('address', $warehouse->address ?? '') }}</textarea>
    </div>
</div>