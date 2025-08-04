@extends('layouts.app')

@section('content')

{{-- Estilos personalizados con la nueva paleta y ajustes de elegancia --}}
<style>
    :root {
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
        --color-text-primary: #2b2b2b;
        --color-text-secondary: #666666;
        --color-surface: #ffffff;
        --color-background: #f3f4f6; /* Gris muy claro para el fondo */
        --color-success: #10B981;
    }

    body {
        background-color: var(--color-background);
    }

    /* Botones */
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
    }
    .btn-accent {
        background-color: var(--color-accent);
        color: var(--color-surface);
    }
    .btn-accent:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
    }
    .btn-primary {
        background-color: var(--color-primary);
        color: var(--color-surface);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
    }
    .btn-export {
        background-color: var(--color-success);
        color: var(--color-surface);
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.7rem;
    }

    /* Input Pequeño */
    .form-input-sm {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem; /* Un poco menos redondeado */
    }

    /* Insignias de Estatus */
    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
    }
    .badge-programada { background-color: var(--color-primary); color: white; }
    .badge-ingresado { background-color: var(--color-accent); color: white; }
    .badge-no-ingresado { background-color: var(--color-text-secondary); color: white; }
    .badge-cancelada { background-color: var(--color-text-primary); color: white; }
    .badge-finalizada { background-color: var(--color-success); color: white; }

    /* Estilos para Formularios */
    .form-input {
        border-radius: 0.5rem;
        border-color: #e5e7eb;
        transition: all 0.3s ease;
    }
    .form-input:focus {
        --tw-ring-color: var(--color-accent);
        border-color: var(--color-accent);
    }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <header class="flex flex-col sm:flex-row items-center justify-between mb-8">
        <h1 class="text-4xl font-bold text-[var(--color-text-primary)]">Bitácora de Visitas</h1>
        <a href="{{ route('area_admin.visits.create') }}" class="btn btn-accent mt-4 sm:mt-0">
            <i class="fas fa-plus mr-2"></i> Crear Visita
        </a>
    </header>

    <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
        <h3 class="text-xl font-bold text-[var(--color-primary)] mb-4">Filtros de Búsqueda</h3>
        <form action="{{ route('area_admin.visits.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4 items-center">
            
            <input type="text" class="form-input form-input-sm" name="search" placeholder="Nombre, empresa..." value="{{ $filters['search'] ?? '' }}">
            <input type="date" class="form-input form-input-sm" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
            <input type="date" class="form-input form-input-sm" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
            <select name="status" class="form-input form-input-sm">
                <option value="">Todos los Estatus</option>
                <option value="Programada" @selected(($filters['status'] ?? '') == 'Programada')>Programada</option>
                <option value="Ingresado" @selected(($filters['status'] ?? '') == 'Ingresado')>Ingresado</option>
                <option value="Finalizada" @selected(($filters['status'] ?? '') == 'Finalizada')>Finalizada</option>
                <option value="No ingresado" @selected(($filters['status'] ?? '') == 'No ingresado')>No Ingresado</option>
                <option value="Cancelada" @selected(($filters['status'] ?? '') == 'Cancelada')>Cancelada</option>
            </select>

            <div class="flex items-center space-x-2">
                <button type="submit" class="btn btn-sm btn-primary w-full text-center">Filtrar</button>
                <a href="{{ route('area_admin.visits.index') }}" class="btn btn-sm bg-gray-200 text-gray-700 hover:bg-gray-300 w-full text-center">Limpiar</a>
                <a href="{{ route('area_admin.visits.export', request()->query()) }}" class="btn btn-sm btn-export w-full text-center">CSV</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[var(--color-primary)] text-white">
                    <tr>
                        <th scope="col" class="p-4 font-semibold text-left uppercase tracking-wider">Visitante</th>
                        <th scope="col" class="p-4 font-semibold text-left uppercase tracking-wider">Empresa</th>
                        <th scope="col" class="p-4 font-semibold text-left uppercase tracking-wider">Entrada</th>
                        <th scope="col" class="p-4 font-semibold text-left uppercase tracking-wider">Salida</th>
                        <th scope="col" class="p-4 font-semibold text-left uppercase tracking-wider">Estatus</th>
                        <th scope="col" class="p-4 font-semibold text-center uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($visits as $visit)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-4 whitespace-nowrap"><p class="font-bold text-[var(--color-text-primary)]">{{ $visit->visitor_name }} {{ $visit->visitor_last_name }}</p><p class="text-xs text-[var(--color-text-secondary)]">{{ $visit->email }}</p></td>
                            <td class="p-4 whitespace-nowrap text-[var(--color-text-secondary)]">{{ $visit->company ?? 'N/A' }}</td>
                            <td class="p-4 whitespace-nowrap text-[var(--color-text-secondary)]">{{ $visit->visit_datetime->format('d/m/Y h:i A') }}</td>
                            <td class="p-4 whitespace-nowrap text-[var(--color-text-secondary)]">{{ $visit->exit_datetime ? $visit->exit_datetime->format('d/m/Y h:i A') : '—' }}</td>
                            <td class="p-4 whitespace-nowrap">
                                <span class="badge badge-{{ strtolower(str_replace(' ', '-', $visit->status)) }}">
                                    {{ $visit->status }}
                                </span>
                            </td>
                            <td class="p-4 whitespace-nowrap text-center">
                                <form action="{{ route('area_admin.visits.destroy', $visit) }}" method="POST" onsubmit="return confirm('¿Confirmas la eliminación de esta visita?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors" title="Eliminar"><i class="fas fa-trash-alt fa-lg"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center p-8 text-gray-500">No se encontraron visitas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t">
            {!! $visits->appends(request()->query())->links() !!}
        </div>
    </div>

    <div class="mt-12">
        <h2 class="text-3xl font-bold text-[var(--color-text-primary)] mb-6">Métricas Visuales</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white p-6 rounded-xl shadow-lg"><h4 class="font-bold text-center text-gray-700 mb-2">Visitas en los Últimos 30 Días</h4><div class="relative h-80"><canvas id="visitsPerDayChart"></canvas></div></div>
            <div class="bg-white p-6 rounded-xl shadow-lg"><h4 class="font-bold text-center text-gray-700 mb-2">Visitas por Estatus</h4><div class="relative h-80"><canvas id="statusChart"></canvas></div></div>
            <div class="bg-white p-6 rounded-xl shadow-lg"><h4 class="font-bold text-center text-gray-700 mb-2">Top 5 Empresas</h4><div class="relative h-80"><canvas id="companyChart"></canvas></div></div>
            <div class="bg-white p-6 rounded-xl shadow-lg flex flex-col items-center justify-center h-80"><div class="bg-blue-100 rounded-full p-4 mb-4"><i class="fas fa-clock text-4xl text-blue-500"></i></div><div><h4 class="text-gray-500 font-semibold text-center text-lg">Duración Promedio</h4><p id="avgDuration" class="text-5xl font-bold text-[var(--color-text-primary)] text-center">...</p></div></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch("{{ route('area_admin.visits.charts') }}")
            .then(response => response.json())
            .then(data => {
                // Tarjeta de Duración Promedio
                const avgDurationEl = document.getElementById('avgDuration');
                if(data.averageDuration > 0) {
                    const hours = Math.floor(data.averageDuration / 60);
                    const minutes = data.averageDuration % 60;
                    avgDurationEl.textContent = `${hours}h ${minutes}m`;
                } else {
                    avgDurationEl.textContent = 'N/A';
                }

                // Gráfico de Visitas por Día (Líneas)
                new Chart(document.getElementById('visitsPerDayChart'), {
                    type: 'line',
                    data: {
                        labels: data.visitsPerDay.labels,
                        datasets: [{
                            label: 'Visitas',
                            data: data.visitsPerDay.data,
                            borderColor: '#2c3856',
                            backgroundColor: 'rgba(44, 56, 86, 0.1)',
                            fill: true,
                            tension: 0.1
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });

                // Gráfico de Estatus (Dona)
                new Chart(document.getElementById('statusChart'), {
                    type: 'doughnut',
                    data: {
                        labels: data.visitsByStatus.labels,
                        datasets: [{
                            label: 'Visitas',
                            data: data.visitsByStatus.data,
                            backgroundColor: ['#2c3856', '#ff9c00', '#10B981', '#666666', '#2b2b2b'],
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
                });

                // Gráfico de Compañías (Barras)
                new Chart(document.getElementById('companyChart'), {
                    type: 'bar',
                    data: {
                        labels: data.visitsByCompany.labels,
                        datasets: [{
                            label: 'Nº de Visitas',
                            data: data.visitsByCompany.data,
                            backgroundColor: '#ff9c00',
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            })
            .catch(error => console.error('Error al cargar datos de gráficos:', error));
    });
</script>
@endsection