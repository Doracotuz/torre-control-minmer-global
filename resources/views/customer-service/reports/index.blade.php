<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-[#2c3856] leading-tight">Historial de Auditorías Completadas</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-2xl shadow-xl">
                
                <div class="mb-6">
                    <form method="GET" action="{{ route('customer-service.audit-reports.index') }}">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   name="search" 
                                   placeholder="Buscar por Guía, SO o Cliente..." 
                                   value="{{ request('search') }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm pl-10">
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Guía</th>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Fecha de Carga</th>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Auditor(es)</th>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">SOs</th>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Clientes</th>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($guias as $guia)
                                @php
                                    $orders = $guia->plannings->pluck('order')->filter();
                                    $audits = $orders->pluck('audits')->flatten();
                                    $auditors = $audits->pluck('auditor.name')->filter()->unique()->join(', ');
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium text-gray-900">{{ $guia->guia }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $guia->plannings->first()?->fecha_carga?->format('d/m/Y') ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $auditors ?: 'N/A' }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $orders->pluck('so_number')->unique()->join(', ') }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $orders->pluck('customer_name')->unique()->join(', ') }}</td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('customer-service.audit-reports.show', $guia) }}" class="text-indigo-600 hover:text-indigo-900" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-gray-500 py-6">
                                        No se encontraron auditorías completadas con los filtros aplicados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $guias->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>