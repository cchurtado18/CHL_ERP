<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->to(Auth::user()->homePath());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        if (Auth::check()) {
            return redirect()->to(Auth::user()->homePath());
        }

        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required'],
        ]);

        // Solo cuentas activas pueden autenticarse.
        if (Auth::attempt($credentials + ['estado' => true], $request->filled('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            // No usar intended('/') porque manda al dashboard y provoca 403
            // si el usuario no tiene permiso de dashboard.
            return redirect()->to($user->homePath());
        }

        // Solo avisar de cuenta desactivada si la contraseña es correcta,
        // para no revelar qué correos existen.
        if (Auth::validate($credentials + ['estado' => false])) {
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

    public function sinAcceso()
    {
        $user = Auth::user();
        if ($user && $user->homePath() !== '/sin-acceso') {
            return redirect()->to($user->homePath());
        }

        return view('auth.sin-acceso');
    }
}
