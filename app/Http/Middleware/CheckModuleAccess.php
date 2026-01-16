<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        if ($user->isSuperAdmin()) {
             return $next($request);
        }

        if (! $user->hasModuleAccess($moduleKey)) {
            abort(403, 'No tienes permisos para acceder al módulo: ' . $moduleKey);
        }

        return $next($request);
    }
}