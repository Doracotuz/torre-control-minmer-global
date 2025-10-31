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
                
                @foreach($statuses as $status)
                <div class="bg-gray-100 rounded-lg shadow-md">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-700">{{ $status }}
                            <span class="text-sm font-normal text-gray-500">(<span class="column-count">{{ $projectsByStatus[$status]->count() }}</span>)</span>
                        </h3>
                    </div>
                    
                    <div class="p-4 space-y-4 min-h-[60vh] kanban-column" data-status="{{ $status }}">
                        @foreach($projectsByStatus[$status] as $project)
                        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-500 cursor-grab relative project-card" data-id="{{ $project->id }}">
                            @can('update', $project)
                                <a href="{{ route('projects.edit', $project) }}" class="absolute top-2 right-2 text-gray-400 hover:text-indigo-600 p-1 rounded-full transition-colors">
                                    <i class="fas fa-pencil-alt fa-sm"></i>
                                </a>
                            @endcan                            
                            <a href="{{ route('projects.show', $project) }}" class="font-bold text-gray-800 hover:text-blue-600 hover:underline transition-colors">
                                {{ $project->name }}
                            </a>
                            <p class="text-sm text-gray-500 mt-2">
                                {{ Str::limit($project->description, 80) }}
                            </p>
                            <div class="flex justify-between items-center mt-4">
                                <div class="text-xs text-gray-500">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    {{ $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('d M, Y') : 'N/A' }}
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
                        ghostClass: 'bg-blue-100',
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Falló la respuesta del servidor');
                    return response.json();
                })
                .then(data => {
                    console.log(data.message);
                    this.updateCounts(fromColumn, toColumn);
                })
                .catch(error => {
                    console.error('Error al actualizar el estatus:', error);
                    fromColumn.appendChild(event.item);
                    alert('Hubo un error al actualizar el proyecto.');
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