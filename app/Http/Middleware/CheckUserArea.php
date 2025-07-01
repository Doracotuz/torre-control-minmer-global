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
     */
    public function handle(Request $request, Closure $next, ...$areas): Response
    {
        // Si el usuario no está autenticado, redirige al login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Si el usuario no tiene un área asignada, o si su área no está en la lista de áreas permitidas
        // Nota: Usamos array_map para asegurar que los nombres de las áreas estén en minúsculas para una comparación consistente
        if (!$user->area || !in_array(strtolower($user->area->name), array_map('strtolower', $areas))) {
            // Puedes redirigir a una página de "Acceso Denegado" o al dashboard con un mensaje de error
            return redirect('/dashboard')->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}