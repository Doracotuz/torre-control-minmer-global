<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserArea
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Puede ser nombres de áreas o 'area_admin'
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Convertir los roles esperados a minúsculas para una comparación consistente
        $expectedRoles = array_map('strtolower', $roles);

        // Si 'area_admin' es uno de los roles esperados
        if (in_array('area_admin', $expectedRoles)) {
            // Si el usuario no es un administrador de área, denegar acceso
            if (!$user->is_area_admin) {
                return redirect('/dashboard')->with('error', 'No tienes permisos de administrador de área para acceder a esta sección.');
            }
            // Si es un administrador de área, continuar con la verificación de área si también se especificó
            // O si solo se pidió 'area_admin', permitir el acceso
            if (count($expectedRoles) === 1 && $expectedRoles[0] === 'area_admin') {
                return $next($request);
            }
        }

        // Si el usuario no tiene un área asignada O si su área no está en la lista de áreas permitidas
        // (Esto se ejecuta si 'area_admin' no fue el único rol o no fue solicitado)
        if (!$user->area || !in_array(strtolower($user->area->name), $expectedRoles)) {
            return redirect('/dashboard')->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}