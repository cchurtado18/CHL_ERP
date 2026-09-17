<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Restringe el acceso a uno o más roles explícitos.
 * Uso: middleware('role:admin,agente')
 *
 * Nota: el control fino por módulo debe hacerse con middleware('permiso:...').
 * Este middleware queda para rutas legacy / API que aún usan roles.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
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

        // Acepta "role:admin,agente" como un solo argumento o varios.
        $allowed = [];
        foreach ($roles as $role) {
            foreach (explode(',', (string) $role) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $allowed[] = $part;
                }
            }
        }

        if ($allowed === []) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        if (! in_array($user->rol, $allowed, true)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
