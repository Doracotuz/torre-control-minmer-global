<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckFriendsAndFamilyAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        $isSuperAdmin = $user && $user->isSuperAdmin();

        $isVentas = $user && $user->area?->name === 'Ventas';

        if ($isSuperAdmin || $isVentas) {
            return $next($request);
        }

        abort(403, 'Acceso denegado. No tienes permisos para acceder a este módulo.');
    }
}