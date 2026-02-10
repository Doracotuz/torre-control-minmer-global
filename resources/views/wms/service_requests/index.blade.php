<x-app-layout>
    <x-slot name="header"></x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }

        .nexus-card { background: white; border-radius: 1.25rem; box-shadow: 0 4px 20px -4px rgba(44, 56, 86, 0.06); border: 1px solid #eef0f4; }

        .btn-nexus { background: #2c3856; color: white; border-radius: 0.75rem; padding: 0.65rem 1.25rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.7rem; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(44, 56, 86, 0.15); display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-1px); box-shadow: 0 8px 15px -3px rgba(44, 56, 86, 0.25); }

        /* Stat Cards - Minmer Global Brand Palette */
        .stat-card {
            border-radius: 1rem; padding: 1.5rem; position: relative; overflow: hidden;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); cursor: default;
            background: #ffffff; border: 1px solid rgba(44,56,86,0.08);
            box-shadow: 0 2px 12px -3px rgba(44,56,86,0.06);
        }
        .stat-card::after {
            content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #2c3856, #ff9c00);
            transform: scaleX(0); transform-origin: left; transition: transform 0.4s ease;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px -8px rgba(44,56,86,0.12); }
        .stat-card:hover::after { transform: scaleX(1); }

        .stat-card .stat-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
        .stat-card .stat-icon {
            width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
            font-size: 1rem; background: #2c3856; color: #ff9c00;
            box-shadow: 0 4px 10px -2px rgba(44,56,86,0.25);
        }
        .stat-card .stat-badge {
            font-size: 0.55rem; text-transform: uppercase; letter-spacing: 0.1em;
            font-weight: 700; color: #666666; background: rgba(44,56,86,0.05);
            padding: 0.2rem 0.6rem; border-radius: 2rem;
        }

        .stat-card .stat-value {
            font-family: 'Raleway', sans-serif; font-weight: 900; font-size: 2.25rem;
            line-height: 1; color: #2b2b2b; margin-bottom: 0.25rem;
        }
        .stat-card .stat-label {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em;
            font-weight: 600; color: #666666;
        }

        /* Entrance */
        .stat-card { opacity: 0; transform: translateY(12px); animation: fadeUp 0.4s ease-out forwards; }
        .stat-card:nth-child(1) { animation-delay: 0.05s; }
        .stat-card:nth-child(2) { animation-delay: 0.12s; }
        .stat-card:nth-child(3) { animation-delay: 0.19s; }
        .stat-card:nth-child(4) { animation-delay: 0.26s; }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        /* Desktop Table */
        .nexus-table { width: 100%; border-collapse: separate; border-spacing: 0 0.4rem; }
        .nexus-table thead th { font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; font-weight: 800; padding: 0 1.25rem 0.5rem; text-align: left; }
        .nexus-row { transition: all 0.2s; }
        .nexus-row:hover { transform: translateY(-1px); box-shadow: 0 6px 12px -4px rgba(0,0,0,0.04); }
        .nexus-row td { padding: 0.75rem 1.25rem; vertical-align: middle; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; background: white; }
        .nexus-row td:first-child { border-left: 1px solid #f3f4f6; border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem; }
        .nexus-row td:last-child { border-right: 1px solid #f3f4f6; border-top-right-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }

        /* Mobile Cards */
        .mobile-card { background: white; border: 1px solid #eef0f4; border-radius: 1rem; padding: 1rem 1.25rem; margin-bottom: 0.75rem; box-shadow: 0 2px 8px -2px rgba(44,56,86,0.04); }
        .mobile-card .mc-row { display: flex; justify-content: space-between; align-items: center; padding: 0.3rem 0; }
        .mobile-card .mc-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; font-weight: 700; }
        .mobile-card .mc-value { font-size: 0.8rem; font-weight: 600; color: #374151; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20">
        <div class="w-full max-w-[1600px] mx-auto px-4 md:px-8 pt-6 md:pt-10">

            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
                <div>
                    <h1 class="text-3xl md:text-5xl font-raleway font-black text-[#2c3856] mb-1">
                        Solicitudes <span class="text-[#ff9c00]">de Servicio</span>
                    </h1>
                    <p class="text-[#666666] font-medium text-sm">Gestiona y crea solicitudes de servicios independientes.</p>
                </div>
                <a href="{{ route('wms.service-requests.create') }}" class="btn-nexus">
                    <i class="fas fa-plus"></i> Nueva Solicitud
                </a>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                        <span class="stat-badge">Global</span>
                    </div>
                    <p class="stat-value" data-count="{{ $stats['total'] }}">0</p>
                    <p class="stat-label">Total Solicitudes</p>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <span class="stat-badge">En espera</span>
                    </div>
                    <p class="stat-value" data-count="{{ $stats['pending'] }}">0</p>
                    <p class="stat-label">Pendientes</p>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <span class="stat-badge">Cerradas</span>
                    </div>
                    <p class="stat-value" data-count="{{ $stats['completed'] }}">0</p>
                    <p class="stat-label">Completadas</p>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                        <span class="stat-badge">Cobradas</span>
                    </div>
                    <p class="stat-value" data-count="{{ $stats['invoiced'] }}">0</p>
                    <p class="stat-label">Facturadas</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="nexus-card p-4 mb-6">
                <form method="GET" action="{{ route('wms.service-requests.index') }}" class="grid grid-cols-2 md:grid-cols-6 gap-3 items-end">
                    <div>
                        <label class="block text-[10px] uppercase tracking-widest font-bold text-gray-400 mb-1">Cliente</label>
                        <select name="area_id" class="w-full rounded-lg border-gray-200 text-xs focus:border-[#2c3856] focus:ring-[#2c3856] py-2">
                            <option value="">Todos</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ request('area_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase tracking-widest font-bold text-gray-400 mb-1">Almacén</label>
                        <select name="warehouse_id" class="w-full rounded-lg border-gray-200 text-xs focus:border-[#2c3856] focus:ring-[#2c3856] py-2">
                            <option value="">Todos</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase tracking-widest font-bold text-gray-400 mb-1">Estatus</label>
                        <select name="status" class="w-full rounded-lg border-gray-200 text-xs focus:border-[#2c3856] focus:ring-[#2c3856] py-2">
                            <option value="">Todos</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completada</option>
                            <option value="invoiced" {{ request('status') === 'invoiced' ? 'selected' : '' }}>Facturada</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase tracking-widest font-bold text-gray-400 mb-1">Desde</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-lg border-gray-200 text-xs focus:border-[#2c3856] focus:ring-[#2c3856] py-2">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase tracking-widest font-bold text-gray-400 mb-1">Hasta</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-lg border-gray-200 text-xs focus:border-[#2c3856] focus:ring-[#2c3856] py-2">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-nexus flex-1 justify-center text-[10px] py-2">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route('wms.service-requests.index') }}" class="py-2 px-3 rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50 transition-colors flex items-center text-xs" title="Limpiar">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Desktop Table (hidden on mobile) -->
            <div class="nexus-card p-5 hidden md:block">
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
                            <td class="font-black text-[#2c3856] text-sm">
                                <div>{{ $request->display_folio }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-wider {{ $request->source_badge ?? 'bg-gray-100' }} px-1.5 py-0.5 rounded inline-block mt-1">
                                    {{ $request->source_type }}
                                </div>
                            </td>
                            <td class="font-semibold text-gray-600 text-sm">{{ $request->area->name ?? 'N/A' }}</td>
                            <td class="text-sm text-gray-500">{{ $request->warehouse->name ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $s = strtolower($request->status);
                                    $color = 'bg-gray-50 text-gray-600 border border-gray-200';
                                    if(in_array($s, ['completed', 'packed'])) $color = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                    if(in_array($s, ['pending', 'receiving', 'picking'])) $color = 'bg-amber-50 text-amber-700 border border-amber-200';
                                    if(in_array($s, ['cancelled', 'rejected'])) $color = 'bg-red-50 text-red-600 border border-red-200';
                                    if($s === 'invoiced') $color = 'bg-blue-50 text-blue-700 border border-blue-200';
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $color }}">
                                    {{ ucfirst(__($request->status)) }}
                                </span>
                            </td>
                            <td class="text-xs font-mono text-gray-400">{{ $request->display_date ? $request->display_date->format('d/m/Y H:i') : '-' }}</td>
                            <td class="text-sm text-gray-500">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-[#2c3856] flex items-center justify-center text-[9px] font-bold text-white">
                                        {{ substr($request->user->name ?? 'S', 0, 1) }}
                                    </div>
                                    {{ $request->user->name ?? 'Sistema' }}
                                </div>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ $request->show_route }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors text-xs"><i class="fas fa-eye"></i></a>
                                    @if($request->pdf_route)
                                    <a href="{{ $request->pdf_route }}" target="_blank" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors text-xs"><i class="fas fa-file-pdf"></i></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-16">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 bg-gray-50 rounded-xl flex items-center justify-center mb-3">
                                        <i class="fas fa-inbox text-xl text-gray-300"></i>
                                    </div>
                                    <p class="font-raleway font-bold text-base text-gray-400 mb-1">No hay solicitudes</p>
                                    <p class="text-gray-300 text-xs mb-4">Crea tu primera solicitud de servicio.</p>
                                    <a href="{{ route('wms.service-requests.create') }}" class="btn-nexus text-[10px]"><i class="fas fa-plus"></i> Crear Solicitud</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards (visible only on mobile) -->
            <div class="md:hidden space-y-3">
                @forelse($requests as $request)
                <div class="mobile-card">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <div class="font-black text-[#2c3856] text-sm">{{ $request->display_folio }}</div>
                            <div class="text-[9px] text-gray-400">{{ $request->source_type }}</div>
                        </div>
                        @php
                            $s = strtolower($request->status);
                            $color = 'bg-gray-50 text-gray-600 border border-gray-200';
                            if(in_array($s, ['completed', 'packed'])) $color = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                            if(in_array($s, ['pending', 'receiving', 'picking'])) $color = 'bg-amber-50 text-amber-700 border border-amber-200';
                            if(in_array($s, ['cancelled', 'rejected'])) $color = 'bg-red-50 text-red-600 border border-red-200';
                            if($s === 'invoiced') $color = 'bg-blue-50 text-blue-700 border border-blue-200';
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wide {{ $color }}">
                            {{ ucfirst(__($request->status)) }}
                        </span>
                    </div>
                    <div class="mc-row"><span class="mc-label">Cliente</span><span class="mc-value">{{ $request->area->name ?? 'N/A' }}</span></div>
                    <div class="mc-row"><span class="mc-label">Almacén</span><span class="mc-value">{{ $request->warehouse->name ?? 'N/A' }}</span></div>
                    <div class="mc-row"><span class="mc-label">Fecha</span><span class="mc-value font-mono text-xs text-gray-400">{{ $request->display_date ? $request->display_date->format('d/m/Y H:i') : '-' }}</span></div>
                    <div class="mc-row"><span class="mc-label">Usuario</span><span class="mc-value">{{ $request->user->name ?? 'Sistema' }}</span></div>
                    <div class="flex gap-2 mt-3 pt-3 border-t border-gray-100">
                        <a href="{{ $request->show_route }}" class="btn-nexus text-[9px] flex-1 justify-center py-2"><i class="fas fa-eye"></i> Ver</a>
                        @if($request->pdf_route)
                        <a href="{{ $request->pdf_route }}" target="_blank" class="py-2 px-4 rounded-lg border border-gray-200 text-red-500 text-[9px] font-bold uppercase tracking-wide hover:bg-red-50 transition-colors flex items-center gap-1 justify-center"><i class="fas fa-file-pdf"></i> PDF</a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="nexus-card p-10 text-center">
                    <div class="w-14 h-14 bg-gray-50 rounded-xl flex items-center justify-center mb-3 mx-auto">
                        <i class="fas fa-inbox text-xl text-gray-300"></i>
                    </div>
                    <p class="font-raleway font-bold text-base text-gray-400 mb-1">No hay solicitudes</p>
                    <p class="text-gray-300 text-xs mb-4">Crea tu primera solicitud.</p>
                    <a href="{{ route('wms.service-requests.create') }}" class="btn-nexus text-[10px]"><i class="fas fa-plus"></i> Crear</a>
                </div>
                @endforelse
            </div>

            @if($requests->hasPages())
            <div class="mt-6">
                {{ $requests->links() }}
            </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.stat-value[data-count]').forEach(el => {
                const target = parseInt(el.dataset.count) || 0;
                if (target === 0) { el.textContent = '0'; return; }
                const duration = 900;
                const start = performance.now();
                const step = (now) => {
                    const p = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - p, 3);
                    el.textContent = Math.floor(eased * target);
                    if (p < 1) requestAnimationFrame(step);
                    else el.textContent = target;
                };
                requestAnimationFrame(step);
            });
        });
    </script>
</x-app-layout>
