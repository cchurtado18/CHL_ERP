<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'admin.only' => \App\Http\Middleware\AdminOnlyMiddleware::class,
            'permiso' => \App\Http\Middleware\PermissionMiddleware::class,
            'portal.cliente' => \App\Http\Middleware\EnsureClientePortal::class,
            'staff' => \App\Http\Middleware\EnsureStaffUser::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\EnsureUserIsActive::class,
            \App\Http\Middleware\AuditChangesMiddleware::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            if (! $user) {
                return '/';
            }
            if ($user->esCliente()) {
                return '/portal';
            }
            if ($user->tienePermiso('dashboard')) {
                return '/';
            }
            if ($user->tienePermiso('inventario')) {
                return '/inventario';
            }
            if ($user->tienePermiso('contabilidad.cobros')) {
                return '/contabilidad/cobros/crear';
            }

            return '/';
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('facturacion:alertar-morosidad')->dailyAt('08:00');
        $schedule->command('leads:recordar-seguimientos')->hourly();
        $schedule->command('paquetes:sync-estados')->everyFifteenMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
