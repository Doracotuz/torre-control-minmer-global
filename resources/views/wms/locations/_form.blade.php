<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
    @if (isset($location))
        <div>
            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Código (ID Permanente)</label>
            <input type="text" value="{{ $location->code }}" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-100 text-gray-500 font-mono font-bold cursor-not-allowed" disabled>
        </div>
    @endif

    <div>
        <label for="warehouse_id" class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Almacén</label>
        <select name="warehouse_id" id="warehouse_id" required class="block w-full pl-4 pr-10 py-3 rounded-xl border-gray-200 bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all cursor-pointer">
            @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $location->warehouse_id ?? '') == $warehouse->id)>{{ $warehouse->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <label for="type" class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Tipo de Ubicación</label>
        <select name="type" id="type" required class="block w-full pl-4 pr-10 py-3 rounded-xl border-gray-200 bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all cursor-pointer">
            @foreach(['storage' => 'Almacenamiento', 'picking' => 'Picking', 'receiving' => 'Recepción', 'shipping' => 'Embarque', 'quality_control' => 'Control de Calidad'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $location->type ?? '') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="bg-[#f9fafb] rounded-2xl p-6 border border-gray-100 mb-8">
    <h3 class="text-sm font-bold text-[#2c3856] uppercase tracking-wide mb-4 flex items-center gap-2">
        <i class="fas fa-map-marker-alt text-[#ff9c00]"></i> Coordenadas Físicas
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
            <label for="aisle" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Pasillo</label>
            <input type="text" name="aisle" id="aisle" value="{{ old('aisle', $location->aisle ?? '') }}" placeholder="Ej: A" class="w-full px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] font-bold text-[#2c3856] placeholder-gray-300">
        </div>
        <div>
            <label for="rack" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Rack</label>
            <input type="text" name="rack" id="rack" value="{{ old('rack', $location->rack ?? '') }}" placeholder="Ej: 01" class="w-full px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] font-bold text-[#2c3856] placeholder-gray-300">
        </div>
        <div>
            <label for="shelf" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Nivel</label>
            <input type="text" name="shelf" id="shelf" value="{{ old('shelf', $location->shelf ?? '') }}" placeholder="Ej: 03" class="w-full px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] font-bold text-[#2c3856] placeholder-gray-300">
        </div>
        <div>
            <label for="bin" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Bin</label>
            <input type="text" name="bin" id="bin" value="{{ old('bin', $location->bin ?? '') }}" placeholder="Ej: B" class="w-full px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] font-bold text-[#2c3856] placeholder-gray-300">
        </div>
    </div>
</div>

<div class="mb-8">
    <label for="pick_sequence" class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Secuencia de Picking (Opcional)</label>
    <input type="number" name="pick_sequence" id="pick_sequence" value="{{ old('pick_sequence', $location->pick_sequence ?? '') }}" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all" placeholder="Ej: 1010">
    <p class="text-xs text-gray-400 mt-2 ml-1">Un número para ordenar la ruta de recolección (menor = primero).</p>
</div>