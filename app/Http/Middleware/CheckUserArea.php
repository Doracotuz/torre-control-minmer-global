<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserArea
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Puede ser nombres de áreas o 'area_admin'
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $expectedRoles = array_map('strtolower', $roles);

        if (in_array('area_admin', $expectedRoles)) {
            if (!$user->is_area_admin) {
                return redirect('/dashboard')->with('error', 'No tienes permisos de administrador de área para acceder a esta sección.');
            }
            if (count($expectedRoles) === 1 && $expectedRoles[0] === 'area_admin') {
                return $next($request);
            }
        }

        if (!$user->area || !in_array(strtolower($user->area->name), $expectedRoles)) {
            return redirect('/dashboard')->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}