<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    Dashboard de Conteo: <span class="text-indigo-600">{{ $session->name }}</span>
                </h2>
                <p class="text-sm text-gray-500">
                    Asignado a: {{ $session->assignedUser->name ?? 'N/A' }} | 
                    Almacén: {{ $session->warehouse->name ?? 'N/A' }}
                </p>
            </div>
            <a href="{{ route('wms.physical-counts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 font-semibold text-sm rounded-lg shadow-sm hover:bg-gray-300">
                <i class="fas fa-arrow-left mr-2"></i> Volver a Sesiones
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="adjustmentHandler()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))<div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert"><p>{{ session('success') }}</p></div>@endif
            @if (session('error'))<div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert"><p>{{ session('error') }}</p></div>@endif

            @php
                $total = $session->tasks->count();
                $pending = $session->tasks->where('status', 'pending')->count();
                $discrepancy = $session->tasks->where('status', 'discrepancy')->count();
                $resolved = $session->tasks->where('status', 'resolved')->count();
                $progress = $total > 0 ? (($resolved + $discrepancy) / $total) * 100 : 0;
            @endphp

            <div class="bg-white p-6 rounded-xl shadow-lg border">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Progreso de la Sesión</h3>
                <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                    <div class="bg-green-600 h-4 rounded-full" style="width: {{ $progress }}%"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                    <div><p class="text-sm text-gray-500">Total Tareas</p><p class="text-3xl font-bold">{{ $total }}</p></div>
                    <div><p class="text-sm text-gray-500">Pendientes</p><p class="text-3xl font-bold">{{ $pending }}</p></div>
                    <div><p class="text-sm text-red-500">Discrepancias</p><p class="text-3xl font-bold text-red-600">{{ $discrepancy }}</p></div>
                    <div><p class="text-sm text-green-500">Resueltas</p><p class="text-3xl font-bold text-green-600">{{ $resolved }}</p></div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border" x-data="{ filter: 'all' }">
                <div class="p-4 border-b flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-700">Filtrar:</span>
                    <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-3 py-1 rounded-full text-xs font-semibold">Todas ({{ $total }})</button>
                    <button @click="filter = 'pending'" :class="filter === 'pending' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-3 py-1 rounded-full text-xs font-semibold">Pendientes ({{ $pending }})</button>
                    <button @click="filter = 'discrepancy'" :class="filter === 'discrepancy' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-3 py-1 rounded-full text-xs font-semibold">Discrepancias ({{ $discrepancy }})</button>
                    <button @click="filter = 'resolved'" :class="filter === 'resolved' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-3 py-1 rounded-full text-xs font-semibold">Resueltas ({{ $resolved }})</button>
                </div>
                
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estatus</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ubicación</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">LPN</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU / Producto</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Sistema</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Contado</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Diferencia</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($session->tasks as $task)
                            <tr x-show="filter === 'all' || filter === '{{ $task->status }}'" x-transition>
                                <td class="px-4 py-4"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full @if($task->status == 'discrepancy') bg-yellow-100 text-yellow-800 @elseif($task->status == 'resolved') bg-green-100 text-green-800 @else bg-gray-100 text-gray-800 @endif">
                                    {{ $task->status_in_spanish }}
                                </span></td>
                                <td class="px-4 py-4 font-mono text-sm">{{ $task->location ? "{$task->location->aisle}-{$task->location->rack}-{$task->location->shelf}-{$task->location->bin}" : 'N/A' }}</td>
                                <td class="px-4 py-4 font-mono text-indigo-600 font-semibold">{{ $task->pallet->lpn ?? 'N/A' }}</td>
                                <td class="px-4 py-4"><div class="text-sm font-medium text-gray-900">{{ $task->product->name }}</div><div class="text-xs text-gray-500 font-mono">{{ $task->product->sku }}</div></td>
                                <td class="px-4 py-4 text-center font-bold text-lg text-gray-700">{{ $task->expected_quantity }}</td>
                                <td class="px-4 py-4 text-center font-bold text-lg @if($task->records->last() && $task->records->last()->counted_quantity != $task->expected_quantity) text-red-600 @else text-gray-700 @endif">
                                    {{ $task->records->last()->counted_quantity ?? '-' }}
                                </td>
                                <td class="px-4 py-4 text-center font-bold text-lg @if($task->records->last() && ($task->records->last()->counted_quantity - $task->expected_quantity) != 0) text-red-600 @else text-gray-700 @endif">
                                    {{ $task->records->last() ? $task->records->last()->counted_quantity - $task->expected_quantity : '-' }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    @if ($task->status == 'pending' || ($task->status == 'discrepancy' && $task->records->count() < 3))
                                        <a href="{{ route('wms.physical-counts.tasks.perform', $task) }}" class="text-indigo-600 font-semibold hover:underline">
                                            {{ $task->records->count() > 0 ? 'Re-contar' : 'Contar' }}
                                        </a>
                                    @elseif ($task->status == 'discrepancy')
                                        <button @click="openModal({{ $task->id }})" type="button" class="font-semibold text-red-600 hover:underline">Ajustar</button>
                                    @elseif ($task->status == 'resolved')
                                        <span class="text-green-600 font-semibold"><i class="fas fa-check-circle"></i></span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-60" style="display: none;">
            <div @click.away="resetModal()" class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col" x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                <div class="p-6 border-b flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Resolver Discrepancia</h2>
                    <button @click="resetModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                </div>
                
                <div class="p-6 overflow-y-auto">
                    <div x-show="isLoading" class="text-center text-gray-500 py-10"><i class="fas fa-spinner fa-spin text-3xl"></i><p class="mt-2">Cargando LPNs...</p></div>
                    
                    <div x-show="!isLoading && !selectedLpnItem">
                        <p class="text-gray-700 mb-1">Se encontraron múltiples LPNs con este producto en la ubicación.</p>
                        <p class="text-gray-700 mb-4 font-semibold">Por favor, selecciona la tarima correcta a la que deseas aplicar el ajuste.</p>
                        <div class="space-y-3">
                            <template x-for="item in candidateLpns" :key="item.id">
                                <button @click="selectLpnItem(item)" class="w-full text-left p-4 border rounded-lg hover:bg-indigo-50 hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                    <div class="flex justify-between items-center">
                                        <p class="font-mono font-bold text-lg text-indigo-600" x-text="item.pallet.lpn"></p>
                                        <p>Cant. Actual: <span class="font-bold text-lg" x-text="item.quantity"></span></p>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <p>Calidad: <span class="font-semibold" x-text="item.quality.name"></span></p>
                                        <p>Pedimento A4: <span class="font-mono" x-text="item.pallet.purchase_order ? item.pallet.purchase_order.pedimento_a4 : 'N/A'"></span></p>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <template x-if="selectedLpnItem">   
                        <div x-transition>
                            <form :action="`/wms/inventory/pallet-items/${selectedLpnItem.id}/adjust`" method="POST">
                                @csrf
                                <div class="mb-4 bg-gray-50 p-4 rounded-lg border">
                                    <p class="text-sm">Ajustando LPN: <strong class="font-mono text-indigo-600" x-text="selectedLpnItem.pallet.lpn"></strong></p>
                                    <div class="mt-2 pt-2 border-t text-sm">
                                        <p>Cantidad Actual en este LPN: <strong x-text="selectedLpnItem.quantity"></strong></p>
                                        <p>La nueva cantidad sugerida para este LPN (basado en el conteo total) es:</p>
                                        <p class="text-3xl font-bold text-center text-green-600 my-2" x-text="adjustmentAmount"></p>
                                    </div>
                                </div>
                                <input type="hidden" name="new_quantity" :value="adjustmentAmount">
                                <div class="mt-4">
                                    <label for="reason" class="block font-medium text-gray-700">Motivo del Ajuste <span class="text-red-500">*</span></label>
                                    <textarea name="reason" id="reason" rows="3" class="w-full rounded-md border-gray-300 mt-1 focus:border-indigo-500 focus:ring-indigo-500" required>Ajuste por discrepancia en Conteo Cíclico.</textarea>
                                </div>
                                <div class="px-6 py-4 bg-gray-50 -mx-6 -mb-6 mt-6 flex justify-end gap-4 rounded-b-2xl">
                                    <button type="button" @click="selectedLpnItem = null" class="px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300">&larr; Seleccionar otro LPN</button>
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700">Confirmar y Guardar Ajuste</button>
                                </div>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        function adjustmentHandler() {
            return {
                isModalOpen: false, isLoading: false, candidateLpns: [],
                selectedLpnItem: null, taskToAdjust: null,
                tasks: @json($session->tasks->keyBy('id')),
                adjustmentAmount: 0,

                async openModal(taskId) {
                    this.resetModal();
                    this.isModalOpen = true;
                    this.isLoading = true;
                    this.taskToAdjust = this.tasks[taskId];
                    
                    try {
                        const response = await fetch(`/wms/physical-counts/tasks/${taskId}/candidate-lpns`);
                        const data = await response.json();
                        if (!response.ok) throw new Error('No se pudieron cargar los LPNs.');
                        
                        this.candidateLpns = data;
                        if (data.length === 1) {
                            this.selectLpnItem(data[0]);
                        } else if (data.length === 0) {
                             throw new Error('Error fatal: No se encontró ningún LPN candidato para este producto y ubicación. No se puede ajustar.');
                        }
                    } catch (error) {
                        alert(error.message);
                        this.isModalOpen = false;
                    } finally {
                        this.isLoading = false;
                    }
                },
                selectLpnItem(item) {
                    this.selectedLpnItem = item;
                    this.calculateAdjustment();
                },
                calculateAdjustment() {
                    if (!this.taskToAdjust || !this.selectedLpnItem) return;
                    const totalCounted = this.taskToAdjust.records[this.taskToAdjust.records.length - 1].counted_quantity;
                    let otherPalletsTotal = 0;
                    this.candidateLpns.forEach(candidate => {
                        if (candidate.id !== this.selectedLpnItem.id) {
                            otherPalletsTotal += candidate.quantity;
                        }
                    });
                    this.adjustmentAmount = totalCounted - otherPalletsTotal;
                },
                resetModal() {
                    this.isModalOpen = false; this.isLoading = false; this.candidateLpns = [];
                    this.selectedLpnItem = null; this.taskToAdjust = null; this.adjustmentAmount = 0;
                }
            }
        }
    </script>
</x-app-layout>