<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Organigrama Interactivo') }}
        </h2>
    </x-slot>

    {{-- Estilos personalizados para el organigrama --}}
    <style>
        /* Fondo general para el contenedor del organigrama */
        #chart-container {
            background-color: #f8f9fa;
            background-image: none;
        }

        /* Contenedor principal de la librería */
        .orgchart {
            background: transparent !important;
        }

        /* Estilo para cada nodo (la tarjeta) */
        .orgchart .node {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            padding: 0;
            width: 200px;
            margin: 20px; 
        }
        
        /* Contenido personalizado dentro del nodo */
        .node-content-wrapper {
            padding: 1rem;
            text-align: center;
        }
        
        /* ---- NUEVO: Clases para estandarizar altura de texto ---- */
        .node-title, .node-position {
            display: flex;
            align-items: center; /* Centrado vertical */
            justify-content: center; /* Centrado horizontal */
            text-align: center;
        }
        .node-title {
            height: 2.5rem; /* 40px - Espacio para hasta dos líneas de texto */
            line-height: 1.25;
        }
        .node-position {
            height: 2rem; /* 32px - Espacio para el cargo */
        }

        /* Estilo para las líneas conectoras */
        .orgchart .lines .line {
            border-color: #cbd5e1;
        }
    </style>

    <div class="py-12 bg-gray-100">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-8">
                <div class="flex justify-end mb-4">
                    <a href="{{ route('admin.organigram.index') }}"
                        class="inline-flex items-center px-5 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md">
                        {{ __('Volver a Gestión de Miembros') }}
                    </a>
                </div>

                <div id="chart-container" class="w-full h-[700px] border border-gray-300 rounded-lg shadow-inner overflow-auto"></div>

                {{-- Modal de Propiedades (sin cambios) --}}
                <div x-data="propertiesModalData()" 
                     @open-properties-modal.window="openPropertiesModal($event.detail)"
                     x-show="showPropertiesModal"
                     x-transition
                     class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50"
                     style="display: none;" @click.away="showPropertiesModal = false"
                     @keydown.escape.window="showPropertiesModal = false">
                    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" @click.stop="">
                        <div class="flex justify-between items-center pb-4 border-b border-gray-200 mb-4">
                            <h3 class="text-xl font-semibold text-[#2c3856]" x-text="propertiesData.name + ' - Detalles'"></h3>
                            <button @click="showPropertiesModal = false" class="text-gray-500 hover:text-gray-700"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                        </div>
                        <div class="flex-1 overflow-y-auto text-gray-700 grid grid-cols-1 md:grid-cols-2 gap-4">
                           <div>
                                <div class="mb-3 flex flex-col sm:flex-row items-center sm:items-start">
                                    <img x-show="propertiesData.profile_photo_path" :src="propertiesData.profile_photo_path" class="h-24 w-24 rounded-full object-cover border-4 border-gray-200 shadow-md mb-3 sm:mb-0 sm:mr-4">
                                    <div x-show="!propertiesData.profile_photo_path" class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 border-4 border-gray-300 shadow-md mb-3 sm:mb-0 sm:mr-4">
                                        <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg>
                                    </div>
                                    <div class="w-full text-center sm:text-left">
                                        <span class="font-semibold text-[#2c3856]">Nombre:</span> <span x-text="propertiesData.name"></span><br>
                                        <span class="font-semibold text-[#2c3856]">Posición:</span> <span x-text="propertiesData.position_name"></span><br>
                                        <span class="font-semibold text-[#2c3856]">Área:</span> <span x-text="propertiesData.area_name"></span>
                                    </div>
                                </div>
                                <div class="mb-3"><span class="font-semibold text-[#2c3856]">Email:</span> <span x-text="propertiesData.email"></span></div>
                                <div class="mb-3"><span class="font-semibold text-[#2c3856]">Celular:</span> <span x-text="propertiesData.cell_phone"></span></div>
                                <div class="mb-3"><span class="font-semibold text-[#2c3856]">Jefe Directo:</span> <span x-text="propertiesData.manager_name || 'N/A'"></span></div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <span class="font-semibold text-[#2c3856]">Actividades:</span>
                                    <ul class="list-disc list-inside text-sm"><template x-for="activity in propertiesData.activities" :key="activity.id"><li x-text="activity.name"></li></template><template x-if="!propertiesData.activities || propertiesData.activities.length === 0"><li>N/A</li></template></ul>
                                </div>
                                <div class="mb-4">
                                    <span class="font-semibold text-[#2c3856]">Habilidades:</span>
                                    <ul class="list-disc list-inside text-sm"><template x-for="skill in propertiesData.skills" :key="skill.id"><li x-text="skill.name"></li></template><template x-if="!propertiesData.skills || propertiesData.skills.length === 0"><li>N/A</li></template></ul>
                                </div>
                                <div class="mb-4">
                                    <span class="font-semibold text-[#2c3856]">Trayectoria:</span>
                                    <ul class="list-disc list-inside text-sm"><template x-for="trajectory in propertiesData.trajectories" :key="trajectory.id"><li><span x-text="trajectory.title"></span> (<span x-text="trajectory.start_date"></span> - <span x-text="trajectory.end_date || 'Actual'"></span>)<p x-show="trajectory.description" class="text-xs text-gray-500 pl-4" x-text="trajectory.description"></p></li></template><template x-if="!propertiesData.trajectories || propertiesData.trajectories.length === 0"><li>N/A</li></template></ul>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end pt-4 border-t border-gray-200"><button @click="showPropertiesModal = false" class="inline-flex items-center px-5 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">{{ __('Cerrar') }}</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function propertiesModalData() { /* ... (sin cambios) ... */ }
    </script>
    
    {{-- Librerías --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.1.3/js/jquery.orgchart.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.1.3/css/jquery.orgchart.min.css" />
    @vite('resources/js/app.js')

    {{-- Script Principal del Organigrama --}}
    <script>
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
                        
                        /* ---- NUEVO: Se define la profundidad inicial para plegar el organigrama ---- */
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

                            return `
                                <div class="node-header" style="background-color: ${topBorderColor}; height: 8px; border-radius: 0.5rem 0.5rem 0 0;"></div>
                                <div class="node-content-wrapper">
                                    ${photoHtml}
                                    <div class="font-semibold text-gray-800 node-title">${data.name}</div>
                                    <div class="text-xs text-gray-500 node-position">${data.title}</div>
                                </div>
                            `;
                        },

                        'onClickNode': function(node, nodeData) { /* ... (sin cambios) ... */ }
                    });
                },
                error: function(xhr, status, error) { /* ... (sin cambios) ... */ }
            });
        });
    </script>
</x-app-layout>