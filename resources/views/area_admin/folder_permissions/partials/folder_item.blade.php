{{-- Este archivo se incluye desde index.blade.php --}}
{{-- Variables disponibles: $folder (carpeta actual), $level (nivel de anidamiento actual) --}}
<li x-data="{ open: false }" class="border-b border-gray-100 last:border-b-0 py-2">
    <div class="flex items-center justify-between">
        <div class="flex items-center" style="padding-left: {{ $level * 1.5 }}rem;"> {{-- Indentación basada en el nivel --}}
            {{-- Icono de toggle para carpetas con hijos --}}
            @if ($folder->children->isNotEmpty())
                <button @click="open = !open" class="mr-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg x-show="!open" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <svg x-show="open" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
            @else
                {{-- Espaciador para alineación si no hay hijos --}}
                <span class="w-6 h-4 inline-block"></span>
            @endif

            <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
            <span class="text-sm font-medium text-gray-800">{{ $folder->name }}</span>
            <span class="ml-2 text-xs text-gray-500">
                ({{ $folder->full_path }}) {{-- Usando el accesor full_path --}}
            </span>
        </div>
        <div>
            <a href="{{ route('area_admin.folder_permissions.edit', $folder) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-sm">Gestionar Permisos</a>
        </div>
    </div>

    {{-- Renderizado recursivo para los hijos --}}
    @if ($folder->children->isNotEmpty())
        <ul x-show="open" x-transition.opacity class="mt-2 space-y-2">
            @foreach ($folder->children as $childFolder)
                {{-- Incluye recursivamente el parcial, incrementando el nivel --}}
                @include('area_admin.folder_permissions.partials.folder_item', ['folder' => $childFolder, 'level' => $level + 1])
            @endforeach
        </ul>
    @endif
</li>