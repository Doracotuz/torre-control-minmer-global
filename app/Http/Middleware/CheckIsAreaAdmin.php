<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckIsAreaAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica si el usuario está autenticado y si la propiedad 'is_area_admin' es verdadera.
        if (Auth::check() && Auth::user()->is_area_admin) {
            // Si cumple la condición, permite que la petición continúe.
            return $next($request);
        }

        // Si no es un admin de área, detiene la petición y muestra un error 403 (Prohibido).
        abort(403, 'Acceso no autorizado.');
    }
}
