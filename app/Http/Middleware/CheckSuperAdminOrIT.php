<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdminOrIT
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        $privilegedAreas = ['Administración', 'Innovación y Desarrollo'];

        if ($user && $user->is_area_admin && in_array($user->area?->name, $privilegedAreas)) {
            return $next($request);
        }

        abort(403, 'Acceso denegado. Se requieren privilegios de administrador elevado.');
    }
}