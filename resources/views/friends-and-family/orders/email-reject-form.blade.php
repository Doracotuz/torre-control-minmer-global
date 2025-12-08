@extends('layouts.guest-rutas')

@section('content')
<div class="flex items-center justify-center min-h-[calc(100vh-6rem)] px-4 pb-12">
    <div class="bg-white p-8 rounded-2xl shadow-[0_20px_50px_rgba(8,_112,_184,_0.07)] border border-slate-100 max-w-md w-full relative overflow-hidden ring-1 ring-slate-900/5">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-[#2c3856]"></div>

        <div class="text-center mb-8 mt-2">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-4 ring-1 ring-red-100 shadow-sm">
                <svg class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <h2 class="text-2xl font-black text-[#2c3856] tracking-tight">Confirmar Rechazo</h2>
            <p class="text-sm text-slate-500 mt-2 font-medium">Estás a punto de rechazar el pedido <span class="text-[#2c3856] font-bold font-mono-tech">#{{ $folio }}</span>.</p>
        </div>

        <form action="{{ URL::signedRoute('ff.email.reject.submit', ['folio' => $folio, 'adminId' => $adminId]) }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label for="reason" class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Motivo del rechazo</label>
                <textarea id="reason" name="reason" rows="4" required
                    class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-2 focus:ring-[#2c3856] focus:border-[#2c3856] p-4 placeholder-slate-400 resize-none transition-all outline-none font-medium"
                    placeholder="Indica la razón por la cual no se autoriza..."></textarea>
            </div>

            <div class="bg-orange-50 border border-orange-100 rounded-xl p-4 mb-8 flex items-start gap-3">
                <svg class="h-5 w-5 text-[#ff9c00] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-xs text-orange-800 leading-relaxed font-medium">
                    <strong>Atención:</strong> Esta acción es irreversible. El stock reservado se liberará y devolverá al inventario automáticamente.
                </p>
            </div>

            <button type="submit" class="w-full flex justify-center items-center py-4 px-4 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-red-500/30 transition-all transform active:scale-[0.98]">
                Confirmar Rechazo
            </button>
        </form>
    </div>
</div>
@endsection