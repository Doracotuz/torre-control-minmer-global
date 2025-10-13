<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Dashboard de Conteo: {{ $session->name }}</h2></x-slot>
    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Aquí irán los KPIs del conteo --}}
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h3 class="font-bold text-lg">Tareas de Conteo</h3>
            <table class="min-w-full divide-y divide-gray-200 mt-4">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ubicación</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                        <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cant. Sistema</th>
                        <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">1er Conteo</th>
                        <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">2do Conteo</th>
                        <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">3er Conteo</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estatus</th>
                        <th class="px-2 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($session->tasks as $task)
                        @php
                            $count1 = $task->records->where('count_number', 1)->first();
                            $count2 = $task->records->where('count_number', 2)->first();
                            $count3 = $task->records->where('count_number', 3)->first();
                        @endphp
                        <tr class="@if($task->status == 'discrepancy') bg-yellow-50 @elseif($task->status == 'resolved') bg-green-50 @endif">
                            <td class="px-2 py-4 font-mono">{{ $task->location->code }}</td>
                            <td class="px-2 py-4 font-mono">{{ $task->product->sku }}</td>
                            <td class="px-2 py-4 text-center font-bold text-lg">{{ $task->expected_quantity }}</td>
                            <td class="px-2 py-4 text-center font-bold text-lg {{ $count1 && $count1->counted_quantity != $task->expected_quantity ? 'text-red-600' : '' }}">{{ $count1->counted_quantity ?? '-' }}</td>
                            <td class="px-2 py-4 text-center font-bold text-lg {{ $count2 && $count2->counted_quantity != $task->expected_quantity ? 'text-red-600' : '' }}">{{ $count2->counted_quantity ?? '-' }}</td>
                            <td class="px-2 py-4 text-center font-bold text-lg {{ $count3 && $count3->counted_quantity != $task->expected_quantity ? 'text-red-600' : '' }}">{{ $count3->counted_quantity ?? '-' }}</td>
                            <td class="px-2 py-4"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full @if($task->status == 'discrepancy') bg-yellow-100 text-yellow-800 @elseif($task->status == 'resolved') bg-green-100 text-green-800 @else bg-gray-100 text-gray-800 @endif">{{ $task->status }}</span></td>
                            <td class="px-2 py-4 text-right">
                                @if ($task->status == 'pending' || ($task->status == 'discrepancy' && $task->records->count() < 3))
                                    <a href="{{ route('wms.physical-counts.tasks.perform', $task) }}" class="text-indigo-600 font-semibold">
                                        {{ $task->records->count() > 0 ? 'Re-contar' : 'Contar' }}
                                    </a>
                                @elseif ($task->status == 'discrepancy' && $task->records->count() >= 3)
                                    <form action="{{ route('wms.physical-counts.tasks.adjust', $task) }}" method="POST" onsubmit="return confirm('¿Confirmas el ajuste de inventario basado en el último conteo? Esta acción es irreversible.');">
                                        @csrf
                                        <button type="submit" class="font-semibold text-red-600">Ajustar Inventario</button>
                                    </form>
                                @elseif ($task->status == 'resolved')
                                    <span class="text-green-600 font-semibold"><i class="fas fa-check-circle"></i> Resuelto</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div></div>
</x-app-layout>