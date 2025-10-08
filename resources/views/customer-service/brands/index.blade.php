<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Marcas') }}
            </h2>
            <a href="{{ route('customer-service.products.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                &larr; Volver a Productos
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="modalManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4">
                <button 
                    @click="openModal()" 
                    class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-green-700"
                >
                    Añadir Nueva Marca
                </button>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre de la Marca</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($brands as $brand)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $brand->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="{{ route('customer-service.brands.destroy', $brand) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro? Solo se puede eliminar si no tiene productos asociados.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center p-4">No hay marcas creadas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $brands->links() }}</div>
        </div>

        <div 
            x-show="isModalOpen" 
            x-cloak
            x-transition
            @keydown.escape.window="closeModal()"
            x-ref="modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-title"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
            style="display: none;"
        >
            <div 
                class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md"
                x-ref="modalContent"
            >
                <h3 id="modal-title" class="text-xl font-bold text-[#2c3856] mb-4">Crear Nueva Marca</h3>
                
                <form action="{{ route('customer-service.brands.store') }}" method="POST" @submit="closeModal()">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            required 
                            x-ref="firstInput"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 border"
                            placeholder="Ingrese el nombre de la marca"
                        >
                    </div>
                    
                    <div class="mt-6 flex justify-end gap-4">
                        <button 
                            type="button" 
                            @click="closeModal()"
                            x-ref="cancelButton"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            x-ref="submitButton"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                        >
                            Guardar Marca
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        function modalManager() {
            return {
                isModalOpen: false,
                firstFocusableElement: null,
                lastFocusableElement: null,
                
                openModal() {
                    this.isModalOpen = true;
                    this.$nextTick(() => {
                        this.setupFocusTrap();
                    });
                },
                
                closeModal() {
                    this.isModalOpen = false;
                    if (this.$refs.modal) {
                        this.$refs.modal.removeEventListener('keydown', this.handleKeydown.bind(this));
                    }
                    document.body.style.overflow = 'auto';
                },
                
                setupFocusTrap() {
                    const focusableElements = this.$refs.modal.querySelectorAll(
                        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                    );
                    
                    if (focusableElements.length > 0) {
                        this.firstFocusableElement = focusableElements[0];
                        this.lastFocusableElement = focusableElements[focusableElements.length - 1];
                        this.$refs.firstInput.focus();
                        document.body.style.overflow = 'hidden';
                        this.$refs.modal.addEventListener('keydown', this.handleKeydown.bind(this));
                    }
                },
                
                handleKeydown(e) {
                    if (!this.isModalOpen) return;
                    
                    if (e.key === 'Tab') {
                        if (this.firstFocusableElement === this.lastFocusableElement) {
                            e.preventDefault();
                            return;
                        }
                        if (e.shiftKey) {
                            if (document.activeElement === this.firstFocusableElement) {
                                e.preventDefault();
                                this.lastFocusableElement.focus();
                            }
                        } 
                        else {
                            if (document.activeElement === this.lastFocusableElement) {
                                e.preventDefault();
                                this.firstFocusableElement.focus();
                            }
                        }
                    }
                }
            }
        }
    </script>
</x-app-layout>