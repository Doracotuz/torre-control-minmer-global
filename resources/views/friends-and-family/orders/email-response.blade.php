@extends('layouts.guest-rutas')

@section('content')
<div class="flex items-center justify-center min-h-[calc(100vh-6rem)] px-4 pb-12">
    <div class="bg-white p-10 rounded-3xl shadow-[0_20px_50px_rgba(8,_112,_184,_0.07)] border border-slate-100 max-w-md w-full text-center relative overflow-hidden ring-1 ring-slate-900/5">
        
        @if($status === 'success')
            <div class="absolute top-0 left-0 w-full h-1.5 bg-green-500"></div>
            <div class="mb-8 mt-2">
                <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-50 ring-1 ring-green-100 shadow-sm mb-6">
                    <svg class="h-10 w-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-black text-[#2c3856] mb-3 tracking-tight">¡Acción Exitosa!</h1>
                <p class="text-slate-500 text-sm leading-relaxed font-medium">{{ $message }}</p>
            </div>
        @else
            <div class="absolute top-0 left-0 w-full h-1.5 bg-red-500"></div>
            <div class="mb-8 mt-2">
                <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-red-50 ring-1 ring-red-100 shadow-sm mb-6">
                    <svg class="h-10 w-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-black text-[#2c3856] mb-3 tracking-tight">No se pudo procesar</h1>
                <p class="text-slate-500 text-sm leading-relaxed font-medium">{{ $message }}</p>
            </div>
        @endif

        <div class="pt-8 border-t border-slate-100">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Ya puedes cerrar esta ventana</p>
        </div>
    </div>
</div>
@endsection