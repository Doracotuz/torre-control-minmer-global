<x-app-layout>
    <x-slot name="header"></x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        .nexus-card { background: white; border-radius: 1.5rem; box-shadow: 0 20px 40px -10px rgba(44, 56, 86, 0.1); border: 1px solid #f3f4f6; }
        .btn-nexus { background: #2c3856; color: white; border-radius: 1rem; padding: 0.75rem 1.5rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(44, 56, 86, 0.2); }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(44, 56, 86, 0.3); }
        .input-nexus { border-radius: 1rem; border: 1px solid #e5e7eb; padding: 0.75rem 1rem; font-size: 0.875rem; transition: all 0.2s; }
        .input-nexus:focus { border-color: #ff9c00; ring: 2px solid #ff9c00; box-shadow: 0 0 0 4px rgba(255, 156, 0, 0.1); }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative">
        <div class="max-w-3xl mx-auto px-4 md:px-6 pt-6 md:pt-10">
            
            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('wms.service-requests.index') }}" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-[#2c3856] shadow-md hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-raleway font-black text-[#2c3856]">Nueva Solicitud</h1>
                    <p class="text-gray-500 font-medium text-sm">Crea un folio para asignar servicios independientes.</p>
                </div>
            </div>

            <div class="nexus-card p-8">
                <form method="POST" action="{{ route('wms.service-requests.store') }}" class="space-y-6">
                    @csrf

                    <!-- Client / Area -->
                    <div>
                        <x-input-label for="area_id" :value="__('Cliente / Área')" class="font-raleway font-bold text-[#2c3856] text-xs uppercase tracking-wider mb-2" />
                        <select id="area_id" name="area_id" class="input-nexus block w-full bg-gray-50" required>
                            <option value="">-- Seleccione Cliente --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('area_id')" class="mt-2" />
                    </div>

                    <!-- Warehouse -->
                    <div>
                        <x-input-label for="warehouse_id" :value="__('Almacén')" class="font-raleway font-bold text-[#2c3856] text-xs uppercase tracking-wider mb-2" />
                        <select id="warehouse_id" name="warehouse_id" class="input-nexus block w-full bg-gray-50" required>
                            <option value="">-- Seleccione Almacén --</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('warehouse_id')" class="mt-2" />
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="btn-nexus flex items-center gap-2">
                             <i class="fas fa-check"></i> Crear Solicitud
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
