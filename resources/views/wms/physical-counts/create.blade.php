<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .stagger-enter { opacity: 0; transform: translateY(20px); animation: enterUp 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes enterUp { to { opacity: 1; transform: translateY(0); } }
        
        .input-arch {
            background: transparent; border: none; border-bottom: 2px solid #e5e7eb; border-radius: 0;
            padding: 0.8rem 0; font-family: 'Montserrat', sans-serif; font-weight: 600; color: #2c3856;
            transition: all 0.3s ease; width: 100%; font-size: 1rem;
        }
        .input-arch:focus { border-bottom-color: #ff9c00; box-shadow: none; outline: none; }
        .input-arch-select { background-image: none; cursor: pointer; padding-right: 1.5rem; }

        .btn-nexus { 
            background: #2c3856; color: white; border-radius: 1rem; font-weight: 700;
            transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(44, 56, 86, 0.2); }
        
        .btn-ghost {
            background: transparent; color: #2c3856; border: 2px solid #e5e7eb; border-radius: 1rem; font-weight: 700;
        }
        .btn-ghost:hover { border-color: #2c3856; background: #2c3856; color: white; }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-[50vw] h-full bg-gradient-to-l from-[#f8fafc] to-transparent"></div>
            <div class="absolute bottom-0 left-0 w-[40rem] h-[40rem] bg-[#ff9c00]/5 rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-4xl mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-10 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00]"></span>
                        <span class="text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Auditoría de Inventario</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-raleway font-black text-[#2c3856] leading-none">
                        Nueva Sesión de <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">Conteo</span>
                    </h1>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="{{ route('wms.physical-counts.index') }}" class="btn-ghost px-6 py-3 text-sm uppercase tracking-wider">
                        <i class="fas fa-arrow-left mr-2"></i> Cancelar
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 p-10 stagger-enter" style="animation-delay: 0.2s;">
                <form action="{{ route('wms.physical-counts.store') }}" method="POST" enctype="multipart/form-data" x-data="{ countType: '{{ old('type', 'cycle') }}' }">
                    @csrf
                    
                    @if (session('error'))
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl font-bold flex items-center gap-3">
                            <i class="fas fa-exclamation-circle text-xl"></i>
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl">
                            <ul class="list-disc list-inside text-sm font-medium">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-8">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Nombre de la Sesión</label>
                            <input type="text" name="name" value="{{ old('name', 'Conteo ' . now()->format('d-M-Y')) }}" required class="input-arch text-2xl">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Almacén</label>
                                <select name="warehouse_id" required class="input-arch input-arch-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-[#ff9c00] uppercase tracking-widest mb-1">Área / Cliente (Opcional)</label>
                                <select name="area_id" class="input-arch input-arch-select text-[#ff9c00]">
                                    <option value="">Todas las Áreas</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" @selected(old('area_id') == $area->id)>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Responsable</label>
                                <select name="assigned_user_id" required class="input-arch input-arch-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(old('assigned_user_id') == $user->id)>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Tipo de Conteo</label>
                                <select name="type" x-model="countType" required class="input-arch input-arch-select">
                                    <option value="cycle">Cíclico (Por Pasillo)</option>
                                    <option value="full">Completo (Wall-to-Wall)</option>
                                    <option value="dirigido">Dirigido (Carga CSV)</option>
                                </select>
                            </div>
                        </div>

                        <div x-show="countType === 'cycle'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="p-6 bg-blue-50 border border-blue-100 rounded-2xl">
                                <label class="block text-[10px] font-bold text-blue-500 uppercase tracking-widest mb-2">Seleccionar Pasillo</label>
                                <select name="aisle" :required="countType === 'cycle'" class="w-full bg-white border-none rounded-xl py-3 px-4 text-[#2c3856] font-bold shadow-sm focus:ring-2 focus:ring-blue-200">
                                    <option value="">Seleccione un pasillo...</option>
                                    @foreach($aisles as $aisle)
                                        <option value="{{ $aisle }}" @selected(old('aisle') == $aisle)>Pasillo {{ $aisle }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div x-show="countType === 'dirigido'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="p-6 bg-[#fff8e6] border border-[#ffe0b2] rounded-2xl text-center">
                                <label class="block text-[10px] font-bold text-[#d97706] uppercase tracking-widest mb-4">Cargar Archivo de Ubicaciones</label>
                                <input type="file" name="locations_file" :required="countType === 'dirigido'" accept=".csv, .txt" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-[#ff9c00] file:text-white hover:file:bg-orange-600">
                                <div class="mt-4">
                                    <a href="{{ route('wms.physical-counts.template') }}" class="text-xs font-bold text-[#2c3856] hover:text-[#ff9c00] transition-colors border-b border-[#2c3856] hover:border-[#ff9c00]">
                                        <i class="fas fa-download mr-1"></i> Descargar Plantilla CSV
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-100">
                            <button type="submit" class="btn-nexus w-full py-4 text-sm uppercase tracking-widest shadow-lg shadow-[#2c3856]/20">
                                <i class="fas fa-check-circle mr-2"></i> Crear Sesión y Generar Tareas
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>