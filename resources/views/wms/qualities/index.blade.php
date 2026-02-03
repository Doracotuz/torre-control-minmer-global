<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@700;800;900&display=swap');
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        .shadow-soft { box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.1); }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative">
        
        <div class="max-w-[1800px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 border-b border-gray-200 pb-6">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <div class="h-1 w-8 bg-[#ff9c00]"></div>
                        <p class="text-xs font-bold text-[#666666] uppercase tracking-[0.2em]">Catálogos Maestros</p>
                    </div>
                    <h1 class="text-5xl font-raleway font-black text-[#2c3856] mb-1 leading-none">
                        Catálogo de <span class="text-[#ff9c00]">Calidades</span>
                    </h1>
                </div>

                <div class="flex flex-wrap gap-3 mt-6 xl:mt-0">
                    <a href="{{ route('wms.dashboard') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-[#666666] font-bold rounded-full shadow-sm hover:shadow-md hover:border-[#2c3856] hover:text-[#2c3856] transition-all">
                        <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
                    </a>
                    <a href="{{ route('wms.qualities.create') }}" class="flex items-center gap-2 px-6 py-2.5 bg-[#2c3856] text-white font-bold rounded-full shadow-lg shadow-[#2c3856]/20 hover:bg-[#1a253a] hover:-translate-y-0.5 transition-all">
                        <i class="fas fa-plus"></i> <span>Nueva Calidad</span>
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-xl shadow-sm font-medium flex items-center gap-3">
                    <i class="fas fa-check-circle text-xl"></i>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                 <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl shadow-sm font-medium flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                    <p>{{ session('error') }}</p>
                 </div>
            @endif

            <div class="bg-white rounded-[2rem] shadow-soft border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <form method="GET" action="{{ route('wms.qualities.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="w-full md:w-1/3">
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Cliente</label>
                            <select name="area_id" class="w-full pl-4 pr-4 py-3 rounded-xl border-gray-200 bg-white text-[#2c3856] font-bold focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all cursor-pointer">
                                <option value="">-- Todos --</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-1/3">
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Búsqueda</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
                                <input type="text" name="search" placeholder="Nombre de calidad..." value="{{ request('search') }}" class="w-full pl-10 pr-4 py-3 rounded-xl border-gray-200 bg-white focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all">
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-8 py-3 bg-[#2c3856] text-white font-bold rounded-xl hover:bg-[#1f2940] transition-all shadow-md">
                                Filtrar
                            </button>
                            <a href="{{ route('wms.qualities.index') }}" class="w-12 flex items-center justify-center py-3 bg-white border border-gray-200 text-[#666666] rounded-xl hover:bg-gray-50 transition-all" title="Limpiar">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Estatus</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-[#666666] uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($qualities as $quality)
                                <tr class="hover:bg-blue-50/30 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#fff8e6] text-[#b36b00] border border-[#ff9c00]/20">
                                            {{ $quality->area->name ?? 'Global' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-[#2c3856]">{{ $quality->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($quality->is_available)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                                <i class="fas fa-check-circle text-[10px]"></i> Disponible
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                                <i class="fas fa-ban text-[10px]"></i> Bloqueado
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $quality->description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('wms.qualities.edit', $quality) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-[#ff9c00] hover:border-[#ff9c00] transition-all shadow-sm mr-2">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <form action="{{ route('wms.qualities.destroy', $quality) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta calidad?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-red-500 hover:border-red-500 transition-all shadow-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-certificate text-4xl mb-3 opacity-50"></i>
                                            <p class="text-sm font-medium">No hay calidades registradas.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                    {{ $qualities->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>