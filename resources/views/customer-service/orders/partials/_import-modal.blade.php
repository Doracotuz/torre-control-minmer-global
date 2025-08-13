<div x-show="isImportModalOpen" @keydown.escape.window="isImportModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" style="display: none;">
    <div @click.outside="isImportModalOpen = false" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
        <h3 class="text-xl font-bold text-[#2c3856] mb-4">Importar Órdenes de Venta (SO)</h3>
        <form action="{{ route('customer-service.orders.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div>
                <label for="csv_file" class="block text-sm font-medium text-gray-700">Archivo CSV</label>
                <input type="file" name="csv_file" id="csv_file" required accept=".csv,.txt" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <a href="{{ route('customer-service.orders.template') }}" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Descargar plantilla</a>
            </div>
            <div class="mt-6 flex justify-end gap-4">
                <button type="button" @click="isImportModalOpen = false" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Procesar Archivo</button>
            </div>
        </form>
    </div>
</div>