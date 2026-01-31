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

        .nexus-table { width: 100%; border-collapse: separate; border-spacing: 0 0.8rem; }
        .nexus-table thead th {
            font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 800;
            padding: 0 1.5rem 0.5rem 1.5rem; text-align: left;
        }
        .nexus-row {
            background: white; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .nexus-row td {
            padding: 1rem 1.5rem; vertical-align: middle; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6;
            background-color: white;
        }
        .nexus-row td:first-child { border-top-left-radius: 1rem; border-bottom-left-radius: 1rem; border-left: 1px solid #f3f4f6; }
        .nexus-row td:last-child { border-top-right-radius: 1rem; border-bottom-right-radius: 1rem; border-right: 1px solid #f3f4f6; }
        .nexus-row:hover { transform: scale(1.002); box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.05); z-index: 10; position: relative; }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-[50vw] h-full bg-gradient-to-l from-[#f8fafc] to-transparent"></div>
            <div class="absolute bottom-0 left-0 w-[40rem] h-[40rem] bg-[#ff9c00]/5 rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00]"></span>
                        <span class="text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Control de Inventarios</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-raleway font-black text-[#2c3856] leading-none">
                        Sesiones de <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">Conteo</span>
                    </h1>
                </div>

                <div class="mt-6 xl:mt-0">
                    <a href="{{ route('wms.physical-counts.create') }}" class="btn-nexus px-8 py-3 text-sm uppercase tracking-wider shadow-lg shadow-[#2c3856]/20">
                        <i class="fas fa-plus mr-2"></i> Nueva Sesión
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 p-8 mb-8 stagger-enter" style="animation-delay: 0.2s;">
                <form method="GET" action="{{ route('wms.physical-counts.index') }}" class="flex flex-col md:flex-row gap-8 items-end w-full">
                    <div class="w-full md:w-1/3">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Filtrar por Almacén</label>
                        <select name="warehouse_id" class="input-arch input-arch-select" onchange="this.form.submit()">
                            <option value="">Todos los Almacenes</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected($warehouseId == $warehouse->id)>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-1/3">
                        <label class="block text-[10px] font-bold text-[#ff9c00] uppercase tracking-widest mb-1">Filtrar por Área</label>
                        <select name="area_id" class="input-arch input-arch-select text-[#ff9c00]" onchange="this.form.submit()">
                            <option value="">Todas las Áreas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" @selected($areaId == $area->id)>{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pb-2">
                        <a href="{{ route('wms.physical-counts.index') }}" class="btn-ghost px-6 py-2 text-[10px] uppercase tracking-widest">
                            <i class="fas fa-undo mr-2"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 stagger-enter" style="animation-delay: 0.3s;">
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">Activas</p>
                        <p class="text-3xl font-black text-[#2c3856]">{{ $sessions->where('status', 'Pending')->count() }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500">
                        <i class="fas fa-tasks text-xl"></i>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-red-400 uppercase tracking-widest mb-1">Discrepancias</p>
                        <p class="text-3xl font-black text-[#2c3856]">{{ $sessions->sum(fn($s) => $s->tasks->where('status', 'discrepancy')->count()) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center text-red-500">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-green-400 uppercase tracking-widest mb-1">Resueltas</p>
                        <p class="text-3xl font-black text-[#2c3856]">{{ $sessions->sum(fn($s) => $s->tasks->where('status', 'resolved')->count()) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center text-green-500">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto stagger-enter" style="animation-delay: 0.4s;">
                <table class="nexus-table">
                    <thead>
                        <tr>
                            <th>Nombre / Almacén</th>
                            <th>Área</th>
                            <th>Tipo</th>
                            <th>Progreso</th>
                            <th>Asignado a</th>
                            <th>Creado por</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $session)
                            @php
                                $totalTasks = $session->tasks_count;
                                $resolvedTasks = $session->tasks->where('status', 'resolved')->count();
                                $progress = $totalTasks > 0 ? ($resolvedTasks / $totalTasks) * 100 : 0;
                            @endphp
                            <tr class="nexus-row">
                                <td>
                                    <p class="font-bold text-[#2c3856] text-sm">{{ $session->name }}</p>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mt-1">{{ $session->warehouse->name ?? 'N/A' }}</p>
                                </td>
                                <td>
                                    @if($session->area)
                                        <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">{{ $session->area->name }}</span>
                                    @else
                                        <span class="text-gray-400 text-[10px] italic">General</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">{{ Str::title($session->type) }}</span>
                                </td>
                                <td class="w-48">
                                    @if($totalTasks > 0)
                                        <div class="flex items-center gap-3">
                                            <div class="flex-grow bg-gray-100 rounded-full h-2">
                                                <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold text-gray-500">{{ number_format($progress, 0) }}%</span>
                                        </div>
                                    @else
                                        <span class="text-[10px] text-gray-400 uppercase font-bold">Sin tareas</span>
                                    @endif
                                </td>
                                <td class="text-sm font-medium text-gray-600">{{ $session->assignedUser->name ?? 'N/A' }}</td>
                                <td class="text-sm text-gray-500">{{ $session->user->name }}</td>
                                <td class="text-right">
                                    <a href="{{ route('wms.physical-counts.show', $session) }}" class="btn-ghost px-4 py-2 text-[10px] uppercase tracking-widest border-indigo-200 text-indigo-600 hover:bg-indigo-50 hover:border-indigo-300">
                                        Monitorear
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12">
                                    <div class="flex flex-col items-center justify-center opacity-50">
                                        <i class="fas fa-clipboard-list text-4xl mb-3 text-gray-300"></i>
                                        <p class="text-gray-500 font-medium">No se encontraron sesiones de conteo.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-8">
                {{ $sessions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>