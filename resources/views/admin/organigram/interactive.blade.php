<x-app-layout>
    <x-slot name="header">
  
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Organigrama Interactivo') }}
        </h2>
    </x-slot>

    {{-- Estilos (sin cambios en esta sección) --}}
    <style>
        #main-chart-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        #chart-container {
            background-color: #f8f9fa;
            text-align: center;
            overflow: hidden;
            flex-grow: 1;
            min-height: 200px;
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
    </style>

    {{-- Layout y Modales (sin cambios en la estructura principal del layout) --}}
    <div class="bg-[#E8ECF7] w-full h-full flex flex-col p-6">
        <div class="bg-white w-full h-full shadow-xl sm:rounded-lg border border-gray-200 flex flex-col">
            <div class="flex justify-between items-center p-4 border-b border-gray-200">
                @if(!Auth::user()->is_client)
                    <a href="{{ route('admin.organigram.index') }}" class="inline-flex items-center px-5 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                        {{ __('Volver a Gestión de Miembros') }}
                    </a>
                @endif
                <div class="flex items-center">
                    <input type="checkbox" id="toggleAreaNodes" class="form-checkbox h-5 w-5 text-[#2c3856] rounded focus:ring-[#ff9c00]">
                    <label for="toggleAreaNodes" class="ml-2 text-gray-700 select-none">{{ __('Mostrar Nodos de Área') }}</label>
                </div>
            </div>

            <div id="main-chart-wrapper" class="w-full flex-1">
                <div id="chart-container"></div>
            </div>

            {{-- Modal para Áreas (sin cambios) --}}
            <div x-data="areaModal" @open-area-modal.window="openModal($event.detail)" x-show="showModal" x-transition class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50" style="display: none;" @click.away="showModal = false">
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

            {{-- Modal para Miembros - Actualizado con operador de encadenamiento opcional --}}
            <div x-data="memberModal" @open-member-modal.window="openModal($event.detail)" x-show="showModal" x-transition class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50" style="display: none;" @click.away="closeModalAndResetData()">
                <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden" @click.stop="">
                    <div class="flex justify-between items-center p-4 bg-[#2c3856] text-white">
                        <h3 class="text-xl font-bold" x-text="data?.name + ' - Detalles'"></h3> {{-- Agregado ?. --}}
                        <button @click="closeModalAndResetData()" class="text-gray-300 hover:text-white text-3xl leading-none">&times;</button>
                    </div>
                    <div class="p-6 flex-1 overflow-y-auto bg-gray-50">
                        {{-- Se muestra este div solo si hay datos cargados (data no es null) --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" x-show="data">
                            <div class="md:col-span-1 space-y-4 text-center">
                                <img x-show="data?.profile_photo_path" :src="data?.profile_photo_path" class="w-40 h-40 rounded-full object-cover mx-auto border-4 border-[#ff9c00] shadow-md"> {{-- Agregado ?. --}}
                                <div x-show="!data?.profile_photo_path" class="w-40 h-40 rounded-full bg-gray-200 flex items-center justify-center mx-auto border-4 border-[#ff9c00] shadow-md"> {{-- Agregado ?. --}}
                                    <svg class="w-20 h-20 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg>
                                </div>
                                <div class="space-y-3 text-sm">
                                    <div><p class="font-semibold text-gray-500 block">Posición</p><p class="text-lg font-bold text-[#2c3856]" x-text="data?.position_name"></p></div> {{-- Agregado ?. --}}
                                    <div><p class="font-semibold text-gray-500 block">Área</p><p class="text-base text-[#666666]" x-text="data?.area_name"></p></div> {{-- Agregado ?. --}}
                                    <div><p class="font-semibold text-gray-500 block">Jefe Directo</p><p class="text-base text-[#666666]" x-text="data?.manager_name || 'N/A'"></p></div> {{-- Agregado ?. --}}
                                    <div class="pt-2"><p class="font-semibold text-gray-500 block">Email</p><a :href="'mailto:' + data?.email" class="text-blue-600 hover:underline" x-text="data?.email"></a></div> {{-- Agregado ?. --}}
                                    <div><p class="font-semibold text-gray-500 block">Celular</p><p class="text-base text-[#666666]" x-text="data?.cell_phone"></p></div> {{-- Agregado ?. --}}
                                </div>

                                <div x-show="data?.activities && data?.activities.length > 0">
                                    <h4 class="font-bold text-lg text-[#2c3856] border-b-2 border-[#ff9c00] pb-2 mb-3">Plan de carrera</h4>
                                    <ul class="list-inside space-y-1 text-[#2b2b2b]">
                                        <template x-for="activity in data?.activities" :key="activity.id">
                                            <li x-text="activity.name"></li>
                                        </template>
                                    </ul>
                                </div>

                            </div>
                            <div class="md:col-span-2 space-y-6">
                                <!-- <div x-show="data?.activities && data?.activities.length > 0">
                                    <h4 class="font-bold text-lg text-[#2c3856] border-b-2 border-[#ff9c00] pb-2 mb-3">Plan de carrera</h4>
                                    <ul class="list-disc list-inside space-y-1 text-[#2b2b2b]">
                                        <template x-for="activity in data?.activities" :key="activity.id">
                                            <li x-text="activity.name"></li>
                                        </template>
                                    </ul>
                                </div> -->

                                <div><h4 class="font-bold text-lg text-[#2c3856] border-b-2 border-[#ff9c00] pb-2 mb-3">Habilidades</h4><ul class="list-disc list-inside space-y-1 text-[#2b2b2b]">
                                    <template x-for="skill in data?.skills" :key="skill.id"><li x-text="skill.name"></li></template> {{-- Agregado ?. --}}
                                    <li x-show="!data?.skills || data?.skills.length === 0" class="text-gray-500">No hay habilidades registradas.</li> {{-- Agregado ?. --}}
                                </ul></div>

                                <div><h4 class="font-bold text-lg text-[#2c3856] border-b-2 border-[#ff9c00] pb-2 mb-3">Trayectoria Profesional</h4><div class="space-y-4">
                                    <template x-for="trajectory in data?.trajectories" :key="trajectory.id"> {{-- Agregado ?. --}}
                                        <div class="border-l-4 border-[#ff9c00] pl-4">
                                            <p class="font-semibold text-[#2c3856]" x-text="trajectory.title"></p>
                                            <p class="text-sm text-gray-500" x-text="trajectory.start_date + ' - ' + (trajectory.end_date || 'Actual')"></p>
                                            <p class="text-sm text-[#666666] mt-1" x-text="trajectory.description"></p>
                                        </div>
                                    </template>
                                    <p x-show="!data?.trajectories || data?.trajectories.length === 0" class="text-gray-500">No hay trayectoria registrada.</p> {{-- Agregado ?. --}}
                                </div></div>
                            </div>
                        </div>
                        <div x-show="!data" class="text-center text-gray-600">Cargando detalles...</div> {{-- Mensaje mientras se carga o si no hay datos --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Librerías --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.1.3/js/jquery.orgchart.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.1.3/css/jquery.orgchart.min.css" />
    @vite('resources/js/app.js')

    {{-- Script Principal Optimizado --}}
<script>
    // Almacenamiento global para detalles de miembros
    window.memberDetailsStore = {};
    let originalOrgchartData = null; // Almacena los datos originales obtenidos

    // Obtener el estado inicial de "mostrar nodos de área" de la URL
    const urlParams = new URLSearchParams(window.location.search);
    let showAreaNodesInitially = !(urlParams.get('show_areas') === 'false');

    // Inicialización de componentes de Alpine.js para los modales
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
            data: null, // Inicializado como null para indicar que no hay datos cargados
            openModal(memberId) {
                const details = window.memberDetailsStore[memberId];
                if (details) {
                    // Copia profunda para evitar cualquier referencia compartida y asegurar un estado fresco
                    this.data = JSON.parse(JSON.stringify(details));
                    this.showModal = true;
                } else {
                    console.error('Detalles no encontrados para el miembro ID:', memberId);
                    this.closeModalAndResetData();
                }
            },
            closeModalAndResetData() {
                this.showModal = false;
                // Restablecer data a null después de que el modal se oculte visualmente
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

        function renderOrgChart(data, initialDepth = 0) {
            const existingOrgchartElement = chartContainer.find('.orgchart');
            if (existingOrgchartElement.length > 0) {
                const ocInstance = existingOrgchartElement.data('oc');
                if (ocInstance && typeof ocInstance.destroy === 'function') {
                    ocInstance.destroy();
                }
            }
            chartContainer.empty();


            chartContainer.orgchart({
                data: data,
                pan: true,
                zoom: true,
                direction: 't2b',
                depth: initialDepth,
                collapsible: true,
                nodeContent: 'title',
                nodeTemplate: function(data) {
                    let topBorderColor = data.type === 'root' ? '#2c3856' : (data.type === 'area' ? '#ff9c00' : '#e2e8f0');
                    let proxyClass = data.is_proxy ? ' is-proxy' : '';

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
                                @click.stop="window.dispatchEvent(new CustomEvent('open-member-modal', { detail: '${memberIdForModal}' }))"
                                class="absolute top-1 right-1 p-1.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2c3856] transition-all duration-200"
                                title="Ver detalles de ${data.name}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                        `;
                    }

                    return `
                        <div class="node-header" style="background-color: ${topBorderColor}; height: 8px; border-radius: 0.5rem 0.5rem 0 0;"></div>
                        <div class="node-content-wrapper${proxyClass}" data-type="${data.type}">
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

            chartContainer.off('click', '.oc-edge-btn');

            chartContainer.on('click', '.oc-edge-btn', function() {
                const $button = $(this);
                const $parentNodeElement = $button.closest('.node');
                const isExpanding = $parentNodeElement.hasClass('collapsed');

                setTimeout(() => {
                    if (isExpanding) {
                        centerAndZoomOnNode($parentNodeElement);
                    } else {
                        requestAnimationFrame(fitChart);
                    }
                }, 350);
            });

            setTimeout(() => {
                requestAnimationFrame(fitChart);
            }, 500);
        }

        function centerAndZoomOnNode($nodeElement) {
            const PADDING = 80;
            const MAX_ZOOM = 1.0;
            const MIN_ZOOM = 0.2;
            const chartElement = chartContainer.find('.orgchart');

            if (chartElement.length === 0 || $nodeElement.length === 0) {
                console.warn("centerAndZoomOnNode: Orgchart or node element not found.");
                return;
            }

            const containerWidth = chartContainer.width();
            const containerHeight = chartContainer.height();

            const nodePosition = $nodeElement.position();
            const nodeWidth = $nodeElement.outerWidth(true);
            const nodeHeight = $nodeElement.outerHeight(true);

            const nodeCenterX = nodePosition.left + nodeWidth / 2;
            const nodeCenterY = nodePosition.top + nodeHeight / 2;

            let targetVisibleHeight = nodeHeight * 1.5;
            let targetVisibleWidth = nodeWidth * 1.5;

            let scaleX = (containerWidth - PADDING) / targetVisibleWidth;
            let scaleY = (containerHeight - PADDING) / targetVisibleHeight;
            let newScale = Math.min(scaleX, scaleY);
            newScale = Math.min(Math.max(newScale, MIN_ZOOM), MAX_ZOOM);

            const newX = (containerWidth / 2) - (nodeCenterX * newScale);
            const newY = (containerHeight / 2) - (nodeCenterY * newScale);

            chartElement.css({
                'transition': 'transform 0.5s ease, left 0.5s ease, top 0.5s ease',
                'left': newX + 'px',
                'top': newY + 'px',
                'transform': `scale(${newScale})`,
                'transform-origin': 'top left'
            });

            setTimeout(() => {
                chartElement.css('transition', 'transform 0.3s ease, left 0.3s ease, top 0.3s ease');
            }, 550);
        }

        function fitChart() {
            const PADDING = 40;
            const MAX_ZOOM = 1.0;
            const MIN_ZOOM = 0.2;

            const chartElement = chartContainer.find('.orgchart');
            if (chartElement.length === 0) {
                console.warn("fitChart: .orgchart element not found. Retrying in 50ms.");
                setTimeout(() => requestAnimationFrame(fitChart), 50);
                return;
            }

            const containerWidth = chartContainer.width();
            const containerHeight = chartContainer.height();

            const chartWidth = chartElement[0].scrollWidth;
            const chartHeight = chartElement[0].scrollHeight;

            if (chartWidth === 0 || chartHeight === 0) {
                console.warn("fitChart: chartWidth or chartHeight is zero. This indicates the chart might not be fully rendered yet. Retrying in 100ms.");
                setTimeout(() => requestAnimationFrame(fitChart), 100);
                return;
            }

            let scale = Math.min((containerWidth - PADDING) / chartWidth, (containerHeight - PADDING) / chartHeight);
            scale = Math.min(Math.max(scale, MIN_ZOOM), MAX_ZOOM);

            const scaledWidth = chartWidth * scale;
            const scaledHeight = chartHeight * scale;
            const newX = (containerWidth - scaledWidth) / 2;
            const newY = (containerHeight - scaledHeight) / 2;

            chartElement.css({
                'transition': 'transform 0.3s ease, left 0.3s ease, top 0.3s ease',
                'left': newX + 'px',
                'top': newY + 'px',
                'transform': `scale(${scale})`,
                'transform-origin': 'top left'
            });
        }

        // const showAreaNodesInitially = !(new URLSearchParams(window.location.search).get('show_areas') === 'false');
        let dataUrl;

        @if (Auth::check() && Auth::user()->is_client)
            // Si el usuario es un cliente, usa las rutas seguras para clientes
            dataUrl = showAreaNodesInitially
                ? `{{ route('client.organigram.data') }}`
                : `{{ route('client.organigram.data.without-areas') }}`;
        @else
            // Si no, usa las rutas de administrador como antes
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
                        // Solo almacenar si no existe ya en memberDetailsStore
                        if (!window.memberDetailsStore[storeId]) {
                            window.memberDetailsStore[storeId] = {
                                name: node.full_details.name || '',
                                email: node.full_details.email || '',
                                cell_phone: node.full_details.cell_phone || '',
                                position_name: node.full_details.position_name || '',
                                area_name: node.full_details.area_name || '',
                                manager_name: node.full_details.manager_name || '',
                                profile_photo_path: node.full_details.profile_photo_path || null,
                                // Uso de operador de encadenamiento opcional y mapeo para copias
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

        $(window).on('resize', () => {
            requestAnimationFrame(fitChart);
        });
    });
</script>
</x-app-layout>