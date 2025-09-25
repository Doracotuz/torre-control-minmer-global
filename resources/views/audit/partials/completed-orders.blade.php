<div class="bg-white rounded-xl shadow-md overflow-hidden mt-6">
    <button @click="completedOpen = !completedOpen" class="w-full p-4 text-left font-bold text-lg flex justify-between items-center">
        <span><i class="fas fa-check-circle text-gray-500 mr-2"></i> Auditorías Terminadas</span>
        <i class="fas" :class="completedOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
    </button>

    <div x-show="completedOpen" x-transition class="p-4 border-t">
        <form action="{{ route('audit.index') }}" method="GET" class="mb-4">
            @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
            @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
            
            <input type="text" 
                   name="search_completed" 
                   value="{{ request('search_completed') }}" 
                   placeholder="Buscar en terminadas por SO, Factura o Guía..." 
                   class="w-full rounded-md border-gray-300 shadow-sm"
                   onchange="this.form.submit()">
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($completedGuides as $guia)
                @include('audit.partials.audit-card-completed', ['guia' => $guia])
            @empty
                <p class="text-center text-gray-500 py-4 col-span-full">No se encontraron órdenes terminadas con los filtros aplicados.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $completedGuides->appends(request()->except('page'))->links('pagination::tailwind') }}
        </div>
    </div>
</div>