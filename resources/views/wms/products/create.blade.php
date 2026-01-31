<x-app-layout>
    <x-slot name="header"></x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@700;800;900&display=swap');
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        .shadow-soft { box-shadow: 0 20px 40px -10px rgba(44, 56, 86, 0.08); }
    </style>

    <div class="min-h-screen text-[#2b2b2b] font-montserrat pb-20 relative">
        <div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden">
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#2c3856] rounded-full blur-[150px] opacity-5"></div>
            <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-[#ff9c00] rounded-full blur-[150px] opacity-5"></div>
        </div>

        <div class="max-w-5xl mx-auto px-6 pt-10 relative z-10">
            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('wms.products.index') }}" class="w-12 h-12 rounded-full bg-white border border-gray-200 flex items-center justify-center text-[#666666] hover:text-[#ff9c00] hover:border-[#ff9c00] transition-all shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <p class="text-xs font-bold text-[#666666] uppercase tracking-[0.2em] mb-1">Catálogo</p>
                    <h1 class="text-4xl font-raleway font-black text-[#2c3856]">Nuevo Producto</h1>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-soft border border-gray-100 p-8 md:p-12"
                 x-data="productForm()"
                 x-init="init()">
                 
                <form action="{{ route('wms.products.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-8 bg-[#fff8e6] border border-[#ff9c00]/20 rounded-2xl p-6 relative overflow-hidden">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-[#ff9c00] rounded-full opacity-10"></div>
                        <label for="area_id" class="block text-sm font-bold text-[#b36b00] uppercase tracking-wide mb-2">1. Cliente (Dueño del Producto)</label>
                        <div class="relative">
                            <select name="area_id" id="area_id" x-model="areaId" @change="fetchCatalogs()" required class="block w-full pl-4 pr-10 py-4 rounded-xl border-[#ff9c00]/30 bg-white text-[#2c3856] font-bold text-lg focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all appearance-none cursor-pointer shadow-sm">
                                <option value="">-- Seleccionar Cliente --</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-xs text-[#b36b00] mt-2 font-medium ml-1 flex items-center"><i class="fas fa-info-circle mr-1"></i> Selecciona el cliente para desbloquear sus marcas y tipos.</p>
                        @error('area_id') <span class="text-red-500 text-xs font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div>
                            <label for="sku" class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">SKU (Código Único)</label>
                            <input type="text" name="sku" id="sku" value="{{ old('sku') }}" required class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all font-mono text-[#2c3856] font-bold shadow-inner">
                            @error('sku') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="upc" class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">UPC (Código de Barras)</label>
                            <input type="text" name="upc" id="upc" value="{{ old('upc') }}" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all font-mono shadow-inner">
                        </div>
                        <div class="md:col-span-2">
                            <label for="name" class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Nombre del Producto</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all text-lg font-medium text-[#2c3856] shadow-inner">
                            @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div>
                            <label for="brand_id" class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Marca</label>
                            <div class="relative">
                                <select name="brand_id" id="brand_id" class="w-full pl-4 pr-10 py-3 rounded-xl border-gray-200 bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all appearance-none disabled:bg-gray-100 disabled:text-gray-400 cursor-pointer" :disabled="!areaId">
                                    <option value="">-- Seleccionar --</option>
                                    <template x-for="brand in brands" :key="brand.id">
                                        <option :value="brand.id" x-text="brand.name"></option>
                                    </template>
                                </select>
                            </div>
                            <p x-show="!areaId" class="text-xs text-[#ff9c00] mt-1 ml-1 font-bold">Esperando cliente...</p>
                        </div>
                        <div>
                            <label for="product_type_id" class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Tipo de Producto</label>
                            <div class="relative">
                                <select name="product_type_id" id="product_type_id" class="w-full pl-4 pr-10 py-3 rounded-xl border-gray-200 bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all appearance-none disabled:bg-gray-100 disabled:text-gray-400 cursor-pointer" :disabled="!areaId">
                                    <option value="">-- Seleccionar --</option>
                                    <template x-for="type in types" :key="type.id">
                                        <option :value="type.id" x-text="type.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <label for="description" class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-1">Descripción Detallada</label>
                        <textarea name="description" id="description" rows="3" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-[#2c3856] focus:ring-[#2c3856] transition-all resize-none shadow-inner">{{ old('description') }}</textarea>
                    </div>

                    <div class="border-t border-gray-100 pt-8 mt-8">
                        <h3 class="text-lg font-raleway font-black text-[#2c3856] mb-6 flex items-center gap-2">
                            <i class="fas fa-ruler-combined text-[#ff9c00]"></i> Logística y Dimensiones
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <div class="col-span-2">
                                <label for="unit_of_measure" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Unidad</label>
                                <select name="unit_of_measure" id="unit_of_measure" class="w-full px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-[#ff9c00] focus:ring-[#ff9c00]">
                                    <option value="Pieza">Pieza</option>
                                    <option value="Caja">Caja</option>
                                    <option value="Kg">Kg</option>
                                    <option value="Litro">Litro</option>
                                    <option value="Metro">Metro</option>
                                </select>
                            </div>
                            <div>
                                <label for="length" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Largo (cm)</label>
                                <input type="number" name="length" id="length" value="{{ old('length') }}" step="0.01" class="w-full px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-[#ff9c00] focus:ring-[#ff9c00]">
                            </div>
                            <div>
                                <label for="width" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Ancho (cm)</label>
                                <input type="number" name="width" id="width" value="{{ old('width') }}" step="0.01" class="w-full px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-[#ff9c00] focus:ring-[#ff9c00]">
                            </div>
                            <div>
                                <label for="height" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Alto (cm)</label>
                                <input type="number" name="height" id="height" value="{{ old('height') }}" step="0.01" class="w-full px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-[#ff9c00] focus:ring-[#ff9c00]">
                            </div>
                            <div>
                                <label for="weight" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Peso (kg)</label>
                                <input type="number" name="weight" id="weight" value="{{ old('weight') }}" step="0.01" class="w-full px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-[#ff9c00] focus:ring-[#ff9c00]">
                            </div>
                            <div class="col-span-2 md:col-span-1 lg:col-span-2">
                                <label for="pieces_per_case" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Pzas/Caja</label>
                                <input type="number" name="pieces_per_case" id="pieces_per_case" value="{{ old('pieces_per_case', 1) }}" min="1" step="1" class="w-full px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-[#ff9c00] focus:ring-[#ff9c00]">
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 flex justify-end items-center gap-4">
                        <a href="{{ route('wms.products.index') }}" class="px-8 py-3 rounded-xl border border-gray-200 text-[#666666] font-bold hover:bg-gray-50 transition-all">Cancelar</a>
                        <button type="submit" class="px-10 py-3 rounded-xl bg-[#2c3856] text-white font-bold shadow-lg shadow-[#2c3856]/30 hover:bg-[#1a253a] hover:-translate-y-0.5 transition-all">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function productForm() {
            return {
                areaId: '{{ old('area_id') }}',
                brands: [],
                types: [],
                init() {
                    if (this.areaId) {
                        this.fetchCatalogs();
                    }
                },
                fetchCatalogs() {
                    if (!this.areaId) {
                        this.brands = [];
                        this.types = [];
                        return;
                    }
                    fetch(`{{ route('wms.products.catalogs') }}?area_id=${this.areaId}`)
                        .then(res => res.json())
                        .then(data => {
                            this.brands = data.brands;
                            this.types = data.types;
                        })
                        .catch(err => console.error(err));
                }
            }
        }
    </script>
</x-app-layout>