<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Un Super Admin DEBE ser admin de área Y pertenecer al área de Administración.
        if ($user && $user->is_area_admin && $user->area?->name === 'Administración') {
            return $next($request); // Si cumple ambas condiciones, permite el acceso.
        }

        // Si no cumple, deniega el acceso con un error 403 (Prohibido).
        abort(403, 'Acceso denegado. Se requieren privilegios de Super Administrador.');
    }
}