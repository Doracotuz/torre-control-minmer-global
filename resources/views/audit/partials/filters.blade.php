<div class="mb-6">
    <button @click="openFilters = !openFilters" class="w-full flex justify-between items-center px-4 py-3 bg-white rounded-lg shadow font-semibold text-gray-700">
        <span><i class="fas fa-filter mr-2"></i>Filtros de Búsqueda</span>
        <i class="fas" :class="openFilters ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
    </button>

    <div x-show="openFilters" x-transition class="bg-white p-4 mt-2 rounded-lg shadow-lg">
        <form x-ref="filtersForm" id="filtersForm" method="GET" action="{{ route('audit.index') }}" class="space-y-4">
            
            <input type="text" 
                   name="search" 
                   placeholder="Buscar por SO, Guía o Almacén..." 
                   value="{{ request('search') }}" 
                   class="w-full rounded-md border-gray-300 shadow-sm"
                   @input.debounce.1050ms="$refs.filtersForm.submit()">
            
            <div>
                <label for="location" class="text-sm font-medium text-gray-600">Almacén de Auditoría</label>
                <select name="location" id="location" class="w-full mt-1 rounded-md border-gray-300 shadow-sm" @change="$refs.filtersForm.submit()">
                    <option value="">Todos los Almacenes</option>
                    @if(isset($locations))
                        @foreach($locations as $location)
                            <option value="{{ $location }}" @selected(request('location') == $location)>
                                {{ $location }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="text-sm font-medium text-gray-600">Fecha Inicio (Creación)</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="w-full mt-1 rounded-md border-gray-300 shadow-sm" @change="$refs.filtersForm.submit()">
                </div>
                <div>
                    <label for="end_date" class="text-sm font-medium text-gray-600">Fecha Fin (Creación)</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="w-full mt-1 rounded-md border-gray-300 shadow-sm" @change="$refs.filtersForm.submit()">
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-600">Estatus de Auditoría</label>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                    @if(isset($auditStatuses))
                        @foreach($auditStatuses as $status)
                        <div class="flex items-center">
                            <input type="checkbox" name="status[]" value="{{ $status }}" id="status_{{ Str::slug($status) }}" class="h-4 w-4 rounded border-gray-300 text-indigo-600" @change="$refs.filtersForm.submit()" @checked(in_array($status, request('status', [])))>
                            <label for="status_{{ Str::slug($status) }}" class="ml-2 block text-sm text-gray-900">{{ $status }}</label>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>