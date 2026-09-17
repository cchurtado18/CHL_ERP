<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Bloquea usuarios del portal (rol cliente) fuera de rutas /portal.
 */
class EnsureStaffUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->esCliente()) {
            return redirect()->route('portal.home');
        }

        return $next($request);
    }
}
