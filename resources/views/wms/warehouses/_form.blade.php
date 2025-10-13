<div class="grid grid-cols-1 md:grid-cols-6 gap-6">
    <div class="md:col-span-4">
        <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Almacén</label>
        <input type="text" name="name" id="name" value="{{ old('name', $warehouse->name ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>
    <div class="md:col-span-2">
        <label for="code" class="block text-sm font-medium text-gray-700">Código</label>
        <input type="text" name="code" id="code" value="{{ old('code', $warehouse->code ?? '') }}" required placeholder="Ej: TMX, GDL" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>
    <div class="md:col-span-6">
        <label for="address" class="block text-sm font-medium text-gray-700">Dirección</label>
        <textarea name="address" id="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('address', $warehouse->address ?? '') }}</textarea>
    </div>
</div>