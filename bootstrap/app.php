<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.area' => \App\Http\Middleware\CheckUserArea::class,
            'super.admin' => \App\Http\Middleware\CheckSuperAdmin::class,
            'not_client' => \App\Http\Middleware\CheckIfNotClient::class,

        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
