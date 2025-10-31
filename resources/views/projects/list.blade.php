<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tablero Kanban de Proyectos
            </h2>
            <a href="{{ route('projects.create') }}" class="px-5 py-2 bg-[#ff9c00] text-white font-semibold rounded-lg shadow-md hover:bg-orange-600 transition-colors">
                <i class="fas fa-plus mr-2"></i> Nuevo Proyecto
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="kanbanBoard">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                @php
                    $statusStyles = [
                        'Planeación' => ['bg' => 'bg-gray-500', 'border' => 'border-gray-500', 'text' => 'text-gray-100'],
                        'En Progreso' => ['bg' => 'bg-blue-600', 'border' => 'border-blue-600', 'text' => 'text-blue-100'],
                        'En Pausa' => ['bg' => 'bg-yellow-500', 'border' => 'border-yellow-500', 'text' => 'text-yellow-100'],
                        'Completado' => ['bg' => 'bg-green-600', 'border' => 'border-green-600', 'text' => 'text-green-100'],
                        'Cancelado' => ['bg' => 'bg-red-600', 'border' => 'border-red-600', 'text' => 'text-red-100'],
                    ];
                @endphp

                @foreach($statuses as $status)
                @php $style = $statusStyles[$status] ?? ['bg' => 'bg-gray-400', 'border' => 'border-gray-400', 'text' => 'text-gray-100']; @endphp
                
                <div class="bg-gray-100 rounded-xl shadow-lg flex flex-col">
                    <div class="p-4 rounded-t-xl {{ $style['bg'] }} {{ $style['text'] }} shadow-md">
                        <h3 class="text-lg font-bold flex justify-between items-center">
                            {{ $status }}
                            <span class="text-sm font-semibold {{ $style['bg'] }} {{ $style['text'] }} bg-opacity-50 rounded-full px-2 py-0.5">
                                <span class="column-count">{{ $projectsByStatus[$status]->count() }}</span>
                            </span>
                        </h3>
                    </div>
                    
                    <div class="p-4 space-y-4 min-h-[70vh] kanban-column flex-1" data-status="{{ $status }}">
                        @foreach($projectsByStatus[$status] as $project)
                        @php
                            $completedTasks = $project->tasks->where('status', 'Completada')->count();
                            $totalTasks = $project->tasks->count();
                            $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
                            
                            $health = 'on-track';
                            $healthColor = 'text-green-500';
                            if ($project->due_date) {
                                $daysLeft = now()->diffInDays($project->due_date, false);
                                if ($daysLeft < 0 && $project->status !== 'Completado') {
                                    $health = 'overdue';
                                    $healthColor = 'text-red-500';
                                } elseif ($daysLeft <= 7 && $project->status !== 'Completado') {
                                    $health = 'at-risk';
                                    $healthColor = 'text-yellow-500';
                                }
                            }
                        @endphp
                        
                        <div class="bg-white p-4 rounded-lg shadow-md border-l-4 {{ $style['border'] }} cursor-grab relative project-card transition-all duration-150 ease-in-out hover:shadow-xl hover:-translate-y-1" data-id="{{ $project->id }}">
                            
                            @can('update', $project)
                                <a href="{{ route('projects.edit', $project) }}" class="absolute top-2 right-2 text-gray-400 hover:text-indigo-600 p-1 rounded-full transition-colors opacity-50 hover:opacity-100" title="Editar Proyecto">
                                    <i class="fas fa-pencil-alt fa-sm"></i>
                                </a>
                            @endcan
                            
                            <div class="flex items-center mb-2">
                                <i class="fas fa-circle fa-xs mr-2 {{ $healthColor }}" title="Salud del proyecto"></i>
                                <a href="{{ route('projects.show', $project) }}" class="font-bold text-gray-900 hover:text-indigo-700 transition-colors line-clamp-2">
                                    {{ $project->name }}
                                </a>
                            </div>
                            
                            <p class="text-sm text-gray-600 mt-2 line-clamp-3">
                                {{ $project->description }}
                            </p>

                            @if($totalTasks > 0)
                            <div class="mt-4">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Progreso Tareas</span>
                                    <span>{{ $completedTasks }} / {{ $totalTasks }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                            @endif
                            
                            <div class="flex justify-between items-center mt-4 pt-3 border-t">
                                <div class="text-xs text-gray-500 flex items-center">
                                    <i class="far fa-calendar-alt mr-2"></i>
                                    <span>{{ $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('d M, Y') : 'N/A' }}</span>
                                </div>
                                
                                @if($project->leader)
                                <div class="flex-shrink-0" title="Líder: {{ $project->leader->name }}">
                                    @if ($project->leader->profile_photo_path)
                                        <img class="h-8 w-8 rounded-full object-cover" src="{{ Storage::disk('s3')->url($project->leader->profile_photo_path) }}" alt="{{ $project->leader->name }}">
                                    @else
                                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-600 text-white font-bold text-xs">
                                            @php
                                                $words = explode(" ", $project->leader->name);
                                                $initials = "";
                                                foreach (array_slice($words, 0, 2) as $w) { $initials .= mb_substr($w, 0, 1); }
                                            @endphp
                                            {{ $initials }}
                                        </span>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('kanbanBoard', () => ({
            init() {
                document.querySelectorAll('.kanban-column').forEach(column => {
                    new Sortable(column, {
                        group: 'kanban',
                        animation: 150,
                        ghostClass: 'bg-blue-100 border-2 border-dashed border-blue-400 opacity-70',
                        onEnd: this.handleDrop.bind(this),
                    });
                });
            },

            handleDrop(event) {
                const projectId = event.item.dataset.id;
                const newStatus = event.to.dataset.status;
                const originalStatus = event.from.dataset.status;

                if (newStatus === originalStatus) return;

                const fromColumn = event.from;
                const toColumn = event.to;

                fetch(`/projects/${projectId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => {
                    if (!response.ok) {
                        fromColumn.appendChild(event.item);
                        throw new Error('Falló la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log(data.message);
                    this.updateCounts(fromColumn, toColumn);
                    
                    const card = event.item;
                    const statusStyles = {
                        'Planeación': 'border-gray-500',
                        'En Progreso': 'border-blue-600',
                        'En Pausa': 'border-yellow-500',
                        'Completado': 'border-green-600',
                        'Cancelado': 'border-red-600',
                    };
                    card.classList.remove('border-gray-500', 'border-blue-600', 'border-yellow-500', 'border-green-600', 'border-red-600');
                    if (statusStyles[newStatus]) {
                        card.classList.add(statusStyles[newStatus]);
                    }
                })
                .catch(error => {
                    console.error('Error al actualizar el estatus:', error);
                    alert('Hubo un error al actualizar el proyecto.');
                    fromColumn.appendChild(event.item);
                });
            },

            updateCounts(fromColumn, toColumn) {
                const fromCountEl = fromColumn.parentElement.querySelector('.column-count');
                const toCountEl = toColumn.parentElement.querySelector('.column-count');

                fromCountEl.innerText = fromColumn.querySelectorAll('.project-card').length;
                toCountEl.innerText = toColumn.querySelectorAll('.project-card').length;
            }
        }));
    });
</script>
</x-app-layout>