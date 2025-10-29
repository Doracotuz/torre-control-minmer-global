<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->is_area_admin && $user->area?->name === 'Administración') {
            return $next($request);
        }

        abort(403, 'Acceso denegado. Se requieren privilegios de Super Administrador.');
    }
}