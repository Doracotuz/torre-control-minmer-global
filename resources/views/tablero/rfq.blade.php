<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Request for Quotation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-10xl mx-auto sm:px-6 lg:px-8 space-y-12">
            
            {{-- SECCIÓN DE SOLICITUDES DE COTIZACIÓN (RFQ) --}}
            <div class="bg-[#FFF1DC] overflow-hidden shadow-xl rounded-[40px] p-6 sm:p-10">
                <h3 class="text-2xl font-bold text-[#2c3856]" style="font-family: 'Raleway', sans-serif;">Documentación RFQ</h3>
                
                <div class="mt-8">
                    @if($rfqSubfolders->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            @foreach($rfqSubfolders as $subfolder)
                                <a href="{{ route('folders.index', ['folder' => $subfolder->id]) }}" class="group bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-2">
                                    <div class="bg-[#DFE5F5] p-6 rounded-full transition-colors duration-300 group-hover:bg-[#ff9c00]">
                                        <svg class="w-24 h-24" viewBox="0 0 100 100">
    <image 
        href="{{ Storage::disk('s3')->url('RFQ1/' . $subfolder->name . '.png') }}" 
        width="100" 
        height="100" 
        preserveAspectRatio="xMidYMid meet"
        style="filter: brightness(0) saturate(100%) invert(15%) sepia(30%) saturate(800%) hue-rotate(190deg) brightness(90%) contrast(90%);"
    />
</svg>
                                    </div>
                                    <h4 class="mt-4 text-lg font-semibold text-[#2c3856] text-center">
                                        @if(strlen($subfolder->name) > 6)
                                            <div>{{ substr($subfolder->name, 0, 6) }}</div>
                                            <div>{{ substr($subfolder->name, 6) }}</div>
                                        @else
                                            {{ $subfolder->name }}
                                        @endif
                                    </h4>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-8">No tienes acceso a ninguna subcarpeta de RFQ por el momento.</p>
                    @endif
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>