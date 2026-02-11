<x-app-layout>
    <x-slot name="header"></x-slot>

    <style>
        .loader-spinner { border: 4px solid #f3f3f3; border-top: 4px solid #ff9c00; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .excel-viewer table { width: 100%; border-collapse: collapse; font-size: 12px; background: white; }
        .excel-viewer th, .excel-viewer td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; color: #2c3856; white-space: nowrap; }
        .excel-viewer th { background-color: #f8fafc; font-weight: bold; position: sticky; top: 0; z-index: 10; }
        .excel-viewer tr:nth-child(even) { background-color: #f8fafc; }
        
        #word-container { width: 100%; height: 100%; overflow-y: auto; background-color: #e5e7eb; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        #word-container .docx-wrapper { background: transparent !important; padding: 0 !important; width: 100%; display: flex; flex-direction: column; align-items: center; }
        #word-container section.docx { background: white !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important; margin-bottom: 20px !important; padding: 40px !important; width: 21cm !important; min-height: 29.7cm !important; color: black !important; }
        
        #fs-container:fullscreen { background: #e5e7eb; padding: 20px; display: flex; align-items: center; justify-content: center; overflow: auto; }
        #fs-container:fullscreen img, #fs-container:fullscreen iframe { height: 100%; width: 100%; }
        [x-cloak] { display: none !important; }
    </style>

    <div class="bg-[#F8FAFC] min-h-screen font-sans pb-12 relative rounded-3xl" 
         x-data="{ 
            showRejectModal: false,
            toastVisible: false,
            toastMessage: '',
            previewModalOpen: false,
            previewDownloadUrl: '',
            previewBlobUrl: '', 
            previewType: 'other', 
            previewName: '',
            previewLoading: false,
            isDragging: false,
            files: [],

            copyToClipboard(text) {
                navigator.clipboard.writeText(text);
                this.toastMessage = 'Folio copiado al portapapeles';
                this.toastVisible = true;
                setTimeout(() => this.toastVisible = false, 3000);
            },

            handleDrop(e) {
                this.isDragging = false;
                let droppedFiles = e.dataTransfer.files;
                if (droppedFiles.length > 0) {
                    this.files = droppedFiles;
                    $refs.evidenceInput.files = droppedFiles; 
                }
            },

            async openPreview(url, filename) {
                let ext = filename.split('.').pop().toLowerCase();
                
                this.previewDownloadUrl = url;
                this.previewName = filename;

                const images = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];
                const words = ['docx']; 
                const excels = ['xlsx', 'xls', 'csv'];
                const videos = ['mp4', 'webm', 'ogg', 'mov', 'avi'];

                if (images.includes(ext)) this.previewType = 'image';
                else if (ext === 'pdf') this.previewType = 'pdf';
                else if (words.includes(ext)) this.previewType = 'word';
                else if (excels.includes(ext)) this.previewType = 'excel';
                else if (videos.includes(ext)) this.previewType = 'video';
                else {
                    window.location.href = url;
                    return;
                }

                this.previewModalOpen = true;
                this.previewLoading = true;
                this.previewBlobUrl = ''; 

                try {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('Network error');
                    const blob = await response.blob();
                    
                    if (this.previewType === 'word') {
                        const container = document.getElementById('word-container');
                        container.innerHTML = '';
                        await docx.renderAsync(blob, container, null, { 
                            className: 'docx', inWrapper: true, ignoreWidth: false, breakPages: true 
                        });
                    } 
                    else if (this.previewType === 'excel') {
                        const arrayBuffer = await blob.arrayBuffer();
                        const workbook = XLSX.read(arrayBuffer);
                        const firstSheetName = workbook.SheetNames[0];
                        const worksheet = workbook.Sheets[firstSheetName];
                        const html = XLSX.utils.sheet_to_html(worksheet);
                        document.getElementById('excel-container').innerHTML = html;
                    } 
                    else {
                        const objectUrl = URL.createObjectURL(blob);
                        this.previewBlobUrl = objectUrl;
                    }

                } catch (error) {
                    console.error('Error fetching file blob:', error);
                    alert('No se pudo generar la vista previa. Descargando archivo...');
                    window.location.href = url;
                    this.previewModalOpen = false;
                } finally {
                    this.previewLoading = false;
                }
            },

            closePreview() {
                this.previewModalOpen = false;
                setTimeout(() => {
                    if (this.previewBlobUrl) { URL.revokeObjectURL(this.previewBlobUrl); this.previewBlobUrl = ''; }
                    this.previewType = 'other';
                    const wordContainer = document.getElementById('word-container');
                    const excelContainer = document.getElementById('excel-container');
                    if(wordContainer) wordContainer.innerHTML = '';
                    if(excelContainer) excelContainer.innerHTML = '';
                }, 300);
            },

            toggleFullscreen() {
                const container = document.getElementById('fs-container');
                if (!document.fullscreenElement) {
                    container.requestFullscreen().catch(err => { console.error(`Error: ${err.message}`); });
                } else { document.exitFullscreen(); }
            }
         }">

        @php    
            $isActive = !in_array($header->status, ['cancelled', 'rejected']);
            
            $hasBackorder = $isActive && $movements->contains(function ($value, $key) {
                return $value->is_backorder == true && $value->backorder_fulfilled == false;
            });

            $backorderCount = $movements->filter(function ($value, $key) {
                return $value->is_backorder == true && $value->backorder_fulfilled == false;
            })->sum(fn($m) => abs($m->quantity));
            $loanPendingCount = 0;
            if ($header->order_type === 'prestamo' && $isActive) {
                $loanPendingCount = $movements->filter(function ($m) {
                    return $m->quantity < 0;
                })->sum(function($m) {
                    return abs($m->quantity) - ($m->returned_quantity ?? 0);
                });
            }            
        @endphp

        <div x-show="previewModalOpen" 
             x-cloak 
             class="fixed inset-0 z-[100] flex items-center justify-center px-4 py-6"
             role="dialog" 
             aria-modal="true">
            
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" 
                 x-show="previewModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closePreview()"></div>

            <div class="relative w-full max-w-7xl h-full max-h-[95vh] bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col transform transition-all"
                 x-show="previewModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100 bg-gray-50/95 backdrop-blur-md z-20">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg shrink-0">
                            <template x-if="previewType === 'image'"><i class="fas fa-image"></i></template>
                            <template x-if="previewType === 'pdf'"><i class="fas fa-file-pdf"></i></template>
                            <template x-if="previewType === 'word'"><i class="fas fa-file-word"></i></template>
                            <template x-if="previewType === 'excel'"><i class="fas fa-file-excel"></i></template>
                            <template x-if="previewType === 'video'"><i class="fas fa-video"></i></template>
                        </div>
                        <h3 class="text-sm md:text-lg font-bold text-[#2c3856] truncate max-w-[200px] md:max-w-md" x-text="previewName">Vista Previa</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="toggleFullscreen()" class="p-2 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="Pantalla Completa">
                            <i class="fas fa-expand"></i>
                        </button>
                        <a :href="previewDownloadUrl" download class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Descargar">
                            <i class="fas fa-download"></i>
                        </a>
                        <button @click="closePreview()" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div id="fs-container" class="flex-1 bg-gray-100 overflow-hidden relative flex flex-col items-center justify-start w-full h-full">
                    
                    <div x-show="previewLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 z-50">
                        <div class="loader-spinner mb-3"></div>
                        <p class="text-sm font-bold text-gray-500">Renderizando documento...</p>
                    </div>

                    <template x-if="previewType === 'image' && previewBlobUrl">
                        <div class="flex items-center justify-center min-h-full p-4 w-full h-full overflow-auto">
                            <img :src="previewBlobUrl" class="max-w-full max-h-full object-contain rounded-lg shadow-lg" alt="Vista previa">
                        </div>
                    </template>
                    
                    <template x-if="previewType === 'pdf' && previewBlobUrl">
                        <iframe :src="previewBlobUrl" class="w-full h-full border-none bg-white" frameborder="0"></iframe>
                    </template>

                    <template x-if="previewType === 'video' && previewBlobUrl">
                        <div class="flex items-center justify-center min-h-full w-full h-full">
                            <video :src="previewBlobUrl" controls class="max-w-full max-h-full rounded-lg shadow-lg">
                                Tu navegador no soporta la reproducción de video.
                            </video>
                        </div>
                    </template>                    

                    <div x-show="previewType === 'word'" id="word-container"></div>

                    <div x-show="previewType === 'excel'" class="w-full h-full overflow-auto bg-white">
                        <div id="excel-container" class="excel-viewer w-full p-4"></div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="toastVisible" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-2 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="translate-y-2 opacity-0"
             class="fixed bottom-6 right-6 z-50 bg-[#2c3856] text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 border border-white/10 backdrop-blur-md">
            <i class="fas fa-check-circle text-[#ff9c00]"></i>
            <span class="text-sm font-medium" x-text="toastMessage"></span>
        </div>

        <div class="max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 pt-8">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <a href="{{ route('ff.orders.index') }}" class="w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-[#2c3856] hover:border-[#2c3856] flex items-center justify-center transition-all">
                            <i class="fas fa-arrow-left text-xs"></i>
                        </a>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Detalle de Operación</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-4xl font-black text-[#2c3856] tracking-tight">#{{ $header->folio }}</h1>
                        <button @click="copyToClipboard('{{ $header->folio }}')" class="text-slate-300 hover:text-[#ff9c00] transition-colors" title="Copiar">
                            <i class="fas fa-copy text-lg"></i>
                        </button>
                        
                        <span class="px-3 py-1 rounded-full text-xs font-bold border flex items-center gap-2
                            {{ match($header->status) {
                                'approved' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                'rejected' => 'bg-rose-50 text-rose-600 border-rose-100',
                                'cancelled' => 'bg-slate-100 text-slate-500 border-slate-200', default => 'bg-amber-50 text-amber-600 border-amber-100'
                            } }}">
                            
                            <span class="w-2 h-2 rounded-full 
                                {{ match($header->status) {
                                    'approved' => 'bg-emerald-500',
                                    'rejected' => 'bg-rose-500',
                                    'cancelled' => 'bg-slate-400',
                                    default => 'bg-amber-500 animate-pulse'
                                } }}">
                            </span>
                            
                            {{ match($header->status) { 
                                'pending' => 'Revisión', 
                                'approved' => 'Aprobado', 
                                'rejected' => 'Rechazado', 
                                'cancelled' => 'Cancelado', default => 'Desconocido' 
                            } }}
                        </span>

                        @if($hasBackorder)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-700 border border-purple-200 flex items-center gap-2 animate-pulse">
                                <i class="fas fa-history"></i> Backorder Activo
                            </span>
                        @endif
                        @if($loanPendingCount > 0)
                        <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-100 flex items-start gap-4 shadow-sm relative overflow-hidden mt-4">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-200 rounded-full blur-[50px] opacity-20 -mr-10 -mt-10"></div>
                            
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0 text-lg">
                                <i class="fas fa-hand-holding-box"></i>
                            </div>
                            
                            <div class="relative z-10">
                                <h3 class="text-indigo-900 font-bold text-sm uppercase tracking-wide mb-1">Devolución Pendiente</h3>
                                <p class="text-indigo-700 text-sm">
                                    Este préstamo aún tiene <strong class="text-indigo-900 text-base">{{ $loanPendingCount }} unidades</strong> en poder del cliente que no han sido devueltas.
                                </p>
                            </div>
                        </div>
                        @endif                        
                    </div>
                </div>

                @if(Auth::user()->is_area_admin && $header->status === 'pending')
                <div class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
                    <button @click="showRejectModal = true" class="px-5 py-2.5 rounded-xl text-rose-600 font-bold text-sm hover:bg-rose-50 transition-colors">
                        Rechazar
                    </button>
                    <form action="{{ route('ff.orders.approve', $header->folio) }}" method="POST" onsubmit="return confirm('¿Confirmar aprobación?');">
                        @csrf
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#2c3856] text-white font-bold text-sm shadow-lg shadow-blue-900/20 hover:bg-[#1e273d] hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                            Autorizar Pedido
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-white rounded-[2rem] p-8 shadow-[0_2px_20px_rgba(0,0,0,0.02)] border border-slate-100">
                        <div class="relative">
                            <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-50 -translate-y-1/2 rounded-full z-0"></div>
                            
                            <div class="absolute top-1/2 left-0 h-1 -translate-y-1/2 rounded-full z-0 transition-all duration-1000 ease-out 
                                {{ $header->status == 'pending' ? 'w-1/2 bg-amber-400' : ($header->status == 'approved' ? 'w-full bg-emerald-400' : ($header->status == 'rejected' ? 'w-full bg-rose-400' : 'w-0')) }}">
                            </div>

                            <div class="relative z-10 flex justify-between">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-[#2c3856] text-white flex items-center justify-center shadow-lg shadow-blue-900/20 ring-4 ring-white">
                                        <i class="fas fa-file-signature text-xs"></i>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs font-bold text-[#2c3856] uppercase tracking-wider">Solicitado</p>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $header->created_at->format('d/m H:i') }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center ring-4 ring-white transition-all
                                        {{ $header->status == 'pending' ? 'bg-amber-400 text-white shadow-lg shadow-amber-400/30 scale-110' : 'bg-[#2c3856] text-white' }}">
                                        <i class="fas {{ $header->status == 'pending' ? 'fa-hourglass-half fa-spin-pulse' : 'fa-check' }} text-xs"></i>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs font-bold uppercase tracking-wider {{ $header->status == 'pending' ? 'text-amber-500' : 'text-[#2c3856]' }}">
                                            Revisión
                                        </p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">Validación Admin</p>
                                    </div>
                                </div>

                                <div class="flex flex-col items-center gap-3">
                                    @if($header->status == 'approved')
                                        <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30 ring-4 ring-white scale-110">
                                            <i class="fas fa-check-double text-xs"></i>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Aprobado</p>
                                            <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $header->approved_at ? $header->approved_at->format('d/m H:i') : 'Hoy' }}</p>
                                        </div>
                                    @elseif($header->status == 'rejected')
                                        <div class="w-10 h-10 rounded-full bg-rose-500 text-white flex items-center justify-center shadow-lg shadow-rose-500/30 ring-4 ring-white scale-110">
                                            <i class="fas fa-times text-xs"></i>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-xs font-bold text-rose-600 uppercase tracking-wider">Rechazado</p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">Stock devuelto</p>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-300 flex items-center justify-center ring-4 ring-white">
                                            <i class="fas fa-flag-checkered text-xs"></i>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-xs font-bold text-slate-300 uppercase tracking-wider">Resultado</p>
                                            <p class="text-[10px] text-slate-300 mt-0.5">En espera</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>                    
                    
                    @if($hasBackorder)
                    <div class="bg-purple-50 rounded-2xl p-6 border border-purple-100 flex items-start gap-4 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-200 rounded-full blur-[50px] opacity-20 -mr-10 -mt-10"></div>
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0 text-lg">
                            <i class="fas fa-boxes-packing"></i>
                        </div>
                        <div class="relative z-10">
                            <h3 class="text-purple-900 font-bold text-sm uppercase tracking-wide mb-1">Entrega Pendiente (Backorder)</h3>
                            <p class="text-purple-700 text-sm">
                                Este pedido contiene <strong class="text-purple-900">{{ $backorderCount }} unidades</strong> sin stock físico al momento de la venta. 
                                El sistema notificará al vendedor cuando se realice la entrada de almacén correspondiente.
                            </p>
                        </div>
                    </div>
                    @endif

                    <div class="bg-white rounded-[2rem] p-8 shadow-[0_2px_20px_rgba(0,0,0,0.02)] border border-slate-100 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-slate-50 rounded-bl-[100px] -mr-10 -mt-10 transition-transform group-hover:scale-110"></div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10">
                            <div>
                                <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-2">Cliente Solicitante</p>
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#2c3856] flex items-center justify-center font-bold text-lg">
                                        {{ substr($header->client_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-[#2c3856] leading-tight">{{ $header->client_name }}</h3>
                                        <p class="text-sm text-slate-500">{{ $header->company_name }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex gap-8">
                                <div>
                                    <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-2">Fecha Entrega</p>
                                    <p class="text-xl font-bold text-[#2c3856] font-mono">
                                        {{ $header->delivery_date ? $header->delivery_date->format('d M, Y') : 'N/A' }}
                                    </p>
                                    <p class="text-xs font-bold text-[#ff9c00]">
                                        {{ $header->delivery_date ? $header->delivery_date->format('H:i') . ' hrs' : '' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-2">Tipo</p>
                                    <span class="inline-block px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 capitalize">
                                        {{ $header->order_type }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($header->observations)
                        <div class="mt-8 pt-6 border-t border-slate-50">
                            <p class="text-xs font-bold text-slate-400 uppercase mb-2">Notas Operativas</p>
                            <p class="text-sm text-slate-600 italic bg-slate-50 p-4 rounded-xl border border-slate-100">
                                "{{ $header->observations }}"
                            </p>
                        </div>
                        @endif

                        <div class="bg-white rounded-[2rem] p-8 shadow-[0_2px_20px_rgba(0,0,0,0.02)] border border-slate-100 mt-6">
                            <h3 class="font-bold text-[#2c3856] mb-4 flex items-center gap-2">
                                <i class="fas fa-warehouse text-[#ff9c00]"></i> Origen del Inventario
                            </h3>
                            <div class="pl-6 border-l-2 border-slate-100 ml-1.5">
                                @if($header->ff_warehouse_id)
                                    @php $wh = \App\Models\FfWarehouse::find($header->ff_warehouse_id); @endphp
                                    @if($wh)
                                        <p class="text-lg font-bold text-slate-700">{{ $wh->code }} - {{ $wh->description }}</p>
                                        <p class="text-xs text-slate-400 font-mono mt-1">{{ $wh->address }}</p>
                                    @else
                                        <p class="text-sm text-slate-500 italic">Almacén no encontrado (ID: {{ $header->ff_warehouse_id }})</p>
                                    @endif
                                @else
                                    <p class="text-sm text-slate-500 italic">Inventario General (Sin almacén específico)</p>
                                @endif
                            </div>
                        </div>                    
                    </div>

                    <div class="bg-white rounded-[2rem] shadow-[0_2px_20px_rgba(0,0,0,0.02)] border border-slate-100 overflow-hidden">
                        
                        <div class="px-6 md:px-8 py-6 border-b border-slate-50 flex justify-between items-center">
                            <h3 class="font-bold text-[#2c3856]">Items del Pedido</h3>
                            <span class="text-xs font-bold text-slate-400 bg-slate-50 px-3 py-1 rounded-full">{{ $totalItems }} unidades</span>
                        </div>

                        <div class="block md:hidden">
                            <div class="divide-y divide-slate-50">
                                @foreach($movements as $item)
                                    @php 
                                        $basePrice = $item->product->unit_price;
                                        $discountPercent = $item->discount_percentage ?? 0;
                                        $discountAmount = 0;
                                        $finalPrice = 0;
                                        $qty = abs($item->quantity);

                                        if ($item->order_type === 'normal') {
                                            $discountAmount = $basePrice * ($discountPercent / 100);
                                            $finalPrice = $basePrice - $discountAmount;
                                        }
                                    @endphp

                                    <div class="p-5 {{ $item->is_backorder ? 'bg-purple-50/30' : '' }}">
                                        <div class="flex gap-4">
                                            <div class="w-16 h-16 rounded-xl bg-white border border-slate-100 flex items-center justify-center p-1 shadow-sm relative overflow-hidden flex-shrink-0">
                                                @if($item->product->photo_url)
                                                    <img src="{{ $item->product->photo_url }}" class="w-full h-full object-contain mix-blend-multiply" alt="Producto">
                                                @else
                                                    <i class="fas fa-box text-slate-200 text-lg"></i>
                                                @endif
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <p class="font-bold text-slate-700 text-sm leading-tight mb-1">{{ $item->product->description }}</p>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-[10px] font-mono text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">{{ $item->product->sku }}</span>
                                                    @if($item->quality)
                                                        <span class="text-[9px] font-bold text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full border border-purple-200 flex items-center gap-1">
                                                            <i class="fas fa-medal text-[8px]"></i> {{ $item->quality->name }}
                                                        </span>
                                                    @endif                                                    
                                                    @if($item->is_backorder)
                                                        <span class="text-[9px] font-bold text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full border border-purple-200">
                                                            <i class="fas fa-history text-[8px]"></i> BACKORDER
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex items-center justify-between bg-slate-50 rounded-xl p-3 border border-slate-100">
                                            <div class="flex flex-col">
                                                <span class="text-[10px] text-slate-400 font-bold uppercase">Cant. & Precio</span>
                                                <div class="flex items-baseline gap-1">
                                                    <span class="font-bold text-slate-700">{{ $qty }}</span>
                                                    <span class="text-xs text-slate-400">x</span>
                                                    <span class="text-xs font-mono text-slate-600">${{ number_format($finalPrice, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-[10px] text-slate-400 font-bold uppercase">Total</span>
                                                <p class="font-bold text-[#2c3856] font-mono text-base">${{ number_format($finalPrice * $qty, 2) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="text-xs text-slate-400 font-bold bg-slate-50/50 uppercase tracking-wider text-left">
                                    <tr>
                                        <th class="px-8 py-4 rounded-tl-2xl">Producto</th>
                                        <th class="px-4 py-4 text-center">Cant.</th>
                                        <th class="px-4 py-4 text-right">P. Unit</th>
                                        <th class="px-4 py-4 text-center">Desc.</th>
                                        <th class="px-8 py-4 text-right rounded-tr-2xl">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($movements as $item)
                                        @php 
                                            $basePrice = $item->product->unit_price;
                                            $discountPercent = $item->discount_percentage ?? 0;
                                            $discountAmount = 0;
                                            $finalPrice = 0;
                                            $qty = abs($item->quantity);

                                            if ($item->order_type === 'normal') {
                                                $discountAmount = $basePrice * ($discountPercent / 100);
                                                $finalPrice = $basePrice - $discountAmount;
                                            }
                                            
                                            $rowClass = 'hover:bg-slate-50/80'; // Normal
                                            $badge = null;

                                            if ($item->is_backorder) {
                                                if ($item->backorder_fulfilled) {
                                                    $rowClass = 'bg-emerald-50/30 hover:bg-emerald-50/60 border-l-4 border-emerald-400';
                                                    $badge = '<span class="text-[9px] font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-200 flex items-center gap-1"><i class="fas fa-check-circle"></i> SURTIDO</span>';
                                                } else {
                                                    $rowClass = 'bg-purple-50/30 hover:bg-purple-50/60 border-l-4 border-purple-400';
                                                    $badge = '<span class="text-[9px] font-bold text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full border border-purple-200 flex items-center gap-1"><i class="fas fa-history"></i> PENDIENTE</span>';
                                                }
                                            }
                                        @endphp
                                        
                                        <tr class="group transition-colors {{ $rowClass }}">
                                            <td class="px-8 py-5">
                                                <div class="flex items-center gap-4">
                                                    {{-- Imagen del producto --}}
                                                    <div class="w-12 h-12 rounded-xl bg-white border border-slate-100 flex items-center justify-center p-1 shadow-sm relative overflow-hidden group-hover:border-slate-300 transition-colors">
                                                        @if($item->product->photo_url)
                                                            <img src="{{ $item->product->photo_url }}" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-110 mix-blend-multiply" alt="Producto">
                                                        @else
                                                            <i class="fas fa-box text-slate-200 text-lg"></i>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="flex flex-col">
                                                        <span class="font-bold text-slate-700 text-base {{ $item->is_backorder && !$item->backorder_fulfilled ? 'text-purple-700' : '' }}">
                                                            {{ $item->product->description }}
                                                        </span>
                                                        <div class="flex items-center gap-2 mt-1">
                                                            <span class="text-[10px] font-mono text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">{{ $item->product->sku }}</span>
                                                            @if($item->quality)
                                                                <span class="text-[9px] font-bold text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full border border-purple-200 flex items-center gap-1">
                                                                    <i class="fas fa-medal text-[8px]"></i> {{ $item->quality->name }}
                                                                </span>
                                                            @endif                                                            
                                                            
                                                            @if($badge)
                                                                {!! $badge !!}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-5 text-center">
                                                <span class="font-bold text-slate-700 text-base">{{ $qty }}</span>
                                            </td>
                                            <td class="px-4 py-5 text-right">
                                                @if($discountPercent > 0)
                                                    <div class="flex flex-col items-end">
                                                        <span class="text-[10px] text-slate-300 line-through decoration-slate-300">${{ number_format($basePrice, 2) }}</span>
                                                        <span class="font-bold text-[#2c3856] font-mono text-xs">${{ number_format($finalPrice, 2) }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-slate-400 font-mono text-xs">${{ number_format($basePrice, 2) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-5 text-center">
                                                @if($discountPercent > 0)
                                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 px-2 py-1 rounded-lg">-{{ number_format($discountPercent, 0) }}%</span>
                                                @else
                                                    <span class="text-slate-200 text-lg">·</span>
                                                @endif
                                            </td>
                                            <td class="px-8 py-5 text-right">
                                                <span class="font-bold text-[#2c3856] font-mono text-base">${{ number_format($finalPrice * $qty, 2) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-6">
                    
                    <div class="bg-[#2c3856] rounded-[2rem] p-8 text-white relative overflow-hidden shadow-xl shadow-blue-900/20">
                        <div class="absolute top-0 right-0 w-48 h-48 bg-[#ff9c00] rounded-full blur-[80px] opacity-20 -mr-16 -mt-16"></div>
                        
                        <p class="text-blue-200 text-xs font-bold uppercase tracking-widest mb-1">Monto Total</p>
                        <h2 class="text-4xl font-black tracking-tight mb-6 font-mono">${{ number_format($totalValue, 2) }}</h2>
                        
                        <div class="space-y-4 relative z-10">
                            <div class="flex justify-between text-sm py-3 border-t border-white/10">
                                <span class="text-blue-200">Subtotal Lista</span>
                                <span class="font-mono">${{ number_format($totalValue + $movements->sum('discount_amount'), 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm py-3 border-t border-white/10">
                                <span class="text-blue-200">Descuentos</span>
                                <span class="font-mono text-[#ff9c00]">-${{ number_format($movements->sum(function($item){ return $item->order_type == 'normal' ? ($item->product->unit_price * ($item->discount_percentage/100) * abs($item->quantity)) : 0; }), 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2rem] p-8 shadow-[0_2px_20px_rgba(0,0,0,0.02)] border border-slate-100">
                        <h3 class="font-bold text-[#2c3856] mb-4 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-rose-500"></i> Destino
                        </h3>
                        <div class="pl-6 border-l-2 border-slate-100 ml-1.5 space-y-4">
                            <div>
                                <p class="text-xs text-slate-400 uppercase font-bold mb-1">Localidad</p>
                                <p class="text-sm font-bold text-slate-700">{{ $header->locality }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase font-bold mb-1">Dirección</p>
                                <p class="text-sm text-slate-600 leading-relaxed">{{ $header->address }}</p>
                            </div>
                        </div>
                    </div>

                    @if(Auth::user()->hasFfPermission('orders.evidence'))
                    <div class="bg-white rounded-[2rem] p-8 shadow-[0_2px_20px_rgba(0,0,0,0.02)] border border-slate-100">

                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-[#2c3856] flex items-center gap-2">
                                <i class="fas fa-camera text-[#ff9c00]"></i> Evidencias de Entrega
                            </h3>
                            <span class="text-xs font-bold text-slate-400 bg-slate-50 px-3 py-1 rounded-full">
                                {{ $header->evidences->count() }} archivos
                            </span>
                        </div>

                        @if($header->evidences->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                                @foreach($header->evidences as $evidence)
                                    <div class="relative group bg-slate-50 border border-slate-100 rounded-xl p-3 flex items-center gap-3 hover:bg-white hover:shadow-md transition-all">
                                        
                                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 text-lg">
                                            @if(Str::endsWith(strtolower($evidence->filename), ['jpg','jpeg','png','webp']))
                                                <i class="fas fa-image"></i>
                                            @elseif(Str::endsWith(strtolower($evidence->filename), ['pdf']))
                                                <i class="fas fa-file-pdf text-red-500"></i>
                                            @elseif(Str::endsWith(strtolower($evidence->filename), ['xls','xlsx','csv']))
                                                <i class="fas fa-file-excel text-green-600"></i>
                                            @elseif(Str::endsWith(strtolower($evidence->filename), ['doc','docx']))
                                                <i class="fas fa-file-word text-blue-800"></i>
                                            @else
                                                <i class="fas fa-file text-slate-500"></i>
                                            @endif
                                        </div>

                                        <div class="flex-grow min-w-0">
                                            <p class="text-xs font-bold text-[#2c3856] truncate" title="{{ $evidence->filename }}">
                                                {{ $evidence->filename }}
                                            </p>
                                            <p class="text-[10px] text-slate-400">
                                                {{ $evidence->created_at->format('d/m/Y H:i') }} • {{ $evidence->uploader->name ?? 'Sistema' }}
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                            <button 
                                                type="button" 
                                                @click="openPreview('{{ route('ff.orders.downloadEvidence', ['path' => $evidence->path]) }}', '{{ $evidence->filename }}')" 
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" 
                                                title="Ver / Previsualizar">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            @if(Auth::user()->is_area_admin || Auth::user()->isSuperAdmin())
                                                <form action="{{ route('ff.evidence.delete', $evidence->id) }}" method="POST" onsubmit="return confirm('¿Eliminar evidencia permanentemente?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Eliminar">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-slate-50 rounded-2xl border border-dashed border-slate-300 mb-6">
                                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-300 mb-2">
                                    <i class="fas fa-folder-open text-xl"></i>
                                </div>
                                <p class="text-xs text-slate-400 font-medium">No hay evidencias cargadas aún.</p>
                            </div>
                        @endif

                        <form action="{{ route('ff.orders.uploadBatchEvidences', $header->folio) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            @if ($errors->any())
                                <div class="mb-4 p-3 bg-red-50 text-red-600 rounded-xl text-xs font-bold border border-red-100">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>• {{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <div class="relative w-full">
                                <label 
                                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all group"
                                    :class="isDragging ? 'border-[#ff9c00] bg-orange-50' : 'border-slate-300'"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop($event)">
                                    
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <i class="fas fa-cloud-upload-alt text-2xl mb-3 text-slate-400 group-hover:text-[#2c3856] transition-colors" :class="isDragging ? 'text-[#ff9c00]' : ''"></i>
                                        <p class="mb-1 text-sm text-slate-500 font-bold">
                                            <span class="font-black text-[#2c3856]">Clic para subir</span> o arrastra archivos
                                        </p>
                                        <p class="text-[10px] text-slate-400">PDF, JPG, PNG (Máx 20MB)</p>
                                    </div>
                                    
                                    <input 
                                        type="file" 
                                        name="evidences[]" 
                                        class="hidden" 
                                        multiple 
                                        x-ref="evidenceInput"
                                        @change="files = $event.target.files" 
                                    />
                                </label>
                            </div>

                            <div x-show="files.length > 0" class="mt-4 space-y-3" x-cloak>
                                <div class="text-xs font-bold text-[#2c3856] flex justify-between">
                                    <span>Archivos listos para subir:</span>
                                    <span x-text="files.length" class="bg-blue-100 text-blue-700 px-2 rounded"></span>
                                </div>
                                
                                <ul class="space-y-2 max-h-40 overflow-y-auto custom-scroll pr-2">
                                    <template x-for="file in files">
                                        <li class="flex items-center justify-between text-xs bg-white p-2 rounded border border-slate-200">
                                            <span class="truncate text-slate-600 w-3/4" x-text="file.name"></span>
                                            <span class="text-[10px] text-slate-400" x-text="(file.size/1024).toFixed(1) + ' KB'"></span>
                                        </li>
                                    </template>
                                </ul>

                                <button type="submit" class="w-full py-3 bg-[#2c3856] hover:bg-[#1e273d] text-white font-bold rounded-xl shadow-lg shadow-blue-900/20 transition-all flex items-center justify-center gap-2 mt-2">
                                    <i class="fas fa-save"></i>
                                    Guardar Evidencias
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif

                    @if(!in_array($header->status, ['cancelled', 'rejected']))
                        @if(Auth::user()->hasFfPermission('sales.checkout'))
                        <a href="{{ route('ff.sales.index', ['edit_folio' => $header->folio]) }}" 
                           class="block w-full bg-white border border-slate-200 text-slate-600 font-bold py-4 rounded-xl shadow-sm hover:bg-slate-50 hover:text-[#2c3856] hover:border-[#2c3856] transition-all text-sm text-center">
                            <i class="fas fa-pencil-alt mr-2"></i> Editar Pedido
                        </a>
                        @endif
                    @else
                        <div class="block w-full bg-slate-100 border border-slate-200 text-slate-400 font-bold py-4 rounded-xl text-sm text-center cursor-not-allowed">
                            <i class="fas fa-ban mr-2"></i> Edición Bloqueada ({{ ucfirst($header->status) }})
                        </div>
                    @endif
                    @if($header->order_type === 'prestamo' && !$header->is_loan_returned && !in_array($header->status, ['cancelled', 'rejected']))
                        @if(Auth::user()->hasFfPermission('sales.loans'))
                        <a href="{{ route('ff.sales.index', ['edit_folio' => $header->folio]) }}" 
                        class="block w-full bg-purple-600 border border-purple-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-purple-200 hover:bg-purple-700 transition-all text-sm text-center mb-3">
                            <i class="fas fa-boxes-packing mr-2"></i> Registrar Devolución
                        </a>
                        @endif
                    @endif                    

                </div>
            </div>
        </div>

        <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-[#2c3856]/40 backdrop-blur-sm" x-cloak 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md mx-4 relative" @click.away="showRejectModal = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="scale-95 opacity-0 translate-y-4"
                 x-transition:enter-end="scale-100 opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="scale-100 opacity-100 translate-y-0"
                 x-transition:leave-end="scale-95 opacity-0 translate-y-4">
                
                <div class="text-center mb-6">
                    <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4 text-xl">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="text-xl font-bold text-[#2c3856]">Rechazar Pedido</h3>
                    <p class="text-sm text-slate-500 mt-1">El stock reservado será liberado.</p>
                </div>

                <form action="{{ route('ff.orders.reject', $header->folio) }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Razón del rechazo</label>
                        <textarea name="reason" rows="3" class="w-full bg-slate-50 border-slate-200 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm p-4 font-medium resize-none transition-all outline-none" placeholder="Escribe el motivo aquí..." required></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="showRejectModal = false" class="flex-1 py-3 text-slate-600 font-bold text-sm bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Cancelar</button>
                        <button type="submit" class="flex-1 py-3 bg-rose-500 text-white font-bold text-sm hover:bg-rose-600 rounded-xl shadow-lg shadow-rose-200 transition-colors">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script src="https://unpkg.com/jszip@3.10.1/dist/jszip.min.js"></script>
    <script src="https://unpkg.com/docx-preview@0.1.15/dist/docx-preview.min.js"></script>

</x-app-layout>