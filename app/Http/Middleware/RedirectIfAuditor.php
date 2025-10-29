<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuditor
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->area?->name === 'Auditoría') {
            
            if (!$request->routeIs('logout') && !$request->routeIs('audit.*')) {
                return redirect()->route('audit.index');
            }
        }
        
        return $next($request);
    }
}
