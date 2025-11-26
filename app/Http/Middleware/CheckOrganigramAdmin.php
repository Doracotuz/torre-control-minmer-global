<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganigramAdmin
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_area_admin && (Auth::user()->area->name === 'Administración' || Auth::user()->area->name === 'Recursos Humanos' || Auth::user()->area->name === 'Innovación y Desarrollo' || Auth::user()->area->name === 'Comercial')) {
            return $next($request);
        }

        abort(403, 'Acceso denegado. No tienes permisos para acceder a esta función.');
    }
}