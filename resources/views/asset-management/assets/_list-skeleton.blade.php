<div class="table-container border rounded-lg overflow-hidden">
    {{-- Cabecera de la tabla (sin cambios) --}}
    <div class="responsive-table-header hidden md:grid md:grid-cols-7 gap-4 bg-[#2c3856] p-4 font-bold text-xs text-[#ffffff] uppercase tracking-wider">
        <div class="col-span-1">Etiqueta</div>
        <div class="col-span-1">Categoría</div>
        <div class="col-span-1">Modelo</div>
        <div class="col-span-1">Estatus</div>
        <div class="col-span-1">Asignado a</div>
        <div class="col-span-1">Ubicación</div>
        <div class="col-span-1 text-right">Acciones</div>
    </div>

    {{-- Cuerpo de la tabla (el "fantasma") --}}
    <div class="divide-y md:divide-y-0">
        @for ($i = 0; $i < 5; $i++) {{-- Muestra 5 filas fantasma --}}
            <div class="hidden md:grid md:grid-cols-7 gap-4 p-4 items-center">
                <div><div class="skeleton-bar skeleton-shimmer w-3/4"></div></div>
                <div><div class="skeleton-bar skeleton-shimmer w-5/6"></div></div>
                <div><div class="skeleton-bar skeleton-shimmer w-full"></div></div>
                <div><div class="skeleton-bar skeleton-shimmer w-1/2"></div></div>
                <div><div class="skeleton-bar skeleton-shimmer w-4/5"></div></div>
                <div><div class="skeleton-bar skeleton-shimmer w-3/4"></div></div>
                <div class="flex items-center justify-end space-x-4">
                    <div class="skeleton-bar skeleton-shimmer w-6 h-6 rounded-full"></div>
                    <div class="skeleton-bar skeleton-shimmer w-6 h-6 rounded-full"></div>
                </div>
            </div>
            
            {{-- Vista móvil fantasma --}}
            <div class="asset-card md:hidden">
                <div class="asset-card-row">
                    <span class="asset-card-label"><div class="skeleton-bar skeleton-shimmer w-1/4"></div></span>
                    <span class="asset-card-value"><div class="skeleton-bar skeleton-shimmer w-1/2"></div></span>
                </div>
                <div class="asset-card-row">
                    <span class="asset-card-label"><div class="skeleton-bar skeleton-shimmer w-1/4"></div></span>
                    <span class="asset-card-value"><div class="skeleton-bar skeleton-shimmer w-1/3"></div></span>
                </div>
                {{-- ... puedes añadir más filas si lo deseas ... --}}
                <div class="flex items-center justify-end space-x-6 pt-4">
                    <div class="skeleton-bar skeleton-shimmer w-1/3 h-8"></div>
                    <div class="skeleton-bar skeleton-shimmer w-1/3 h-8"></div>
                </div>
            </div>
        @endfor
    </div>
</div>