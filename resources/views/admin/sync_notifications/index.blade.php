<x-app-layout>
    <x-slot name="header"></x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@700;800;900&display=swap');

        :root {
            --minmer-navy: #2c3856;
            --minmer-orange: #ff9c00;
            --minmer-grey: #666666;
            --minmer-dark: #2b2b2b;
        }

        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .shadow-soft { box-shadow: 0 20px 40px -10px rgba(44, 56, 86, 0.1); }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    <div class="min-h-screen text-[#2b2b2b] font-montserrat pb-20 relative overflow-x-hidden">

        {{-- Background Effects --}}
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#2c3856] rounded-full blur-[150px] opacity-5"></div>
            <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-[#ff9c00] rounded-full blur-[150px] opacity-5"></div>
        </div>

        <div class="max-w-[1800px] mx-auto px-6 pt-10 relative z-10">
            
            {{-- Header Section --}}
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 border-b border-gray-200 pb-6">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <div class="h-1 w-8 bg-[#ff9c00]"></div>
                        <p class="text-xs font-bold text-[#666666] uppercase tracking-[0.2em]">Centro de Control</p>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-raleway font-black text-[#2c3856] mb-1 leading-none">
                        LOGS DE <span class="text-[#ff9c00]">SINCRONIZACIÓN</span>
                    </h1>
                    <p class="text-[#666666] text-lg font-medium">Monitoreo de eventos WMS ↔ FnF</p>
                </div>

                {{-- Filters Card --}}
                <div class="bg-white shadow-soft border border-gray-100 rounded-[2rem] p-4 mt-8 xl:mt-0 w-full xl:w-auto">
                    <form action="{{ route('admin.sync-notifications.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-center">
                        
                        <div class="relative group w-full md:w-auto">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="search" placeholder="Buscar..." value="{{ request('search') }}" 
                                class="w-full md:w-64 bg-gray-50 hover:bg-gray-100 text-[#2c3856] font-semibold text-sm py-3 pl-10 pr-6 rounded-xl border-none focus:ring-2 focus:ring-[#ff9c00]/50 transition-all placeholder-gray-400">
                        </div>

                        <div class="h-8 w-px bg-gray-200 hidden md:block"></div>

                        <div class="relative group w-full md:w-auto">
                            <select name="type" onchange="this.form.submit()" class="w-full md:w-48 appearance-none bg-gray-50 hover:bg-gray-100 text-[#2c3856] font-bold text-sm py-3 pl-6 pr-10 rounded-xl border-none focus:ring-0 transition-colors cursor-pointer">
                                <option value="">Todos los Tipos</option>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                        </div>

                        <div class="relative group w-full md:w-auto">
                            <select name="status" onchange="this.form.submit()" class="w-full md:w-48 appearance-none bg-gray-50 hover:bg-gray-100 text-[#2c3856] font-bold text-sm py-3 pl-6 pr-10 rounded-xl border-none focus:ring-0 transition-colors cursor-pointer">
                                <option value="">Todos los Estados</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendientes</option>
                                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resueltos</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                        </div>

                        @if(request()->anyFilled(['search', 'type', 'status']))
                            <a href="{{ route('admin.sync-notifications.index') }}" class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 transition-colors" title="Limpiar Filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Main Content --}}
            <div class="bg-white rounded-[2.5rem] shadow-soft border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50">
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Tipo</th>
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Mensaje</th>
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Detalles Técnicos</th>
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Estado</th>
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Fecha</th>
                                <th class="px-8 py-5 text-right text-xs font-bold text-[#666666] uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($notifications as $notification)
                            @php
                                $statusColor = 'bg-blue-50 text-blue-600 border-blue-100';
                                if (Str::contains(strtolower($notification->type), 'error')) {
                                    $statusColor = 'bg-red-50 text-red-600 border-red-100';
                                } elseif (Str::contains(strtolower($notification->type), 'warning')) {
                                    $statusColor = 'bg-orange-50 text-[#ff9c00] border-orange-100';
                                } elseif (Str::contains(strtolower($notification->type), 'success')) {
                                    $statusColor = 'bg-green-50 text-green-600 border-green-100';
                                }
                            @endphp
                            <tr class="group hover:bg-gray-50/50 transition-colors">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <span class="inline-flex px-4 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wide border {{ $statusColor }}">
                                        {{ $notification->type }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-bold text-[#2c3856] max-w-xs truncate" title="{{ $notification->message }}">
                                        {{ $notification->message }}
                                    </p>
                                </td>
                                <td class="px-8 py-6">
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" class="text-xs font-bold text-[#ff9c00] hover:text-orange-600 transition-colors flex items-center gap-1">
                                            <span>Ver Payload</span>
                                            <i class="fas fa-chevron-down text-[10px] transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                                        </button>
                                        <div x-show="open" @click.away="open = false" 
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 translate-y-2"
                                             x-transition:enter-end="opacity-100 translate-y-0"
                                             class="absolute top-full left-0 mt-2 w-96 bg-[#2c3856] rounded-xl shadow-2xl z-50 p-4 border border-gray-700">
                                            <pre class="text-[10px] text-gray-300 font-mono overflow-auto max-h-60 custom-scrollbar">{{ json_encode($notification->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    @if($notification->resolved)
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                            <span class="text-sm font-bold text-gray-600">Resuelto</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full bg-[#ff9c00] animate-pulse"></div>
                                            <span class="text-sm font-bold text-gray-600">Pendiente</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <p class="text-sm font-bold text-[#2c3856]">{{ $notification->created_at->timezone(config('app.timezone', 'America/Mexico_City'))->locale('es')->isoFormat('D MMM YYYY') }}</p>
                                    <p class="text-xs text-gray-400">{{ $notification->created_at->timezone(config('app.timezone', 'America/Mexico_City'))->format('h:i a') }}</p>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                        @if(!$notification->resolved)
                                            <form action="{{ route('admin.sync-notifications.resolve', $notification->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors" title="Marcar como Resuelto">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.sync-notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('¿Eliminar permanentemente?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors" title="Eliminar Registro">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-8 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-20 h-20 rounded-full bg-gray-50 flex items-center justify-center mb-6">
                                            <i class="fas fa-clipboard-check text-3xl text-gray-300"></i>
                                        </div>
                                        <h3 class="text-xl font-raleway font-black text-[#2c3856] mb-2">Sin Registros</h3>
                                        <p class="text-gray-400 max-w-xs mx-auto">No hay notificaciones de sincronización que coincidan con tus filtros.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($notifications->hasPages())
                <div class="px-8 py-6 border-t border-gray-100 bg-gray-50/50">
                    {{ $notifications->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
