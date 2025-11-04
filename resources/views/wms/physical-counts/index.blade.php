<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                Sesiones de Conteo Físico
            </h2>
            <a href="{{ route('wms.physical-counts.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-lg shadow-md hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i> Nueva Sesión de Conteo
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white p-4 rounded-xl shadow-lg border">
                <form method="GET" action="{{ route('wms.physical-counts.index') }}" class="flex items-end space-x-4">
                    <div>
                        <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Filtrar por Almacén</label>
                        <select name="warehouse_id" id="warehouse_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" onchange="this.form.submit()">
                            <option value="">Todos los Almacenes</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <a href="{{ route('wms.physical-counts.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Limpiar
                    </a>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-lg border flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-blue-100"><i class="fas fa-tasks text-xl text-blue-600"></i></div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Sesiones Activas</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $sessions->where('status', 'Pending')->count() }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg border flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-yellow-100"><i class="fas fa-exclamation-triangle text-xl text-yellow-600"></i></div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Discrepancias Totales</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $sessions->sum(fn($s) => $s->tasks->where('status', 'discrepancy')->count()) }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg border flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-green-100"><i class="fas fa-check-circle text-xl text-green-600"></i></div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tareas Resueltas</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $sessions->sum(fn($s) => $s->tasks->where('status', 'resolved')->count()) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre / Almacén</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progreso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asignado a</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creado por</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($sessions as $session)
                            @php
                                $totalTasks = $session->tasks_count;
                                $resolvedTasks = $session->tasks->where('status', 'resolved')->count();
                                $progress = $totalTasks > 0 ? ($resolvedTasks / $totalTasks) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $session->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $session->warehouse->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">{{ Str::title($session->type) }}</span></td>
                                <td class="px-6 py-4">
                                    @if($totalTasks > 0)
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500 ml-2">{{ number_format($progress, 0) }}%</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">Sin tareas</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $session->assignedUser->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $session->user->name }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('wms.physical-counts.show', $session) }}" class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-700 font-semibold text-xs rounded-full hover:bg-indigo-200">
                                        Monitorear
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-gray-500">No se encontraron sesiones de conteo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $sessions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>