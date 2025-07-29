<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Indicadores para el Área de: {{ $areaName }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                {{-- Botón para regresar a la vista de carpetas --}}
                <a href="{{ route('folders.index', ['folder' => $currentFolder->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    &larr; Volver a la Carpeta
                </a>
            </div>

            {{-- Contenedor del Iframe --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg" style="height: 80vh;">
                @if($reportUrl)
                    <iframe title="Reporte de Power BI para {{ $areaName }}" width="100%" height="100%" src="{{ $reportUrl }}" frameborder="0" allowFullScreen="true"></iframe>
                @else
                    {{-- Mensaje de error si no hay URL --}}
                    <div class="p-10 text-center text-gray-500">
                        <p class="font-semibold text-lg">Reporte no Disponible</p>
                        <p>El reporte de indicadores para esta área no está configurado.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>