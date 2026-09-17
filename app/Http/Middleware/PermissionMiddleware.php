<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Verifica que el usuario autenticado tenga al menos uno de los módulos indicados.
 * Uso: middleware('permiso:clientes') o middleware('permiso:contabilidad,contabilidad.cobros')
 *
 * El rol admin siempre pasa (si está activo).
 */
class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string ...$modulos)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->estado) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Tu cuenta está desactivada. Contacta al administrador.']);
        }

        if ($user->esCliente()) {
            return redirect()->route('portal.home');
        }

        if ($user->esAdmin()) {
            return $next($request);
        }

        foreach ($modulos as $modulo) {
            if ($user->tienePermiso($modulo)) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permiso para acceder a este módulo.');
    }
}
