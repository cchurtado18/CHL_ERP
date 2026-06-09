<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware estricto: solo usuarios con rol "admin" pueden acceder.
 *
 * Se usa para secciones sensibles que afectan a toda la empresa
 * (costos por libra, gastos, reportes financieros).
 */
class AdminOnlyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->rol !== 'admin') {
            abort(403, 'Esta sección es solo para administradores.');
        }

        return $next($request);
    }
}
