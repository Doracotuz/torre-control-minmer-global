<div x-data="{
    aisle: '{{ old('aisle', $location->aisle ?? '') }}'.toUpperCase(),
    rack: '{{ old('rack', $location->rack ?? '') }}'.padStart(2, '0'),
    shelf: '{{ old('shelf', $location->shelf ?? '') }}'.padStart(2, '0'),
    bin: '{{ old('bin', $location->bin ?? '') }}'.toUpperCase(),

    generateCode() {
        // Filtramos las partes que no están vacías y las unimos con un guion
        const parts = [this.aisle, this.rack, this.shelf, this.bin].filter(part => part && part !== '00');
        this.$refs.locationCode.value = parts.join('-');
    }
}" x-init="generateCode()">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700">Código de Ubicación (Automático)</label>
            <input type="text" name="code" id="code" x-ref="locationCode" value="{{ old('code', $location->code ?? '') }}" required placeholder="Ej: A-01-03-B" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" readonly>
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
                <input type="text" name="aisle" id="aisle" x-model="aisle" @input="aisle = $event.target.value.toUpperCase(); generateCode()" placeholder="Ej: A" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label for="rack" class="block text-sm font-medium text-gray-700">Rack</label>
                <input type="text" name="rack" id="rack" x-model="rack" @input="rack = $event.target.value.padStart(2, '0'); generateCode()" placeholder="Ej: 01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label for="shelf" class="block text-sm font-medium text-gray-700">Nivel</label>
                <input type="text" name="shelf" id="shelf" x-model="shelf" @input="shelf = $event.target.value.padStart(2, '0'); generateCode()" placeholder="Ej: 03" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label for="bin" class="block text-sm font-medium text-gray-700">Bin</label>
                <input type="text" name="bin" id="bin" x-model="bin" @input="bin = $event.target.value.toUpperCase(); generateCode()" placeholder="Ej: B" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
        </div>

        <div class="md:col-span-2">
            <label for="pick_sequence" class="block text-sm font-medium text-gray-700">Secuencia de Picking (Opcional)</label>
            <input type="number" name="pick_sequence" id="pick_sequence" value="{{ old('pick_sequence', $location->pick_sequence ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Ej: 1010">
            <p class="text-xs text-gray-500 mt-1">Un número para ordenar la ruta de recolección. Menor es primero.</p>
        </div>
    </div>
</div>