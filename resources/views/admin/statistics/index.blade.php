<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Estadísticas y Bitácora de Actividad') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Resumen General</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-100 p-4 rounded-lg shadow">
                            <h4 class="font-bold text-gray-700">Total Usuarios</h4>
                            <p class="text-3xl font-bold">{{ $totalUsers }}</p>
                        </div>
                        <div class="bg-green-100 p-4 rounded-lg shadow">
                            <h4 class="font-bold text-gray-700">Total Carpetas</h4>
                            <p class="text-3xl font-bold">{{ $totalFolders }}</p>
                        </div>
                        <div class="bg-yellow-100 p-4 rounded-lg shadow">
                            <h4 class="font-bold text-gray-700">Total Archivos</h4>
                            <p class="text-3xl font-bold">{{ $totalFiles }}</p>
                        </div>
                        <div class="bg-purple-100 p-4 rounded-lg shadow">
                            <h4 class="font-bold text-gray-700">Total Enlaces</h4>
                            <p class="text-3xl font-bold">{{ $totalLinks }}</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg shadow mb-6">
                        <h4 class="font-bold text-gray-700 mb-2">Top 3 de Usuarios con más actividad:</h4>
                        <ul class="list-disc list-inside">
                            @forelse($topUsers as $topUser)
                                <li class="text-xl">
                                    {{ $topUser->user->name ?? 'Usuario Eliminado' }} ({{ $topUser->total_activity }} acciones)
                                </li>
                            @empty
                                <li class="text-xl">No hay actividad registrada en este periodo.</li>
                            @endforelse
                        </ul>
                    </div>

                    <h3 class="text-lg font-bold mt-8 mb-4">Bitácora de Actividad</h3>
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div class="flex flex-wrap items-end gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Desde</label>
                                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">Hasta</label>
                                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="area-filter" class="block text-sm font-medium text-gray-700">Área:</label>
                                <select id="area-filter" name="area" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Todas</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" {{ $filterArea == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="user-type-filter" class="block text-sm font-medium text-gray-700">Tipo de Usuario:</label>
                                <select id="user-type-filter" name="user_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Todos</option>
                                    @foreach($userTypes as $key => $label)
                                        <option value="{{ $key }}" {{ $filterUserType == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                                <input type="text" name="search" id="search" value="{{ $searchQuery }}" placeholder="Buscar en la bitácora..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
                             <a href="{{ route('admin.statistics.charts', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Ver Gráficos
                            </a>
                            <a href="{{ route('admin.statistics.export-csv', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Exportar CSV
                            </a>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto bg-gray-50 rounded-lg shadow">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalles</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($activities as $activity)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $activity->created_at->format('d M Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $activity->user->name ?? 'Usuario Eliminado' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $activity->action }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            @if($activity->details)
                                                @foreach($activity->details as $key => $value)
                                                    <strong>{{ Str::title(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}<br>
                                                @endforeach
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formElements = document.querySelectorAll('#start_date, #end_date, #area-filter, #user-type-filter, #search');
            formElements.forEach(element => {
                element.addEventListener('change', function() {
                    applyFilters();
                });
                if (element.id === 'search') {
                    element.addEventListener('input', debounce(function() {
                        applyFilters();
                    }, 500));
                }
            });

            function applyFilters() {
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                const area = document.getElementById('area-filter').value;
                const userType = document.getElementById('user-type-filter').value;
                const search = document.getElementById('search').value;

                const queryParams = new URLSearchParams({
                    start_date: startDate,
                    end_date: endDate,
                    area: area,
                    user_type: userType,
                    search: search
                }).toString();
                
                window.location.href = `{{ route('admin.statistics.index') }}?${queryParams}`;
            }

            function debounce(func, timeout = 300) {
                let timer;
                return (...args) => {
                    clearTimeout(timer);
                    timer = setTimeout(() => { func.apply(this, args); }, timeout);
                };
            }
        });
    </script>
</x-app-layout>