<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PortalCuentaController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $cliente = $user->cliente;

        return view('portal.cuenta', compact('user', 'cliente'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_actual' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->password_actual, $user->password)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.']);
        }

        $user->password = $request->password;
        $user->save();

        return back()->with('success', 'Contraseña actualizada correctamente.');
    }
}
