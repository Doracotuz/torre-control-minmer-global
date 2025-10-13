<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="code" class="block text-sm font-medium text-gray-700">Código de Ubicación (Único)</label>
        <input type="text" name="code" id="code" value="{{ old('code', $location->code ?? '') }}" required placeholder="Ej: A-01-03-B" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>
    <div>
        <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Almacén</label>
        <select name="warehouse_id" id="warehouse_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $location->warehouse_id ?? '') == $warehouse->id)>{{ $warehouse->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label for="type" class="block text-sm font-medium text-gray-700">Tipo de Ubicación</label>
        <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @php $types = ['storage', 'picking', 'receiving', 'shipping', 'quality_control']; @endphp
            @foreach($types as $type)
                <option value="{{ $type }}" @selected(old('type', $location->type ?? '') == $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
            @endforeach
        </select>
    </div>
    
    <div class="md:col-span-2 grid grid-cols-4 gap-4">
        <div>
            <label for="aisle" class="block text-sm font-medium text-gray-700">Pasillo</label>
            <input type="text" name="aisle" id="aisle" value="{{ old('aisle', $location->aisle ?? '') }}" placeholder="Ej: A" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div>
            <label for="rack" class="block text-sm font-medium text-gray-700">Rack</label>
            <input type="text" name="rack" id="rack" value="{{ old('rack', $location->rack ?? '') }}" placeholder="Ej: 01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div>
            <label for="shelf" class="block text-sm font-medium text-gray-700">Nivel</label>
            <input type="text" name="shelf" id="shelf" value="{{ old('shelf', $location->shelf ?? '') }}" placeholder="Ej: 03" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div>
            <label for="bin" class="block text-sm font-medium text-gray-700">Bin</label>
            <input type="text" name="bin" id="bin" value="{{ old('bin', $location->bin ?? '') }}" placeholder="Ej: B" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
    </div>
</div>