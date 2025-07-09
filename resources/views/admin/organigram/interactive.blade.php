<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Organigrama Interactivo') }}
        </h2>
    </x-slot>

    {{-- Estilos personalizados (sin cambios) --}}
    <style>
        #chart-container { background-color: #f8f9fa; background-image: none; }
        .orgchart { background: transparent !important; }
        .orgchart .node { background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); padding: 0; width: 200px; margin: 20px; }
        .node-content-wrapper { padding: 1rem; text-align: center; }
        .node-title, .node-position { display: flex; align-items: center; justify-content: center; text-align: center; }
        .node-title { height: 2.5rem; line-height: 1.25; }
        .node-position { height: 2rem; }
        .orgchart .lines .line { border-color: #cbd5e1; }
        .orgchart .oc-edge-btn { width: 22px; height: 22px; border-radius: 50%; background-color: #2c3856; color: #ffffff; font-size: 14px; line-height: 22px; border: 1px solid #ffffff; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15); cursor: pointer; transition: all 0.3s ease; transform: translateY(-11px); }
        .orgchart .oc-edge-btn:hover { background-color: #ff9c00; transform: translateY(-11px) scale(1.1); }
    </style>

    {{-- Layout de pantalla completa --}}
    <div class="bg-gray-100 w-full h-full flex flex-col p-6">
        <div class="bg-white w-full h-full shadow-xl sm:rounded-lg border border-gray-200 flex flex-col">
            <div class="flex justify-end p-4 border-b border-gray-200">
                <a href="{{ route('admin.organigram.index') }}" class="inline-flex items-center px-5 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    {{ __('Volver a Gestión de Miembros') }}
                </a>
            </div>
            <div id="chart-container" class="w-full flex-1 overflow-auto"></div>

            <div x-data="areaModal"
                 @open-area-modal.window="openModal($event.detail)"
                 x-show="showModal"
                 x-transition
                 class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50"
                 style="display: none;"
                 @click.away="showModal = false"
                 @keydown.escape.window="showModal = false">
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

            <div x-data="memberModal"
                 @open-member-modal.window="openModal($event.detail)"
                 x-show="showModal"
                 x-transition
                 class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50"
                 style="display: none;"
                 @click.away="showModal = false"
                 @keydown.escape.window="showModal = false">
                <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col" @click.stop="">
                    <div class="flex justify-between items-center p-4 border-b border-gray-200">
                        <h3 class="text-2xl font-semibold text-[#2c3856]" x-text="data.name + ' - Detalles'"></h3>
                        <button @click="showModal = false" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                    </div>
                    <div class="p-6 flex-1 overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-1 space-y-4">
                                <img x-show="data.profile_photo_path" :src="data.profile_photo_path" class="w-40 h-40 rounded-full object-cover mx-auto border-4 border-gray-200 shadow-md">
                                <div x-show="!data.profile_photo_path" class="w-40 h-40 rounded-full bg-gray-200 flex items-center justify-center mx-auto border-4 shadow-md">
                                    <svg class="w-20 h-20 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg>
                                </div>
                                <div class="text-center space-y-2">
                                    <div><span class="font-semibold text-gray-500 block">Posición:</span> <span class="text-lg text-[#2c3856]" x-text="data.position_name"></span></div>
                                    <div><span class="font-semibold text-gray-500 block">Área:</span> <span x-text="data.area_name"></span></div>
                                    <div><span class="font-semibold text-gray-500 block">Jefe Directo:</span> <span x-text="data.manager_name || 'N/A'"></span></div>
                                    <div class="pt-2"><span class="font-semibold text-gray-500 block">Email:</span> <a :href="'mailto:' + data.email" class="text-blue-600 hover:underline" x-text="data.email"></a></div>
                                    <div><span class="font-semibold text-gray-500 block">Celular:</span> <span x-text="data.cell_phone"></span></div>
                                </div>
                            </div>
                            <div class="md:col-span-2 space-y-6">
                                <div>
                                    <h4 class="font-semibold text-lg text-[#2c3856] border-b pb-2 mb-2">Actividades</h4>
                                    <ul class="list-disc list-inside space-y-1 text-gray-700"><template x-for="activity in data.activities" :key="activity.id"><li x-text="activity.name"></li></template><template x-if="!data.activities || data.activities.length === 0"><li>No hay actividades asignadas.</li></template></ul>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-lg text-[#2c3856] border-b pb-2 mb-2">Habilidades</h4>
                                    <ul class="list-disc list-inside space-y-1 text-gray-700"><template x-for="skill in data.skills" :key="skill.id"><li x-text="skill.name"></li></template><template x-if="!data.skills || data.skills.length === 0"><li>No hay habilidades registradas.</li></template></ul>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-lg text-[#2c3856] border-b pb-2 mb-2">Trayectoria Profesional</h4>
                                    <div class="space-y-4"><template x-for="trajectory in data.trajectories" :key="trajectory.id"><div class="border-l-4 border-gray-300 pl-4"><p class="font-semibold" x-text="trajectory.title"></p><p class="text-sm text-gray-500" x-text="trajectory.start_date + ' - ' + (trajectory.end_date || 'Actual')"></p><p class="text-sm text-gray-600 mt-1" x-text="trajectory.description"></p></div></template><template x-if="!data.trajectories || data.trajectories.length === 0"><p>No hay trayectoria registrada.</p></template></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Librerías (sin cambios) --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.1.3/js/jquery.orgchart.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.1.3/css/jquery.orgchart.min.css" />
    @vite('resources/js/app.js')

    {{-- ===== SCRIPT PRINCIPAL CON CORRECCIONES ===== --}}
    <script>
        // FORMA CORRECTA Y ROBUSTA DE INICIALIZAR LOS DATOS DE ALPINE.JS
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
                data: {},
                openModal(detail) {
                    this.data = detail;
                    this.showModal = true;
                }
            }));
        });

        // LÓGICA DEL ORGANIGRAMA
        $(function() {
            const chartContainer = $('#chart-container');
            chartContainer.html('<p class="text-gray-500 text-center py-4">Cargando organigrama...</p>');

            $.ajax({
                url: `{{ url('/admin/organigram/interactive-data') }}`,
                method: 'GET',
                success: function(data) {
                    if (!data) {
                        chartContainer.html('<p>No se recibieron datos.</p>');
                        return;
                    }

                    chartContainer.empty().orgchart({
                        'data' : data,
                        'pan': true,
                        'zoom': true,
                        'direction': 't2b',
                        'nodeContent': 'title',
                        'depth': 1,
                        'nodeTemplate': function(data) {
                            let topBorderColor = '#e2e8f0';
                            if (data.type === 'root') topBorderColor = '#2c3856';
                            if (data.type === 'area') topBorderColor = '#ff9c00';
                            let photoHtml = '';
                            const placeholderDiv = `<div class="h-16 w-16 mx-auto mb-3"></div>`;
                            if (data.img) {
                                let imageFitClass = (data.type === 'root' || data.type === 'area') ? 'object-contain' : 'object-cover';
                                let imageShapeClass = data.type === 'member' ? 'rounded-full' : 'rounded-md';
                                photoHtml = `<div class="h-16 w-16 mx-auto mb-3"><img class="${imageShapeClass} ${imageFitClass} h-full w-full border-2 border-gray-200 shadow-sm" src="${data.img}"></div>`;
                            } else {
                                photoHtml = placeholderDiv;
                            }
                            return `<div class="node-header" style="background-color: ${topBorderColor}; height: 8px; border-radius: 0.5rem 0.5rem 0 0;"></div><div class="node-content-wrapper">${photoHtml}<div class="font-semibold text-gray-800 node-title">${data.name}</div><div class="text-xs text-gray-500 node-position">${data.title}</div></div>`;
                        },
                        
                        'onClickNode': function(node, nodeData) {
                            if (nodeData.type === 'member' && nodeData.full_details) {
                                window.dispatchEvent(new CustomEvent('open-member-modal', { detail: nodeData.full_details }));
                            } else if (nodeData.type === 'area') {
                                window.dispatchEvent(new CustomEvent('open-area-modal', { detail: nodeData }));
                            }
                        }
                    });

                    setTimeout(function() {
                        const containerWidth = chartContainer.width();
                        const chartElement = chartContainer.find('.orgchart');
                        const chartWidth = chartElement.width();
                        if (containerWidth > chartWidth) {
                            const horizontalOffset = (containerWidth - chartWidth) / 2;
                            chartElement.css('transform', `matrix(1, 0, 0, 1, ${horizontalOffset}, 20)`);
                        }
                    }, 100);
                },
                error: function(xhr, status, error) {
                    chartContainer.html('<p class="text-red-500 text-center py-4">Error al cargar el organigrama: ' + error + '</p>');
                    console.error("Error en AJAX:", xhr, status, error);
                }
            });
        });
    </script>
</x-app-layout>