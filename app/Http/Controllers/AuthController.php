<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->to($this->homeFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        if (Auth::check()) {
            return redirect()->to($this->homeFor(Auth::user()));
        }

        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required'],
        ]);

        // Solo cuentas activas pueden autenticarse.
        $credentials['estado'] = true;

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            return redirect()->intended($this->homeFor($user));
        }

        // Si el usuario existe pero está inactivo, mensaje explícito (sin revelar de más).
        $inactive = \App\Models\User::query()
            ->where('email', $credentials['email'])
            ->where('estado', false)
            ->exists();

        if ($inactive) {
            return back()->withErrors([
                'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function homeFor($user): string
    {
        if (! $user) {
            return route('login');
        }

        if ($user->esCliente()) {
            return route('portal.home');
        }

        if ($user->tienePermiso('dashboard')) {
            return '/';
        }
        if ($user->tienePermiso('contabilidad')) {
            return '/contabilidad';
        }
        if ($user->tienePermiso('contabilidad.cobros')) {
            return route('contabilidad.cobros.create');
        }
        if ($user->tienePermiso('inventario')) {
            return '/inventario';
        }
        if ($user->tienePermiso('facturacion')) {
            return '/facturacion';
        }
        if ($user->tienePermiso('leads')) {
            return '/leads';
        }
        if ($user->tienePermiso('notificaciones')) {
            return '/notificaciones';
        }

        return route('login');
    }
}
