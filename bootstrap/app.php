<?php

use App\Http\Middleware\AdminSecurityHeaders;
use App\Http\Middleware\RequireAdminAuth;
use App\Http\Middleware\RequireAdminEmailWorkflowAccess;
use App\Http\Middleware\RequirePasswordRotation;
use App\Http\Middleware\RequireSimperRole;
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
        $middleware->validateCsrfTokens(except: [
            'admin/sync/gform',
        ]);

        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'legacy.admin.headers' => AdminSecurityHeaders::class,
            'legacy.admin.auth' => RequireAdminAuth::class,
            'legacy.admin.email-workflow' => RequireAdminEmailWorkflowAccess::class,
            'legacy.password.rotation' => RequirePasswordRotation::class,
            'simper.role' => RequireSimperRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
