<x-app-layout>
    <x-slot name="header"></x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .nexus-card { background: white; border-radius: 1.5rem; box-shadow: 0 10px 30px -5px rgba(44, 56, 86, 0.05); border: 1px solid #f3f4f6; }
        .nexus-table { width: 100%; border-collapse: separate; border-spacing: 0 0.8rem; }
        .nexus-table thead th { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 800; padding: 0 1.5rem 0.5rem 1.5rem; text-align: left; }
        .nexus-row { background: white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02); transition: all 0.2s; }
        .nexus-row:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
        .nexus-row td { padding: 1rem 1.5rem; vertical-align: middle; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; background-color: white; }
        .nexus-row td:first-child { border-top-left-radius: 1rem; border-bottom-left-radius: 1rem; border-left: 1px solid #f3f4f6; }
        .nexus-row td:last-child { border-top-right-radius: 1rem; border-bottom-right-radius: 1rem; border-right: 1px solid #f3f4f6; }
        
        .btn-nexus { background: #2c3856; color: white; border-radius: 1rem; padding: 0.75rem 1.5rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(44, 56, 86, 0.2); }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(44, 56, 86, 0.3); }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative">
        <div class="max-w-7xl mx-auto px-4 md:px-6 pt-6 md:pt-10">
            
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-4">
                <div>
                    <h1 class="text-4xl md:text-5xl font-raleway font-black text-[#2c3856] mb-2">
                        Solicitudes <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">de Servicio</span>
                    </h1>
                    <p class="text-gray-500 font-medium">Gestiona y crea solicitudes de servicios independientes.</p>
                </div>
                <a href="{{ route('wms.service-requests.create') }}" class="btn-nexus flex items-center gap-2">
                    <i class="fas fa-plus"></i> Nueva Solicitud
                </a>
            </div>

            <!-- Content -->
            <div class="overflow-x-auto pb-4">
                <table class="nexus-table min-w-full">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Almacén</th>
                            <th>Estatus</th>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr class="nexus-row group">
                            <td class="font-black text-[#2c3856]">{{ $request->folio }}</td>
                            <td class="font-bold text-gray-600">{{ $request->area->name ?? 'N/A' }}</td>
                            <td class="text-sm font-medium text-gray-500">{{ $request->warehouse->name ?? 'N/A' }}</td>
                            <td>
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide
                                    {{ $request->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $request->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $request->status === 'invoiced' ? 'bg-blue-100 text-blue-700' : '' }}">
                                    {{ ucfirst(__($request->status)) }}
                                </span>
                            </td>
                            <td class="text-sm font-mono text-gray-400">{{ $request->requested_at ? $request->requested_at->format('d/m/Y H:i') : '-' }}</td>
                            <td class="text-sm font-medium text-gray-500 flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-500">
                                    {{ substr($request->user->name ?? 'S', 0, 1) }}
                                </div>
                                {{ $request->user->name ?? 'Sistema' }}
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('wms.service-requests.show', $request) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('wms.service-requests.pdf', $request) }}" target="_blank" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Descargar PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-8">
                                <div class="flex flex-col items-center justify-center text-gray-300">
                                    <i class="fas fa-inbox text-4xl mb-3"></i>
                                    <p class="font-raleway font-bold text-lg">No hay solicitudes registradas</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
