@extends('layouts.app')

@section('content')

{{-- Estilos --}}
<style>
    :root {
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
        --color-text-primary: #2b2b2b;
        --color-background: #f3f4f6;
        --color-success: #10B981;
        --color-danger: #EF4444;
        --color-warning: #F59E0B;
    }
    body { background-color: var(--color-background); }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <header class="mb-8">
        <h1 class="text-4xl font-bold text-[var(--color-text-primary)]">Dashboard de Tickets IT</h1>
        <p class="text-gray-600 mt-2">Una vista general del rendimiento y estado de la mesa de ayuda.</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold text-center text-lg text-[var(--color-primary)] mb-4">Tickets por Estatus</h3>
            <div class="relative h-72"><canvas id="statusChart"></canvas></div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold text-center text-lg text-[var(--color-primary)] mb-4">Tickets por Prioridad</h3>
            <div class="relative h-72"><canvas id="priorityChart"></canvas></div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold text-center text-lg text-[var(--color-primary)] mb-4">Tickets por Categoría</h3>
            <div class="relative h-72"><canvas id="categoryChart"></canvas></div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg flex flex-col items-center justify-center text-center">
            <h3 class="font-bold text-lg text-[var(--color-primary)] mb-4">Tiempo Promedio de Resolución</h3>
            <i class="fas fa-hourglass-half text-5xl text-[var(--color-accent)] mb-4"></i>
            <p id="avgTime" class="text-5xl font-bold text-[var(--color-text-primary)]">--</p>
            <p class="text-gray-500 mt-2">horas</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg lg:col-span-2">
            <h3 class="font-bold text-center text-lg text-[var(--color-primary)] mb-4">Tickets Cerrados por Agente</h3>
            <div class="relative h-72"><canvas id="agentChart"></canvas></div>
        </div>

    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg mt-8">
        <h3 class="font-bold text-center text-lg text-[var(--color-primary)] mb-4">Tickets Creados en los Últimos 30 Días</h3>
        <div class="relative h-80"><canvas id="ticketsPerDayChart"></canvas></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch("{{ route('tickets.charts') }}")
            .then(response => response.json())
            .then(data => {
                
                // --- Gráfico de Estatus (Dona) ---
                new Chart(document.getElementById('statusChart'), {
                    type: 'doughnut', data: { labels: data.ticketsByStatus.labels, datasets: [{ data: data.ticketsByStatus.data, backgroundColor: ['#2c3856', '#ff9c00', '#666666', '#F59E0B'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }}}
                });

                // --- Gráfico de Prioridad (Tarta) ---
                new Chart(document.getElementById('priorityChart'), {
                    type: 'pie', data: { labels: data.ticketsByPriority.labels, datasets: [{ data: data.ticketsByPriority.data, backgroundColor: ['#10B981', '#F59E0B', '#EF4444'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }}}
                });

                // --- Gráfico de Categorías (Barras Polares) ---
                new Chart(document.getElementById('categoryChart'), {
                    type: 'polarArea', data: { labels: data.ticketsByCategory.labels, datasets: [{ data: data.ticketsByCategory.data, backgroundColor: ['#2c3856', '#4f628e', '#7a8eb8', '#a9b9e0', '#dce3ff'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }}}
                });

                // --- Tarjeta de Tiempo Promedio ---
                document.getElementById('avgTime').textContent = data.avgResolutionTime > 0 ? data.avgResolutionTime : 'N/A';

                // --- Gráfico de Agentes (Barras Horizontales) ---
                new Chart(document.getElementById('agentChart'), {
                    type: 'bar', data: { labels: data.ticketsByAgent.labels, datasets: [{ label: 'Tickets Cerrados', data: data.ticketsByAgent.data, backgroundColor: 'var(--color-accent)', borderRadius: 4 }] }, options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }}}
                });
                
                // --- Gráfico de Tickets por Día (Líneas) ---
                new Chart(document.getElementById('ticketsPerDayChart'), {
                    type: 'line', data: { labels: data.ticketsPerDay.labels, datasets: [{ label: 'Tickets Creados', data: data.ticketsPerDay.data, borderColor: 'var(--color-primary)', backgroundColor: 'rgba(44, 56, 86, 0.1)', fill: true, tension: 0.3 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }}}}
                });
            })
            .catch(error => console.error('Error al cargar los datos:', error));
    });
</script>
@endsection