<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    {{ $project->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Detalles y progreso del proyecto.</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('projects.list') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-400">
                    <i class="fas fa-arrow-left mr-2"></i> Volver al Tablero
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="projectDetailManager">
        <div class="w-full mx-auto sm:px-6 lg:px-8">

            {{-- Mensajes de éxito --}}
            @if (session('success_task'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert"><p>{{ session('success_task') }}</p></div>
            @endif
            @if (session('success_comment'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert"><p>{{ session('success_comment') }}</p></div>
            @endif
             @if (session('success_file'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert"><p>{{ session('success_file') }}</p></div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-lg flex items-center">
                    <div class="mr-4">
                        @php
                            $statusInfo = [
                                'Planeación' => ['color' => 'bg-gray-500', 'icon' => 'fa-list-alt'],
                                'En Progreso' => ['color' => 'bg-blue-500', 'icon' => 'fa-tasks'],
                                'En Pausa' => ['color' => 'bg-yellow-500', 'icon' => 'fa-pause-circle'],
                                'Completado' => ['color' => 'bg-green-500', 'icon' => 'fa-check-circle'],
                                'Cancelado' => ['color' => 'bg-red-500', 'icon' => 'fa-times-circle'],
                            ][$project->status];
                        @endphp
                        <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $statusInfo['color'] }} text-white">
                            <i class="fas {{ $statusInfo['icon'] }} fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Estatus</div>
                        <div class="text-lg font-bold text-gray-800">{{ $project->status }}</div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <div class="text-sm text-gray-500">Progreso General</div>
                    <div class="text-lg font-bold text-gray-800">{{ number_format($progress, 0) }}%</div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
                 <div class="bg-white p-6 rounded-xl shadow-lg">
                    <div class="text-sm text-gray-500">Fecha de Inicio</div>
                    <div class="text-lg font-bold text-gray-800">{{ \Carbon\Carbon::parse($project->start_date)->format('d M, Y') }}</div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <div class="text-sm text-gray-500">Fecha de Entrega</div>
                    <div class="text-lg font-bold text-gray-800">{{ $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('d M, Y') : 'No definida' }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white p-8 rounded-xl shadow-lg">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-3">Descripción del Proyecto</h3>
                        <div class="prose max-w-none text-gray-600">
                            {!! nl2br(e($project->description)) ?: '<p>No hay descripción para este proyecto.</p>' !!}
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-xl shadow-lg">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-3">Línea de Tiempo del Proyecto</h3>
                        <div id="taskTimelineChart"></div>
                    </div>

                    <div class="bg-white p-8 rounded-xl shadow-lg">
                        <div class="flex justify-between items-center mb-4 border-b pb-3">
                            <h3 class="text-xl font-bold text-gray-800">Detalle de Tareas</h3>
                            <button @click="isTaskModalOpen = true" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow-md hover:bg-indigo-700">
                                <i class="fas fa-plus mr-1"></i> Añadir Tarea
                            </button>
                        </div>
                        <div class="space-y-4">
                            @forelse ($project->tasks as $task)
                                <div class="border rounded-lg p-4 flex items-center justify-between transition-all hover:shadow-md">
                                    <div class="flex items-center">
                                        <div class="mr-4">
                                            @if($task->status === 'Completada')
                                                <i class="fas fa-check-circle text-green-500 fa-lg"></i>
                                            @else
                                                <i class="far fa-circle text-gray-400 fa-lg"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800 {{ $task->status === 'Completada' ? 'line-through text-gray-500' : '' }}">{{ $task->name }}</p>
                                            @if($task->description)
                                            <p class="text-sm text-gray-600 mt-1 pl-1">
                                                {{ Str::limit($task->description, 100) }}
                                            </p>
                                            @endif                                     
                                            <div x-data="{ open: false }" class="relative inline-block text-left">
                                                <button @click="open = !open" type="button" class="text-xs font-semibold px-2 py-1 rounded-full transition-transform transform hover:scale-105 {{ $task->status === 'Pendiente' ? 'bg-yellow-200 text-yellow-800' : '' }} {{ $task->status === 'En Progreso' ? 'bg-blue-200 text-blue-800' : '' }} {{ $task->status === 'Completada' ? 'bg-green-200 text-green-800' : '' }}">
                                                    {{ $task->status }} <i class="fas fa-chevron-down fa-xs ml-1"></i>
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-transition class="origin-top-left absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                                    <div class="py-1">
                                                        @foreach (['Pendiente', 'En Progreso', 'Completada'] as $status)
                                                            @if ($task->status !== $status)
                                                                <form action="{{ route('projects.tasks.status.update', $task) }}" method="POST" class="block">
                                                                    @csrf @method('PATCH')
                                                                    <input type="hidden" name="status" value="{{ $status }}">
                                                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Marcar como "{{ $status }}"</button>
                                                                </form>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @php
                                                    $priorityStyles = [
                                                        'Alta' => ['icon' => 'fas fa-fire', 'color' => 'text-red-500', 'label' => 'Prioridad Alta'],
                                                        'Media' => ['icon' => 'fas fa-minus', 'color' => 'text-yellow-500', 'label' => 'Prioridad Media'],
                                                        'Baja' => ['icon' => 'fas fa-chevron-down', 'color' => 'text-blue-500', 'label' => 'Prioridad Baja'],
                                                    ][$task->priority];
                                                @endphp
                                                <span title="{{ $priorityStyles['label'] }}" class="{{ $priorityStyles['color'] }}">
                                                    <i class="{{ $priorityStyles['icon'] }}"></i>
                                                </span>                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <span class="text-sm text-gray-500 hidden md:block">Vence: {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M, Y') : 'N/A' }}</span>
                                        @if($task->assignee)
                                            <div class="flex-shrink-0" title="Asignado a: {{ $task->assignee->name }}">
                                                 <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-600 text-white font-bold text-xs">
                                                    @php $words = explode(" ", $task->assignee->name); $initials = ""; foreach (array_slice($words, 0, 2) as $w) { $initials .= mb_substr($w, 0, 1); } @endphp
                                                    {{ $initials }}
                                                </span>
                                            </div>
                                        @endif
                                        <button @click="editingTask = {{ $task }}; isTaskEditModalOpen = true" class="text-gray-400 hover:text-indigo-600" title="Editar Tarea"><i class="fas fa-pencil-alt"></i></button>
                                        <form action="{{ route('projects.tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta tarea?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-600" title="Eliminar Tarea"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-gray-500 border-2 border-dashed rounded-lg p-8"><i class="fas fa-folder-open fa-2x mb-2"></i><p>Aún no se han añadido tareas a este proyecto.</p></div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Información Clave</h3>
                        <ul class="space-y-3 text-sm">
                            <li class="flex justify-between"><span class="text-gray-500">Líder:</span><span class="font-semibold text-gray-800">{{ $project->leader->name ?? 'No asignado' }}</span></li>
                            <li class="flex justify-between"><span class="text-gray-500">Presupuesto:</span><span class="font-semibold text-gray-800">${{ number_format($project->budget, 2) ?? 'No definido' }}</span></li>
                            <li class="flex justify-between"><span class="text-gray-500">Tareas Totales:</span><span class="font-semibold text-gray-800">{{ $project->tasks->count() }}</span></li>
                        </ul>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Actividad Reciente</h3>
                        <form action="{{ route('projects.comments.store', $project) }}" method="POST" class="mb-6">
                            @csrf
                            <textarea name="body" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Escribe una actualización o comentario..."></textarea>
                            <div class="flex justify-end mt-2">
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow-md hover:bg-indigo-700">Comentar</button>
                            </div>
                        </form>
                        <div class="space-y-6">
                            @forelse ($project->comments as $comment)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mr-3">
                                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-500 text-white font-bold">
                                             @php $words = explode(" ", $comment->user->name); $initials = ""; foreach (array_slice($words, 0, 2) as $w) { $initials .= mb_substr($w, 0, 1); } @endphp
                                            {{ $initials }}
                                        </span>
                                    </div>
                                    <div class="bg-gray-100 rounded-lg p-3 flex-1">
                                        <p class="text-sm font-semibold text-gray-900">{{ $comment->user->name }}</p>
                                        <p class="text-sm text-gray-700 mt-1">{!! nl2br(e($comment->body)) !!}</p>
                                        <p class="text-xs text-gray-400 mt-2 text-right">{{ $comment->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 text-center">No hay comentarios todavía. ¡Sé el primero!</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Archivos del Proyecto</h3>
                        <form action="{{ route('projects.files.store', $project) }}" method="POST" enctype="multipart/form-data" class="mb-4 border-b pb-4">
                            @csrf
                            <label for="file-upload" class="block text-sm font-medium text-gray-700">Añadir un nuevo archivo</label>
                            <div class="mt-1 flex items-center">
                                <input type="file" name="file" id="file-upload" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                                <button type="submit" class="ml-4 px-3 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-lg shadow-sm hover:bg-indigo-700">Subir</button>
                            </div>
                            @error('file') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </form>
                        <ul class="space-y-3">
                            @forelse ($project->files as $file)
                                <li class="flex items-center justify-between text-sm">
                                    <div class="flex items-center truncate"><i class="fas fa-file-alt text-gray-400 mr-3"></i><span class="text-gray-800 truncate" title="{{ $file->file_name }}">{{ Str::limit($file->file_name, 30) }}</span></div>
                                    <a href="{{ route('projects.files.download', $file) }}" class="text-indigo-600 hover:underline font-semibold">Descargar</a>
                                </li>
                            @empty
                                <li class="text-sm text-gray-500">No hay archivos adjuntos.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    

        {{-- Modal para Añadir Nueva Tarea --}}
        <div x-show="isTaskModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="isTaskModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isTaskModalOpen = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="isTaskModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form action="{{ route('projects.tasks.store', $project) }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Añadir Nueva Tarea</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="task_name" class="block text-sm font-medium text-gray-700">Nombre de la Tarea</label>
                                    <input type="text" name="name" id="task_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="task_description" class="block text-sm font-medium text-gray-700">Descripción (Opcional)</label>
                                    <textarea name="description" id="task_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="task_due_date" class="block text-sm font-medium text-gray-700">Fecha de Entrega</label>
                                        <input type="date" name="due_date" id="task_due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="task_priority" class="block text-sm font-medium text-gray-700">Prioridad</label>
                                        <select name="priority" id="task_priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option>Baja</option><option selected>Media</option><option>Alta</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label for="task_assignee" class="block text-sm font-medium text-gray-700">Asignar a</label>
                                    <select name="assignee_id" id="task_assignee" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">-- Sin asignar --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#2c3856] text-base font-medium text-white hover:bg-gray-800 sm:ml-3 sm:w-auto sm:text-sm">Guardar Tarea</button>
                            <button type="button" @click="isTaskModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal para Editar Tarea --}}
        <div x-show="isTaskEditModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-edit" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="isTaskEditModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isTaskEditModalOpen = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="isTaskEditModalOpen" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <template x-if="editingTask">
                        <form :action="`/projects/tasks/${editingTask.id}`" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title-edit">Editar Tarea</h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="edit_task_name" class="block text-sm font-medium text-gray-700">Nombre de la Tarea</label>
                                        <input type="text" name="name" id="edit_task_name" required x-model="editingTask.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="edit_task_description" class="block text-sm font-medium text-gray-700">Descripción (Opcional)</label>
                                        <textarea name="description" id="edit_task_description" rows="3" x-model="editingTask.description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="edit_task_due_date" class="block text-sm font-medium text-gray-700">Fecha de Entrega</label>
                                            <input type="date" name="due_date" id="edit_task_due_date" x-model="editingTask.due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <label for="edit_task_priority" class="block text-sm font-medium text-gray-700">Prioridad</label>
                                            <select name="priority" id="edit_task_priority" x-model="editingTask.priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <option>Baja</option><option>Media</option><option>Alta</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="edit_task_assignee" class="block text-sm font-medium text-gray-700">Asignar a</label>
                                        <select name="assignee_id" id="edit_task_assignee" x-model="editingTask.assignee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">-- Sin asignar --</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#2c3856] text-base font-medium text-white hover:bg-gray-800 sm:ml-3 sm:w-auto sm:text-sm">Actualizar Tarea</button>
                                <button type="button" @click="isTaskEditModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('projectDetailManager', () => ({
                isTaskModalOpen: false, isTaskEditModalOpen: false, editingTask: null,
            }));
        });

        document.addEventListener('DOMContentLoaded', function () {
            const timelineData = @json($timelineData);
            const chartElement = document.querySelector("#taskTimelineChart");

            if (timelineData && timelineData.length > 0 && chartElement) {
                
                const timelineOptions = {
                    series: [{ data: timelineData }],
                    chart: {
                        height: 100 + (timelineData.length * 50), // Aumentamos un poco la altura por tarea
                        type: 'rangeBar',
                        toolbar: { show: true, tools: { download: false, selection: true, zoom: true, zoomin: true, zoomout: true, pan: true, reset: true } }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 5,
                            barHeight: '50%',
                        }
                    },
                    // --- CAMBIO 1: Deshabilitamos las etiquetas DENTRO de las barras ---
                    dataLabels: {
                        enabled: false, 
                    },
                    xaxis: {
                        type: 'datetime',
                        min: new Date(@json($timelineMinDate)).getTime(),
                        max: new Date(@json($timelineInitialMaxDate)).getTime(),
                        labels: { datetimeUTC: false, format: 'dd MMM yy', style: { colors: '#6b7280' } }
                    },
                    tooltip: { theme: 'dark', x: { format: 'dd MMMM yyyy' } },
                    // --- CAMBIO 2: Habilitamos y estilizamos el eje Y para que muestre los nombres ---
                    yaxis: {
                        show: true,
                        labels: {
                            style: {
                                colors: ['#4b5563'], // Color del texto
                                fontSize: '13px',
                                fontWeight: 600,
                            },
                            // Trunca el texto si es muy largo para que no se desborde
                            formatter: function (value) {
                                if (value.length > 25) {
                                    return value.substr(0, 25) + '...';
                                }
                                return value;
                            }
                        }
                    },
                    grid: { 
                        row: { colors: ['#f3f4f5', '#fff'], opacity: 1 },
                        borderColor: '#e7e7e7',
                        strokeDashArray: 4,
                        // Añadimos un padding a la izquierda para que los nombres no se corten
                        padding: { left: 20 }
                    },
                    legend: { show: false }
                };

                const chart = new ApexCharts(chartElement, timelineOptions);
                chart.render();

            } else if (chartElement) {
                chartElement.innerHTML = '<div class="text-center text-gray-500 p-8 border-2 border-dashed rounded-lg">No hay tareas con fechas para mostrar en la línea de tiempo.</div>';
            }
        });
    </script>

</x-app-layout>