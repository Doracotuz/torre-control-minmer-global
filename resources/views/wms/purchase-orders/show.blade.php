<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl text-gray-800 leading-tight tracking-tight">
                    Detalles del arribo: <span class="font-bold text-4xl text-blue-800 leading-tight tracking-tight">{{ $purchaseOrder->po_number }}</span>
                </h2>
            </div>
            <a href="{{ route('wms.purchase-orders.index') }}" class="mt-4 md:mt-0 px-5 py-2 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Volver al Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8 space-y-8">
            {{-- Bloque de Mensajes y Alertas --}}
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-md" role="alert">
                    <div class="flex"><div class="py-1"><i class="fas fa-check-circle mr-3"></i></div><div><p class="font-bold">{{ session('success') }}</p></div></div>
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-md">
                    <p class="font-bold">Error de validación:</p>
                    <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                <div class="lg:col-span-2 bg-white p-4 sm:p-8 rounded-2xl shadow-xl border border-gray-200 space-y-10">
                    
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center mb-4">
                            <div class="flex items-center flex-grow">
                                <div class="bg-gray-100 p-3 rounded-full mr-4"><i class="fas fa-file-invoice-dollar text-gray-600 fa-lg"></i></div>
                                <h3 class="font-bold text-xl text-gray-800">Información General</h3>
                            </div>
                            <div class="mt-4 sm:mt-0 self-start sm:self-center flex items-center space-x-2">
                                {{-- El botón de EDITAR solo aparece si la orden NO está completada --}}
                                @if ($purchaseOrder->status != 'Completed')
                                    <a href="{{ route('wms.purchase-orders.edit', $purchaseOrder) }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg shadow-sm hover:bg-gray-50">
                                        <i class="fas fa-pencil-alt mr-2"></i> Editar Orden
                                    </a>
                                @endif

                                {{-- El botón de PDF solo aparece si la orden SÍ está completada --}}
                                @if ($purchaseOrder->status == 'Completed')
                                    <a href="{{ route('wms.purchase-orders.arrival-report-pdf', $purchaseOrder) }}" target="_blank" class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-red-700">
                                        <i class="fas fa-file-pdf mr-2"></i> Generar Reporte
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm pl-16">
                            <div><p class="text-gray-500">Nº de PO</p><p class="font-mono font-semibold">{{ $purchaseOrder->po_number }}</p></div>
                            <div><p class="text-gray-500">Fecha Esperada</p><p class="font-semibold">{{ Carbon\Carbon::parse($purchaseOrder->expected_date)->format('d M, Y') }}</p></div>
                            <div><p class="text-gray-500">Estado</p><span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $purchaseOrder->status == 'Pending' ? 'bg-yellow-100 text-yellow-800' : ($purchaseOrder->status == 'Receiving' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800') }}">{{ $purchaseOrder->status_in_spanish }}</span></div>
                            <div><p class="text-gray-500">Contenedor</p><p class="font-mono font-semibold">{{ $purchaseOrder->container_number ?? 'N/A' }}</p></div>
                            <div><p class="text-gray-500">Factura</p><p class="font-mono font-semibold">{{ $purchaseOrder->document_invoice ?? 'N/A' }}</p></div>
                            <div><p class="text-gray-500">Creado por</p><p class="font-semibold">{{ $purchaseOrder->user->name }}</p></div>
                            <div><p class="text-gray-500">Pedimento A4</p><p class="font-semibold">{{ $purchaseOrder->pedimento_a4 ?? 'N/A' }}</p></div>
                        </div>                        
                    </div>

                    <div class="border-t pt-8">
                        <div class="flex items-center mb-4">
                            <div class="bg-gray-100 p-3 rounded-full mr-4"><i class="fas fa-boxes text-gray-600 fa-lg"></i></div>
                            <h3 class="font-bold text-xl text-gray-800">Resumen de Recepción</h3>
                        </div>
                        @php $summary = $purchaseOrder->getReceiptSummary(); @endphp
                        
                        <div class="hidden lg:block overflow-x-auto pl-0 lg:pl-16">
                            <table class="min-w-full">
                                <thead class="border-b-2 border-gray-200">
                                    <tr>
                                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">SKU / Producto</th>
                                        <th class="px-2 py-2 text-right text-xs font-bold text-gray-500 uppercase">Ordenado</th>
                                        <th class="px-2 py-2 text-right text-xs font-bold text-gray-500 uppercase">Recibido</th>
                                        <th class="px-2 py-2 text-right text-xs font-bold text-gray-500 uppercase">Cajas</th>
                                        <th class="px-2 py-2 text-right text-xs font-bold text-gray-500 uppercase">Pallets</th>
                                        <th class="px-2 py-2 text-right text-xs font-bold text-gray-500 uppercase">Diferencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($summary as $line)
                                        @php $diff = $line->quantity_received - $line->quantity_ordered; @endphp
                                        <tr class="border-b border-gray-100"><td class="px-2 py-4"><p class="font-mono text-indigo-600 font-semibold">{{ $line->sku }}</p><p class="text-sm font-medium text-gray-800">{{ $line->product_name }}</p></td><td class="px-2 py-4 text-right font-medium text-gray-600">{{ number_format($line->quantity_ordered) }}</td><td class="px-2 py-4 text-right font-bold text-lg text-gray-900">{{ number_format($line->quantity_received) }}</td><td class="px-2 py-4 text-right font-bold text-blue-600">{{ $line->cases_received }}</td><td class="px-2 py-4 text-right font-medium text-gray-600">{{ $line->pallet_count }}</td><td class="px-2 py-4 text-right font-bold text-lg {{ $diff == 0 && $line->quantity_received > 0 ? 'text-green-600' : ($diff != 0 ? 'text-red-600' : 'text-gray-500') }}">{{ $diff > 0 ? '+' : '' }}{{ number_format($diff) }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="block lg:hidden space-y-4 pl-0 lg:pl-16">
                            @foreach($summary as $line)
                                @php $diff = $line->quantity_received - $line->quantity_ordered; @endphp
                                <div class="p-4 rounded-lg border bg-gray-50">
                                    <p class="font-mono text-indigo-600 font-semibold">{{ $line->sku }}</p>
                                    <p class="text-sm font-medium text-gray-800">{{ $line->product_name }}</p>
                                    <div class="grid grid-cols-3 gap-4 text-center mt-3 pt-3 border-t">
                                        <div><p class="text-xs text-gray-500">Ordenado</p><p class="font-medium">{{ number_format($line->quantity_ordered) }}</p></div>
                                        <div><p class="text-xs text-gray-500">Recibido</p><p class="font-bold text-lg">{{ number_format($line->quantity_received) }}</p></div>
                                        <div><p class="text-xs text-gray-500">Diferencia</p><p class="font-bold text-lg {{ $diff == 0 && $line->quantity_received > 0 ? 'text-green-600' : ($diff != 0 ? 'text-red-600' : 'text-gray-500') }}">{{ $diff > 0 ? '+' : '' }}{{ number_format($diff) }}</p></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="border-t pt-8">
                        <div class="flex items-center mb-4"><div class="bg-gray-100 p-3 rounded-full mr-4"><i class="fas fa-history text-gray-600 fa-lg"></i></div><h3 class="font-bold text-xl text-gray-800">Historial de Tarimas Recibidas</h3></div>
                        <div class="space-y-3 pl-0 lg:pl-16">@forelse($purchaseOrder->pallets as $pallet)<div class="bg-gray-50 p-3 rounded-lg border border-gray-200"><div class="flex justify-between items-center"><p class="font-mono font-bold text-indigo-800">{{ $pallet->lpn }}</p><div class="text-xs font-semibold text-gray-500 text-right"><p>Recibido por: {{ $pallet->user->name ?? 'N/A' }}</p><p>{{ $pallet->updated_at->format('d/M/y h:i A') }}</p></div></div><ul class="text-xs mt-2 space-y-1 border-t pt-2">@foreach($pallet->items as $item)<li class="flex justify-between"><span><strong class="text-indigo-700">[{{ $item->quality->name ?? 'N/A' }}]</strong> {{ $item->product->name ?? 'Producto no encontrado' }}</span><span class="font-semibold">x {{ $item->quantity }}</span></li>@endforeach</ul></div>@empty<div class="text-center text-gray-500 py-6"><i class="fas fa-pallet fa-2x mb-2"></i><p>No se han registrado tarimas para esta orden.</p></div>@endforelse</div>
                    </div>
                    <div class="border-t pt-8" x-data="evidenceHandler()">
                        <div class="flex items-center mb-6">
                            <div class="bg-gray-100 p-3 rounded-full mr-4"><i class="fas fa-camera text-gray-600 fa-lg"></i></div>
                            <h3 class="font-bold text-xl text-gray-800">Evidencias Fotográficas</h3>
                        </div>
                        
                        <form action="{{ route('wms.purchase-orders.upload-evidence', $purchaseOrder) }}" method="POST" enctype="multipart/form-data" class="pl-0 lg:pl-16">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                
                                <div x-data="fileInput('marchamo')" class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Marchamo</label>
                                    <label :class="preview ? 'border-indigo-500' : 'border-gray-300'" class="relative flex justify-center w-full h-32 px-4 transition bg-white border-2 border-dashed rounded-md appearance-none cursor-pointer hover:border-indigo-400 focus:outline-none"><img x-show="preview" :src="preview" class="absolute inset-0 w-full h-full object-cover rounded-md"><span x-show="!preview" class="flex items-center space-x-2"><i class="fas fa-cloud-upload-alt text-gray-400 text-2xl"></i><span class="font-medium text-gray-600">Suelte o haga clic</span></span><input type="file" name="marchamo" @change="updatePreview" class="absolute inset-0 z-50 w-full h-full opacity-0"></label>
                                </div>

                                <div x-data="fileInput('puerta_cerrada')" class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Puerta Cerrada</label>
                                    <label :class="preview ? 'border-indigo-500' : 'border-gray-300'" class="relative flex justify-center w-full h-32 px-4 transition bg-white border-2 border-dashed rounded-md appearance-none cursor-pointer hover:border-indigo-400 focus:outline-none"><img x-show="preview" :src="preview" class="absolute inset-0 w-full h-full object-cover rounded-md"><span x-show="!preview" class="flex items-center space-x-2"><i class="fas fa-cloud-upload-alt text-gray-400 text-2xl"></i><span class="font-medium text-gray-600">Suelte o haga clic</span></span><input type="file" name="puerta_cerrada" @change="updatePreview" class="absolute inset-0 z-50 w-full h-full opacity-0"></label>
                                </div>

                                <div x-data="fileInput('apertura_puertas')" class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Apertura de Puertas</label>
                                    <label :class="preview ? 'border-indigo-500' : 'border-gray-300'" class="relative flex justify-center w-full h-32 px-4 transition bg-white border-2 border-dashed rounded-md appearance-none cursor-pointer hover:border-indigo-400 focus:outline-none"><img x-show="preview" :src="preview" class="absolute inset-0 w-full h-full object-cover rounded-md"><span x-show="!preview" class="flex items-center space-x-2"><i class="fas fa-cloud-upload-alt text-gray-400 text-2xl"></i><span class="font-medium text-gray-600">Suelte o haga clic</span></span><input type="file" name="apertura_puertas" @change="updatePreview" class="absolute inset-0 z-50 w-full h-full opacity-0"></label>
                                </div>
                                
                                <div x-data="fileInput('caja_vacia')" class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Caja Vacía</label>
                                    <label :class="preview ? 'border-indigo-500' : 'border-gray-300'" class="relative flex justify-center w-full h-32 px-4 transition bg-white border-2 border-dashed rounded-md appearance-none cursor-pointer hover:border-indigo-400 focus:outline-none"><img x-show="preview" :src="preview" class="absolute inset-0 w-full h-full object-cover rounded-md"><span x-show="!preview" class="flex items-center space-x-2"><i class="fas fa-cloud-upload-alt text-gray-400 text-2xl"></i><span class="font-medium text-gray-600">Suelte o haga clic</span></span><input type="file" name="caja_vacia" @change="updatePreview" class="absolute inset-0 z-50 w-full h-full opacity-0"></label>
                                </div>

                                <div x-data="multiFileInput('proceso_descarga')" class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Proceso de Descarga</label>
                                    <label :class="fileCount > 0 ? 'border-indigo-500' : 'border-gray-300'" class="relative flex justify-center w-full h-32 px-4 transition bg-white border-2 border-dashed rounded-md appearance-none cursor-pointer hover:border-indigo-400 focus:outline-none">
                                        <span class="flex items-center space-x-2">
                                            <i class="fas fa-copy text-gray-400 text-2xl"></i>
                                            <span class="font-medium text-gray-600" x-text="fileText"></span>
                                        </span>
                                        <input type="file" name="proceso_descarga[]" multiple @change="updateText" class="absolute inset-0 z-50 w-full h-full opacity-0">
                                    </label>
                                </div>
                                
                                <div x-data="multiFileInput('producto_danado')" class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Producto Dañado</label>
                                    <label :class="fileCount > 0 ? 'border-indigo-500' : 'border-gray-300'" class="relative flex justify-center w-full h-32 px-4 transition bg-white border-2 border-dashed rounded-md appearance-none cursor-pointer hover:border-indigo-400 focus:outline-none">
                                        <span class="flex items-center space-x-2">
                                            <i class="fas fa-copy text-gray-400 text-2xl"></i>
                                            <span class="font-medium text-gray-600" x-text="fileText"></span>
                                        </span>
                                        <input type="file" name="producto_danado[]" multiple @change="updateText" class="absolute inset-0 z-50 w-full h-full opacity-0">
                                    </label>
                                </div>
                            </div>
                            <div class="text-right border-t pt-6 mt-6"><button type="submit" class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700"><i class="fas fa-upload mr-2"></i>Subir Evidencias</button></div>
                        </form>

                        @if($purchaseOrder->evidences->isNotEmpty())
                        <div class="mt-10 border-t pt-8 pl-0 lg:pl-16">
                            <h4 class="font-bold text-lg text-gray-700 mb-6">Galería de Evidencias</h4>
                            @foreach($purchaseOrder->evidences->groupBy('type') as $type => $evidences)
                                <div class="mb-6">
                                    <h5 class="font-semibold text-gray-600 capitalize mb-2">{{ str_replace('_', ' ', $type) }}</h5>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                        @foreach($evidences as $evidence)
                                            <div class="relative group border rounded-lg overflow-hidden shadow-sm">
                                                <button type="button" @click="openModal('{{ Storage::url($evidence->file_path) }}')" class="w-full">
                                                    <img src="{{ Storage::url($evidence->file_path) }}" alt="{{ $evidence->original_name }}" class="w-full h-32 object-cover transition-transform group-hover:scale-105">
                                                </button>
                                                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 truncate" title="{{ $evidence->original_name }}">{{ $evidence->original_name }}</div>
                                                <form action="{{ route('wms.purchase-orders.destroy-evidence', $evidence) }}" method="POST" onsubmit="return confirm('¿Eliminar esta foto?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" title="Eliminar"><i class="fas fa-times text-sm"></i></button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>                  
                </div>

                <div class="space-y-8">
                    <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200">
                        <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center"><i class="fas fa-truck text-gray-400 mr-3"></i>Gestión de Patio</h3>
                        
                        @if(!$purchaseOrder->download_start_time)
                            <form action="{{ route('wms.purchase-orders.register-arrival', $purchaseOrder) }}" method="POST" class="space-y-3">
                                @csrf
                                <input type="text" name="truck_plate" placeholder="Placas del Vehículo" required class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <input type="text" name="driver_name" placeholder="Nombre del Operador" required class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <button type="submit" class="w-full mt-2 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition-all"><i class="fas fa-sign-in-alt mr-2"></i>Registrar Llegada</button>
                            </form>
                        @else
                            <div class="text-sm space-y-3 bg-gray-50 p-4 rounded-lg">
                                <div class="flex justify-between"><span>Operador:</span> <strong class="text-gray-900">{{ $purchaseOrder->operator_name ?? 'N/A' }}</strong></div>
                                <div class="flex justify-between"><span>Llegada:</span> <strong class="text-gray-900">{{ Carbon\Carbon::parse($purchaseOrder->download_start_time)->format('d/M/y h:i A') }}</strong></div>
                                <div class="flex justify-between"><span>Salida:</span> <strong class="text-gray-900">{{ $purchaseOrder->download_end_time ? Carbon\Carbon::parse($purchaseOrder->download_end_time)->format('d/M/y h:i A') : '---' }}</strong></div>
                            </div>
                            @if(!$purchaseOrder->download_end_time)
                                <form action="{{ route('wms.purchase-orders.register-departure', $purchaseOrder) }}" method="POST" class="mt-4">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white font-semibold rounded-lg shadow-md hover:bg-red-700 transition-all"><i class="fas fa-sign-out-alt mr-2"></i>Registrar Salida</button>
                                </form>
                            @endif
                        @endif
                    </div>
                    
                    @if (($purchaseOrder->status == 'Receiving' || $purchaseOrder->status == 'Pending') && $purchaseOrder->received_bottles < $purchaseOrder->expected_bottles)
                    <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-2xl shadow-xl text-center">
                        <h3 class="text-white font-bold text-lg mb-2">Siguiente Paso</h3>
                        <p class="text-green-100 text-sm mb-4">Continúa con el registro de productos en la interfaz de recepción física.</p>
                        <a href="{{ route('wms.receiving.show', $purchaseOrder) }}" class="inline-block px-8 py-3 bg-white text-green-600 font-bold rounded-lg shadow-md hover:bg-green-50 transition-transform hover:scale-105">
                            <i class="fas fa-pallet mr-2"></i> Ir a Recepción Física
                        </a>
                    </div>
                    @endif
                    @if ($purchaseOrder->status == 'Receiving')
                    <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200">
                        <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-tasks text-gray-400 mr-3"></i>Acciones Finales
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Una vez que toda la mercancía física haya sido registrada, cierra la orden para marcar el proceso de recepción como finalizado.
                        </p>
                        
                        <form action="{{ route('wms.purchase-orders.complete', $purchaseOrder) }}" method="POST"
                            onsubmit="return confirm('¿Estás seguro de que deseas cerrar y completar esta orden de compra? Esta acción no se puede deshacer.');">
                            @csrf
                            
                            {{-- Condición para mostrar advertencia si está incompleta --}}
                            @if ($purchaseOrder->received_bottles < $purchaseOrder->expected_bottles)
                                <div class="my-3 p-3 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 text-xs rounded-md">
                                    <strong>Atención:</strong> La recepción está incompleta. Al cerrar, se aceptará la diferencia.
                                </div>
                            @endif

                            <button type="submit" class="w-full mt-2 px-4 py-3 bg-gray-800 text-white font-bold rounded-lg shadow-md hover:bg-gray-900 transition-all">
                                <i class="fas fa-lock mr-2"></i> Cerrar y Completar Orden
                            </button>
                        </form>
                    </div>
                    @endif                    
                </div>
            </div>
        </div>
    </div>

<div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
    <div @click="closeModal()" class="absolute inset-0 bg-black bg-opacity-75"></div>
    <div @click.away="closeModal()" class="relative bg-white p-2 rounded-lg shadow-xl max-w-4xl max-h-full">
        <img :src="modalImage" class="max-w-full max-h-[85vh] object-contain">
        <button @click="closeModal()" class="absolute -top-3 -right-3 bg-white text-gray-700 rounded-full w-8 h-8 flex items-center justify-center shadow-lg" title="Cerrar">&times;</button>
    </div>
</div>

{{-- Script para manejar la lógica de la vista --}}
<script>
    function evidenceHandler() {
        return {
            modalOpen: false, modalImage: '',
            openModal(imageUrl) { this.modalImage = imageUrl; this.modalOpen = true; },
            closeModal() { this.modalOpen = false; },
            fileInput(name) {
                return {
                    name: name,
                    preview: '',
                    updatePreview(event) {
                        const file = event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = (e) => this.preview = e.target.result;
                            reader.readAsDataURL(file);
                        }
                    }
                }
            },
            multiFileInput(name) {
                return {
                    name: name,
                    fileCount: 0,
                    fileText: 'Seleccionar Archivos',
                    updateText(event) {
                        this.fileCount = event.target.files.length;
                        if (this.fileCount === 0) {
                            this.fileText = 'Seleccionar Archivos';
                        } else if (this.fileCount === 1) {
                            this.fileText = '1 archivo seleccionado';
                        } else {
                            this.fileText = `${this.fileCount} archivos seleccionados`;
                        }
                    }
                }
            }
        }
    }
</script>

</x-app-layout>