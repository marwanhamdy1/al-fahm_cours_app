<?php

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\HandelAuthenticate;
use App\Http\Middleware\ModeratorMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
          $middleware->alias([
            'handelAuth' => HandelAuthenticate::class ,// for exeption jwt and token validate
             'admin' => AdminMiddleware::class, // for exeption jwt and token validate
            'super_admin' => SuperAdminMiddleware::class, // for exeption jwt and token validate
            'moderator' => ModeratorMiddleware::class // for exeption jwt and token validate
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();