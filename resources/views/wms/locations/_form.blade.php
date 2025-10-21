{{-- Quitamos todo el contenedor x-data y la lógica de generateCode() --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- En la vista de edición, se sigue mostrando el código permanente --}}
    @if (isset($location))
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700">Código (ID Permanente)</label>
            <input type="text" id="code" value="{{ $location->code }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-200" disabled>
        </div>
    @endif

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
            @foreach(['storage' => 'Almacenamiento', 'picking' => 'Picking', 'receiving' => 'Recepción', 'shipping' => 'Embarque', 'quality_control' => 'Control de Calidad'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $location->type ?? '') == $value)>{{ $label }}</option>
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

    <div class="md:col-span-2">
        <label for="pick_sequence" class="block text-sm font-medium text-gray-700">Secuencia de Picking (Opcional)</label>
        <input type="number" name="pick_sequence" id="pick_sequence" value="{{ old('pick_sequence', $location->pick_sequence ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Ej: 1010">
        <p class="text-xs text-gray-500 mt-1">Un número para ordenar la ruta de recolección. Menor es primero.</p>
    </div>
</div>