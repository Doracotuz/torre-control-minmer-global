<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Organigrama Interactivo') }}
        </h2>
    </x-slot>

    <style>
        #main-chart-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background-color: #f8f9fa; 
            cursor: grab;
        }
        #main-chart-wrapper:active {
            cursor: grabbing;
        }
        
        #main-chart-wrapper:fullscreen {
            background-color: #f8f9fa;
        }
        
        #chart-container {
            background-color: #f8f9fa;
            text-align: center;
            overflow: hidden;
            flex-grow: 1;
            min-height: 200px;
            position: relative;
            width: 100%;
            height: 100%;
        }
        .orgchart {
            background: transparent !important;
            display: inline-block;
            position: absolute;
            transition: transform 0.3s ease, left 0.3s ease, top 0.3s ease;
        }
        .orgchart .node {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            padding: 0;
            width: 200px;
            margin: 20px;
            position: relative;
            transition: all 0.3s ease;
            cursor: default;
        }
        .node-content-wrapper { padding: 1rem; text-align: center; }
        .node-title, .node-position { display: flex; align-items: center; justify-content: center; text-align: center; }
        .node-title { height: 2.5rem; line-height: 1.25; }
        .node-position { height: 2rem; }
        .orgchart .lines .line { border-color: #cbd5e1; }
        .orgchart .oc-edge-btn {
            width: 22px; height: 22px; border-radius: 50%; background-color: #2c3856; color: #ffffff;
            font-size: 14px; line-height: 22px; border: 1px solid #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15); cursor: pointer;
            transition: all 0.3s ease; transform: translateY(-11px);
        }
        .orgchart .oc-edge-btn:hover {
            background-color: #ff9c00; transform: translateY(-11px) scale(1.1);
        }
        .orgchart .node.collapsed { opacity: 0; transform: scale(0.8); visibility: hidden; }
        .orgchart .node.expanded { opacity: 1; transform: scale(1); visibility: visible; }
        .orgchart .node.is-proxy {
            border-style: dashed;
            border-color: #ff9c00;
            background-color: #fffaf0;
        }
        .orgchart .node.is-proxy .node-title::after {
            content: '(Rol Cruzado)';
            display: block;
            font-size: 0.65rem;
            color: #9ca3af;
            font-weight: normal;
        }

        /* --- ESTILOS DE JERARQUÍA (12 NIVELES) --- */
        .orgchart .node {
            border-left: 5px solid #e2e8f0;
        }
        .orgchart .node.level-1 { border-left-color: #EF4444; }
        .orgchart .node.level-2 { border-left-color: #F97316; }
        .orgchart .node.level-3 { border-left-color: #EAB308; }
        .orgchart .node.level-4 { border-left-color: #22C55E; }
        .orgchart .node.level-5 { border-left-color: #3B82F6; }
        .orgchart .node.level-6 { border-left-color: #6366F1; }
        .orgchart .node.level-7 { border-left-color: #8B5CF6; }
        .orgchart .node.level-8 { border-left-color: #EC4899; }
        .orgchart .node.level-9 { border-left-color: #6B7280; }
        .orgchart .node.level-10 { border-left-color: #06B6D4; }
        .orgchart .node.level-11 { border-left-color: #D946EF; }
        .orgchart .node.level-12 { border-left-color: #0F172A; }
        .orgchart .node.level-unknown { border-left-color: #A1A1AA; }
        .orgchart .node.is-proxy {
            border-style: dashed !important;
            border-width: 1px !important;
            border-color: #ff9c00 !important;
            background-color: #fffaf0 !important;
        }

        /* Estilos para pantalla completa y modales */
        .fullscreen-modal {
            z-index: 99999 !important;
        }
        
        :fullscreen .fullscreen-modal,
        :-webkit-full-screen .fullscreen-modal,
        :-moz-full-screen .fullscreen-modal,
        :-ms-fullscreen .fullscreen-modal {
            z-index: 2147483647 !important; /* Máximo z-index */
        }

        /* Mejora para el contenedor de arrastre */
        .orgchart-container {
            cursor: grab;
            user-select: none;
        }
        .orgchart-container:active {
            cursor: grabbing;
        }
    </style>

    <div class="bg-[#E8ECF7] w-full h-full flex flex-col p-6">
        <div class="bg-white w-full h-full shadow-xl sm:rounded-lg border border-gray-200 flex flex-col">
            
            <div class="flex justify-between items-center p-4 border-b border-gray-200">
                @if(!Auth::user()->is_client)
                    <a href="{{ route('admin.organigram.index') }}" class="inline-flex items-center px-5 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                        {{ __('Volver a Gestión de Miembros') }}
                    </a>
                @endif
                @unless(in_array(Auth::user()->id, [4, 24, 25, 26, 27]))
                <div class="flex items-center">
                    <input type="checkbox" id="toggleAreaNodes" class="form-checkbox h-5 w-5 text-[#2c3856] rounded focus:ring-[#ff9c00]">
                    <label for="toggleAreaNodes" class="ml-2 text-gray-700 select-none">{{ __('Mostrar Nodos de Área') }}</label>
                </div>
                @endunless
            </div>

            <div id="main-chart-wrapper" class="w-full flex-1">
                
                <div class="absolute top-4 right-4 z-10 flex flex-col space-y-2">
                    <button id="zoom-in-btn" title="Acercar" class="w-10 h-10 bg-white border border-gray-300 rounded-full shadow-md flex items-center justify-center text-gray-700 hover:bg-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#2c3856] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </button>
                    <button id="zoom-out-btn" title="Alejar" class="w-10 h-10 bg-white border border-gray-300 rounded-full shadow-md flex items-center justify-center text-gray-700 hover:bg-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#2c3856] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"></path></svg>
                    </button>
                    <button id="center-btn" title="Centrar" class="w-10 h-10 bg-white border border-gray-300 rounded-full shadow-md flex items-center justify-center text-gray-700 hover:bg-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#2c3856] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5v14"></path></svg>
                    </button>
                    <button id="fullscreen-btn" title="Pantalla Completa" class="w-10 h-10 bg-white border border-gray-300 rounded-full shadow-md flex items-center justify-center text-gray-700 hover:bg-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#2c3856] cursor-pointer">
                        <svg id="fullscreen-icon-enter" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"></path>
                        </svg>
                        <svg id="fullscreen-icon-exit" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 20L20 6M4 4l5 5m5-5v4m0 4h4M9 9l5 5m1 1v4m0 4h4"></path>
                        </svg>
                    </button>
                </div>
                <div id="chart-container"></div>
            </div>

            <div id="area-modal-container" x-data="areaModal" @open-area-modal.window="openModal($event.detail)" x-show="showModal" x-transition class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50 fullscreen-modal" style="display: none;" @click.away="showModal = false">
                <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg" @click.stop="">
                    <div class="flex justify-between items-center pb-3 border-b">
                        <h3 class="text-xl font-semibold text-[#2c3856]" x-text="data.name"></h3>
                        <button @click="showModal = false" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                    </div>
                    <div class="py-4">
                        <p class="text-gray-700" x-text="data.description || 'Esta área no tiene una descripción disponible.'"></p>
                    </div>
                    <div class="flex justify-end pt-3 border-t">
                        <button @click="showModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">{{ __('Cerrar') }}</button>
                    </div>
                </div>
            </div>

            <div id="member-modal-container" x-data="memberModal" @open-member-modal.window="openModal($event.detail)" x-show="showModal" x-transition class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50 fullscreen-modal" style="display: none;" @click.away="closeModalAndResetData()">
                <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden" @click.stop="">
                    <div class="flex justify-between items-center p-4 bg-[#2c3856] text-white">
                        <h3 class="text-xl font-bold" x-text="data?.name + ' - Detalles'"></h3>
                        <button @click="closeModalAndResetData()" class="text-gray-300 hover:text-white text-3xl leading-none">&times;</button>
                    </div>
                    <div class="p-6 flex-1 overflow-y-auto bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" x-show="data">
                            <div class="md:col-span-1 space-y-4 text-center">
                                <img x-show="data?.profile_photo_path" :src="data?.profile_photo_path" class="w-40 h-40 rounded-full object-cover mx-auto border-4 border-[#ff9c00] shadow-md">
                                <div x-show="!data?.profile_photo_path" class="w-40 h-40 rounded-full bg-gray-200 flex items-center justify-center mx-auto border-4 border-[#ff9c00] shadow-md">
                                    <svg class="w-20 h-20 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg>
                                </div>
                                <div class="space-y-3 text-sm">
                                    <div><p class="font-semibold text-gray-500 block">Posición</p><p class="text-lg font-bold text-[#2c3856]" x-text="data?.position_name"></p></div>
                                    <div x-show="data?.position_description">
                                        <p class="text-sm text-[#666666]" x-text="data?.position_description"></p>
                                    </div>
                                    <div><p class="font-semibold text-gray-500 block">Área</p><p class="text-base text-[#666666]" x-text="data?.area_name"></p></div>
                                    <div><p class="font-semibold text-gray-500 block">Jefe Directo</p><p class="text-base text-[#666666]" x-text="data?.manager_name || 'N/A'"></p></div>
                                    <div x-show="data?.hierarchy_level"><p class="font-semibold text-gray-500 block">Nivel Jerárquico</p><p class="text-base text-[#666666]" x-text="data?.hierarchy_level"></p></div>
                                    
                                    <div class="pt-2"><p class="font-semibold text-gray-500 block">Email</p><a :href="'mailto:' + data?.email" class="text-blue-600 hover:underline" x-text="data?.email"></a></div>
                                    <div><p class="font-semibold text-gray-500 block">Celular</p><p class="text-base text-[#666666]" x-text="data?.cell_phone"></p></div>
                                </div>
                            </div>
                            <div class="md:col-span-2 space-y-6">
                                <!-- <div x-show="data?.activities && data?.activities.length > 0">
                                    <h4 class="font-bold text-lg text-center text-[#2c3856] border-b-2 border-[#ff9c00] pb-2 mb-3">Plan de carrera</h4>
                                    <ul class="list-inside space-y-1 text-[#2b2b2b]">
                                        <template x-for="activity in data?.activities" :key="activity.id">
                                            <li x-text="activity.name"></li>
                                        </template>
                                    </ul>
                                </div> -->                                
                                <div><h4 class="font-bold text-lg text-[#2c3856] border-b-2 border-[#ff9c00] pb-2 mb-3">Habilidades</h4><ul class="list-disc list-inside space-y-1 text-[#2b2b2b]">
                                    <template x-for="skill in data?.skills" :key="skill.id"><li x-text="skill.name"></li></template>
                                    <li x-show="!data?.skills || data?.skills.length === 0" class="text-gray-500">No hay habilidades registradas.</li>
                                </ul></div>

                                <div><h4 class="font-bold text-lg text-[#2c3856] border-b-2 border-[#ff9c00] pb-2 mb-3">Trayectoria Profesional</h4><div class="space-y-4">
                                    <template x-for="trajectory in data?.trajectories" :key="trajectory.id">
                                        <div class="border-l-4 border-[#ff9c00] pl-4">
                                            <p class="font-semibold text-[#2c3856]" x-text="trajectory.title"></p>
                                            <p class="text-sm text-gray-500" x-text="trajectory.start_date + ' - ' + (trajectory.end_date || 'Actual')"></p>
                                            <p class="text-sm text-[#666666] mt-1" x-text="trajectory.description"></p>
                                        </div>
                                    </template>
                                    <p x-show="!data?.trajectories || data?.trajectories.length === 0" class="text-gray-500">No hay trayectoria registrada.</p>
                                </div></div>
                            </div>
                        </div>
                        <div x-show="data?.activities && data?.activities.length > 0">
                            <h4 class="font-bold text-lg text-center text-[#2c3856] border-b-2 border-[#ff9c00] pb-2 mb-3">Plan de carrera</h4>
                            <ul class="list-inside text-center space-y-1 text-[#2b2b2b]">
                                <template x-for="activity in data?.activities" :key="activity.id">
                                    <li x-text="activity.name"></li>
                                </template>
                            </ul>
                        </div>                            
                        <div x-show="!data" class="text-center text-gray-600">Cargando detalles...</div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.1.3/js/jquery.orgchart.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.1.3/css/jquery.orgchart.min.css" />
    @vite('resources/js/app.js')

<script>
    window.memberDetailsStore = {};
    let originalOrgchartData = null;
    let currentScale = 1.0;
    let currentPosition = { x: 0, y: 0 };

    const urlParams = new URLSearchParams(window.location.search);
    let showAreaNodesInitially = urlParams.get('show_areas') === 'true';

    document.addEventListener('alpine:init', () => {
        Alpine.data('areaModal', () => ({
            showModal: false,
            data: {},
            openModal(detail) {
                this.data = detail;
                this.showModal = true;
            }
        }));

        Alpine.data('memberModal', () => ({
            showModal: false,
            data: null,
            openModal(memberId) {
                const details = window.memberDetailsStore[memberId];
                if (details) {
                    this.data = JSON.parse(JSON.stringify(details));
                    this.showModal = true;
                } else {
                    console.error('Detalles no encontrados para el miembro ID:', memberId);
                    this.closeModalAndResetData();
                }
            },
            closeModalAndResetData() {
                this.showModal = false;
                Alpine.nextTick(() => {
                    this.data = null;
                });
            }
        }));
    });

    $(function() {
        const chartContainer = $('#chart-container');
        const toggleAreaNodesCheckbox = $('#toggleAreaNodes');

        toggleAreaNodesCheckbox.prop('checked', showAreaNodesInitially);

        function getOrgChartInstance() {
            return chartContainer.data('oc');
        }

        function renderOrgChart(data, initialDepth = 0) {
            const existingOrgchartElement = chartContainer.find('.orgchart');
            if (existingOrgchartElement.length > 0) {
                const ocInstance = existingOrgchartElement.data('oc');
                if (ocInstance && typeof ocInstance.destroy === 'function') {
                    ocInstance.destroy();
                }
            }
            chartContainer.empty();

            const oc = chartContainer.orgchart({
                data: data,
                pan: false,
                zoom: true,
                direction: 't2b',
                depth: initialDepth,
                collapsible: true,
                nodeContent: 'title',
                createNode: function($node, data) {
                    // Añadir clase de nivel jerárquico
                    if (data.hierarchy_level) {
                        $node.addClass('level-' + data.hierarchy_level);
                    }
                },
                nodeTemplate: function(data) {
                    let topBorderColor = data.type === 'root' ? '#2c3856' : (data.type === 'area' ? '#ff9c00' : '#e2e8f0');
                    let proxyClass = data.is_proxy ? ' is-proxy' : '';
                    let levelClass = data.hierarchy_level ? 'level-' + data.hierarchy_level : 'level-unknown';

                    let photoHtml = `<div class="h-16 w-16 mx-auto mb-3"></div>`;
                    if (data.img) {
                        let imageFitClass = (data.type === 'root' || data.type === 'area') ? 'object-contain' : 'object-cover';
                        photoHtml = `<div class="h-16 w-16 mx-auto mb-3"><img class="rounded-full ${imageFitClass} h-full w-full border-2 border-gray-200 shadow-sm" src="${data.img}"></div>`;
                    }

                    let detailsButtonHtml = '';
                    if (data.type === 'member') {
                        const memberIdForModal = data.is_proxy ? data.original_id : data.id;
                        detailsButtonHtml = `
                            <button
                                onclick="window.dispatchEvent(new CustomEvent('open-member-modal', { detail: '${memberIdForModal}' }))"
                                class="absolute top-1 right-1 p-1.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2c3856] transition-all duration-200 cursor-pointer"
                                title="Ver detalles de ${data.name}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                        `;
                    }

                    return `
                        <div class="node-header" style="background-color: ${topBorderColor}; height: 8px; border-radius: 0.5rem 0.5rem 0 0;"></div>
                        <div class="node-content-wrapper ${proxyClass} ${levelClass}" data-type="${data.type}">
                            ${detailsButtonHtml}
                            ${photoHtml}
                            <div class="font-semibold text-gray-800 node-title">${data.name}</div>
                            <div class="text-xs text-gray-500 node-position">${data.title}</div>
                        </div>`;
                },
                onClickNode: function(node, nodeData) {
                    if (nodeData.type === 'area') {
                        window.dispatchEvent(new CustomEvent('open-area-modal', {
                            detail: {
                                name: nodeData.name,
                                description: nodeData.description
                            }
                        }));
                    }
                }
            });

            // Configurar arrastre manual para todo el contenedor
            setupManualPanning();

            // Configurar eventos de botones de expansión/colapso
            chartContainer.off('click', '.oc-edge-btn');
            chartContainer.on('click', '.oc-edge-btn', function() {
                const $button = $(this);
                const $parentNodeElement = $button.closest('.node');
                const isExpanding = $parentNodeElement.hasClass('collapsed');

                setTimeout(() => {
                    if (isExpanding) {
                        centerAndZoomOnNode($parentNodeElement);
                    } else {
                        centerChart();
                    }
                }, 350);
            });

            // Ajustar el gráfico después de renderizar
            setTimeout(() => {
                centerChart();
            }, 500);
        }

        // ======================= CORRECCIÓN JAVASCRIPT 1 (Panning) =======================
        // Configuración de arrastre manual
        function setupManualPanning() {
            let isDragging = false;
            let startX, startY;
            let initialX = currentPosition.x;
            let initialY = currentPosition.y;
            let animationFrameId = null; // Para controlar el requestAnimationFrame

            chartContainer.off('mousedown touchstart');
            chartContainer.off('mousemove touchmove');
            $(document).off('mouseup mouseleave touchend.orgchartpan'); // Usar un namespace

            chartContainer.on('mousedown touchstart', function(e) {
                if (!$(e.target).closest('.node').length && 
                    !$(e.target).closest('.oc-edge-btn').length &&
                    !$(e.target).closest('button').length) {
                    
                    isDragging = true;
                    chartContainer.css('cursor', 'grabbing');
                    
                    const clientX = e.type.includes('touch') ? e.originalEvent.touches[0].clientX : e.clientX;
                    const clientY = e.type.includes('touch') ? e.originalEvent.touches[0].clientY : e.clientY;
                    
                    // Asegurarse de leer la posición actual al iniciar
                    initialX = currentPosition.x;
                    initialY = currentPosition.y;

                    startX = clientX - initialX;
                    startY = clientY - initialY;

                    // Cancelar cualquier frame pendiente si se inicia un nuevo arrastre
                    if (animationFrameId !== null) {
                        cancelAnimationFrame(animationFrameId);
                        animationFrameId = null;
                    }
                    
                    e.preventDefault();
                }
            });

            $(document).on('mousemove touchmove', function(e) {
                if (!isDragging) return;
                
                const clientX = e.type.includes('touch') ? e.originalEvent.touches[0].clientX : e.clientX;
                const clientY = e.type.includes('touch') ? e.originalEvent.touches[0].clientY : e.clientY;
                
                currentPosition.x = clientX - startX;
                currentPosition.y = clientY - startY;
                
                // Solicitar un frame de animación en lugar de actualizar el CSS directamente
                if (animationFrameId === null) {
                    animationFrameId = requestAnimationFrame(() => {
                        updateChartPosition();
                        animationFrameId = null; // Permitir que se solicite el siguiente frame
                    });
                }
                
                e.preventDefault();
            });

            // Usar un namespace (p.ej. '.orgchartpan') para no interferir con otros listeners
            $(document).on('mouseup.orgchartpan mouseleave.orgchartpan touchend.orgchartpan', function() {
                if (isDragging) {
                    isDragging = false;
                    chartContainer.css('cursor', 'grab');
                }
                // Cancelar el frame si el usuario suelta el mouse
                if (animationFrameId !== null) {
                    cancelAnimationFrame(animationFrameId);
                    animationFrameId = null;
                }
            });
        }
        // ======================= FIN CORRECCIÓN JAVASCRIPT 1 =======================


        function updateChartPosition() {
            const chartElement = chartContainer.find('.orgchart');
            if (chartElement.length) {
                chartElement.css({
                    'left': currentPosition.x + 'px',
                    'top': currentPosition.y + 'px',
                    'transform': `scale(${currentScale})`
                });
            }
        }

        function centerAndZoomOnNode($nodeElement) {
            const PADDING = 80;
            const MAX_ZOOM = 1.0;
            const MIN_ZOOM = 0.2;
            const chartElement = chartContainer.find('.orgchart');

            if (chartElement.length === 0 || $nodeElement.length === 0) { return; }

            const containerWidth = chartContainer.width();
            const containerHeight = chartContainer.height();
            const nodePosition = $nodeElement.position();
            const nodeWidth = $nodeElement.outerWidth(true);
            const nodeHeight = $nodeElement.outerHeight(true);
            const nodeCenterX = nodePosition.left + nodeWidth / 2;
            const nodeCenterY = nodePosition.top + nodeHeight / 2;

            let scaleX = (containerWidth - PADDING) / (nodeWidth * 1.5);
            let scaleY = (containerHeight - PADDING) / (nodeHeight * 1.5);
            let newScale = Math.min(scaleX, scaleY);
            newScale = Math.min(Math.max(newScale, MIN_ZOOM), MAX_ZOOM);
            currentScale = newScale;

            currentPosition.x = (containerWidth / 2) - (nodeCenterX * newScale);
            currentPosition.y = (containerHeight / 2) - (nodeCenterY * newScale);

            chartElement.css({
                'transition': 'transform 0.5s ease, left 0.5s ease, top 0.5s ease',
                'left': currentPosition.x + 'px',
                'top': currentPosition.y + 'px',
                'transform': `scale(${currentScale})`
            });

            setTimeout(() => {
                chartElement.css('transition', 'transform 0.3s ease, left 0.3s ease, top 0.3s ease');
            }, 550);
        }

        function centerChart() {
            const chartElement = chartContainer.find('.orgchart');
            if (chartElement.length === 0) {
                setTimeout(centerChart, 50);
                return;
            }

            const containerWidth = chartContainer.width();
            const containerHeight = chartContainer.height();
            const chartWidth = chartElement[0].scrollWidth * currentScale;
            const chartHeight = chartElement[0].scrollHeight * currentScale;

            currentPosition.x = (containerWidth - chartWidth) / 2;
            currentPosition.y = (containerHeight - chartHeight) / 2;

            chartElement.css({
                'transition': 'transform 0.3s ease, left 0.3s ease, top 0.3s ease',
                'left': currentPosition.x + 'px',
                'top': currentPosition.y + 'px',
                'transform': `scale(${currentScale})`
            });
        }

        // Función para aplicar zoom manualmente
        function applyZoom(scale) {
            const chartElement = chartContainer.find('.orgchart');
            if (chartElement.length === 0) return;

            const MIN_ZOOM = 0.2;
            const MAX_ZOOM = 2.0;
            
            currentScale = Math.min(Math.max(scale, MIN_ZOOM), MAX_ZOOM);
            
            chartElement.css({
                'transform': `scale(${currentScale})`
            });
        }

        let dataUrl;

        @if (Auth::check() && Auth::user()->is_client)
            dataUrl = showAreaNodesInitially
                ? `{{ route('client.organigram.data') }}`
                : `{{ route('client.organigram.data.without-areas') }}`;
        @else
            dataUrl = showAreaNodesInitially
                ? `{{ route('admin.organigram.interactive.data') }}`
                : `{{ route('admin.organigram.interactive.data.without-areas') }}`;
        @endif

        const loadingMessageDiv = $('<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-gray-500">Cargando organigrama...</div>');
        chartContainer.append(loadingMessageDiv);

        $.ajax({
            url: dataUrl,
            method: 'GET',
            cache: false,
            success: function(response) {
                loadingMessageDiv.remove();

                if (!response || !response.id) {
                    chartContainer.html('<p class="text-red-500 text-center py-4">No se recibieron datos válidos.</p>');
                    return;
                }
                originalOrgchartData = response;

                function extractDetails(node) {
                    const storeId = node.is_proxy ? node.original_id : node.id;
                    if (node.type === 'member' && node.full_details) {
                        if (!window.memberDetailsStore[storeId]) {
                            window.memberDetailsStore[storeId] = {
                                name: node.full_details.name || '',
                                email: node.full_details.email || '',
                                cell_phone: node.full_details.cell_phone || '',
                                position_name: node.full_details.position_name || '',
                                position_description: node.full_details.position_description || '',
                                area_name: node.full_details.area_name || '',
                                manager_name: node.full_details.manager_name || '',
                                profile_photo_path: node.full_details.profile_photo_path || null,
                                hierarchy_level: node.full_details.hierarchy_level || null,
                                activities: node.full_details.activities?.map(item => ({ ...item })) || [],
                                skills: node.full_details.skills?.map(item => ({ ...item })) || [],
                                trajectories: node.full_details.trajectories?.map(item => ({ ...item })) || []
                            };
                        }
                    }
                    if (node.children && node.children.length > 0) {
                        node.children.forEach(child => extractDetails(child));
                    }
                }
                extractDetails(originalOrgchartData);

                renderOrgChart(originalOrgchartData, 0);
            },
            error: function(xhr, status, error) {
                loadingMessageDiv.remove();
                chartContainer.html('<p class="text-red-500 text-center py-4">Error al cargar el organigrama: ' + error + '</p>');
                console.error('Error en AJAX:', xhr, status, error);
            }
        });

        // Evento para toggle de nodos de área
        toggleAreaNodesCheckbox.on('change', function() {
            const isChecked = this.checked;
            const currentUrl = new URL(window.location.href);

            if (isChecked) {
                currentUrl.searchParams.set('show_areas', 'true');
            } else {
                currentUrl.searchParams.set('show_areas', 'false');
            }
            window.location.href = currentUrl.toString();
        });

        // --- CONTROLES CORREGIDOS ---

        // Zoom In
        $('#zoom-in-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            applyZoom(currentScale * 1.2);
        });

        // Zoom Out
        $('#zoom-out-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            applyZoom(currentScale / 1.2);
        });

        // Centrar (solo centra, no cambia zoom)
        $('#center-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            centerChart();
        });

        // Pantalla Completa
        $('#fullscreen-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const chartWrapper = document.getElementById('main-chart-wrapper');

            if (!document.fullscreenElement) {
                if (chartWrapper.requestFullscreen) {
                    chartWrapper.requestFullscreen();
                } else if (chartWrapper.mozRequestFullScreen) {
                    chartWrapper.mozRequestFullScreen();
                } else if (chartWrapper.webkitRequestFullscreen) {
                    chartWrapper.webkitRequestFullscreen();
                } else if (chartWrapper.msRequestFullscreen) {
                    chartWrapper.msRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }
        });

        // ======================= CORRECCIÓN JAVASCRIPT 2 (Fullscreen Modal) =======================
        // Escuchar cambios de pantalla completa
        document.addEventListener('fullscreenchange', () => {
            const isFullscreen = !!document.fullscreenElement;
            const chartWrapper = $('#main-chart-wrapper');
            const areaModal = $('#area-modal-container');
            const memberModal = $('#member-modal-container');
            
            if (isFullscreen) {
                $('#fullscreen-icon-enter').hide();
                $('#fullscreen-icon-exit').show();

                // Mover los modales DENTRO del wrapper que está en fullscreen
                // para que se muestren por encima.
                chartWrapper.append(areaModal);
                chartWrapper.append(memberModal);

            } else {
                $('#fullscreen-icon-enter').show();
                $('#fullscreen-icon-exit').hide();

                // Devolver los modales al <body> para que funcionen
                // correctamente fuera del modo fullscreen.
                $('body').append(areaModal);
                $('body').append(memberModal);
            }
            
            // Reajustar el centrado después del cambio
            setTimeout(() => {
                centerChart();
            }, 100);
        });
        // ======================= FIN CORRECCIÓN JAVASCRIPT 2 =======================

        // Redimensionamiento de ventana
        $(window).on('resize', () => {
            centerChart();
        });
    });
</script>
</x-app-layout>