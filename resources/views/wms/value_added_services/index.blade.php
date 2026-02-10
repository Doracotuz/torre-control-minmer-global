<x-app-layout>
    <x-slot name="header"></x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@700;800;900&display=swap');
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        .shadow-soft { box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.1); }
        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen text-[#2b2b2b] font-montserrat pb-20 relative" 
         x-data="{ 
            showCreateModal: false, 
            showEditModal: false, 
            editingService: null,
            openEditModal(service) {
                this.editingService = service;
                this.showEditModal = true;
            }
         }">
        
        <div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden">
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#2c3856] rounded-full blur-[150px] opacity-5"></div>
            <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-[#ff9c00] rounded-full blur-[150px] opacity-5"></div>
        </div>

        <div class="max-w-[1800px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 border-b border-gray-200 pb-6">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <div class="h-1 w-8 bg-[#ff9c00]"></div>
                        <p class="text-xs font-bold text-[#666666] uppercase tracking-[0.2em]">Configuración</p>
                    </div>
                    <h1 class="text-5xl font-raleway font-black text-[#2c3856] mb-1 leading-none">
                        Valor <span class="text-[#ff9c00]">Agregado</span>
                    </h1>
                </div>

                <div class="flex flex-wrap gap-3 mt-6 xl:mt-0">
                    <a href="{{ route('wms.dashboard') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-[#666666] font-bold rounded-full shadow-sm hover:shadow-md hover:border-[#2c3856] hover:text-[#2c3856] transition-all">
                        <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
                    </a>
                    <button @click="showCreateModal = true" class="flex items-center gap-2 px-6 py-2.5 bg-[#2c3856] text-white font-bold rounded-full shadow-lg shadow-[#2c3856]/20 hover:bg-[#1a253a] hover:-translate-y-0.5 transition-all">
                        <i class="fas fa-plus"></i> <span>Nuevo Servicio</span>
                    </button>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-xl shadow-sm font-medium flex items-center gap-3">
                    <i class="fas fa-check-circle text-xl"></i>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if ($errors->any())
                 <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl shadow-sm font-medium">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                 </div>
            @endif

            <div class="bg-white rounded-[2rem] shadow-soft border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Código</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Costo UNITARIO</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-[#666666] uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($services as $service)
                                <tr class="hover:bg-blue-50/30 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-bold text-[#2c3856]">{{ $service->code }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-700">{{ $service->description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $service->type === 'service' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $service->type === 'service' ? 'Servicio' : 'Consumible' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700">
                                        ${{ number_format($service->cost, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openEditModal({{ $service }})" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-[#ff9c00] hover:border-[#ff9c00] transition-all shadow-sm" title="Editar">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                            <form action="{{ route('wms.value-added-services.destroy', $service) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este servicio?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-red-600 hover:border-red-600 transition-all shadow-sm" title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-box-open text-4xl mb-3 opacity-50"></i>
                                            <p class="text-sm font-medium">No se encontraron servicios registrados.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div x-show="showCreateModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-[#2c3856] bg-opacity-75 transition-opacity" aria-hidden="true" @click="showCreateModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 relative z-50">
                    <form action="{{ route('wms.value-added-services.store') }}" method="POST">
                        @csrf
                        <div class="bg-white px-8 pt-8 pb-6">
                            <h3 class="text-2xl leading-6 font-raleway font-black text-[#2c3856] mb-6">Nuevo Servicio</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-bold text-[#666666] mb-2 uppercase tracking-wide">Código</label>
                                    <input type="text" name="code" required class="w-full pl-4 pr-4 py-3 rounded-xl border-gray-200 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-[#666666] mb-2 uppercase tracking-wide">Descripción</label>
                                    <input type="text" name="description" required class="w-full pl-4 pr-4 py-3 rounded-xl border-gray-200 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-[#666666] mb-2 uppercase tracking-wide">Tipo</label>
                                    <select name="type" required class="w-full pl-4 pr-10 py-3 rounded-xl border-gray-200 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all appearance-none">
                                        <option value="service">Servicio</option>
                                        <option value="consumable">Consumible</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-[#666666] mb-2 uppercase tracking-wide">Costo</label>
                                    <input type="number" step="0.01" name="cost" required class="w-full pl-4 pr-4 py-3 rounded-xl border-gray-200 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-8 py-4 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-lg shadow-[#2c3856]/20 px-6 py-3 bg-[#2c3856] text-base font-bold text-white hover:bg-[#1a253a] focus:outline-none sm:w-auto sm:text-sm transition-all">
                                Guardar
                            </button>
                            <button type="button" @click="showCreateModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-6 py-3 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-all">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div x-show="showEditModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="edit-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-[#2c3856] bg-opacity-75 transition-opacity" aria-hidden="true" @click="showEditModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 relative z-50">
                    <form x-bind:action="'/wms/value-added-services/' + editingService?.id" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-white px-8 pt-8 pb-6">
                            <h3 class="text-2xl leading-6 font-raleway font-black text-[#2c3856] mb-6">Editar Servicio</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-bold text-[#666666] mb-2 uppercase tracking-wide">Código</label>
                                    <input type="text" name="code" x-model="editingService?.code" required class="w-full pl-4 pr-4 py-3 rounded-xl border-gray-200 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-[#666666] mb-2 uppercase tracking-wide">Descripción</label>
                                    <input type="text" name="description" x-model="editingService?.description" required class="w-full pl-4 pr-4 py-3 rounded-xl border-gray-200 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-[#666666] mb-2 uppercase tracking-wide">Tipo</label>
                                    <select name="type" x-model="editingService?.type" required class="w-full pl-4 pr-10 py-3 rounded-xl border-gray-200 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all appearance-none">
                                        <option value="service">Servicio</option>
                                        <option value="consumable">Consumible</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-[#666666] mb-2 uppercase tracking-wide">Costo</label>
                                    <input type="number" step="0.01" name="cost" x-model="editingService?.cost" required class="w-full pl-4 pr-4 py-3 rounded-xl border-gray-200 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-8 py-4 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-lg shadow-[#2c3856]/20 px-6 py-3 bg-[#2c3856] text-base font-bold text-white hover:bg-[#1a253a] focus:outline-none sm:w-auto sm:text-sm transition-all">
                                Actualizar
                            </button>
                            <button type="button" @click="showEditModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-6 py-3 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-all">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
