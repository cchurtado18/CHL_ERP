<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientePortalAccesoController extends Controller
{
    public function store(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        if (! $cliente->correo) {
            return back()->withErrors(['portal' => 'El cliente necesita un correo antes de crear el acceso al portal.']);
        }

        if ($cliente->usuarioPortal) {
            return back()->withErrors(['portal' => 'Este cliente ya tiene acceso al portal. Puedes resetear la contraseña o reactivar la cuenta.']);
        }

        $data = $request->validate([
            'password' => ['nullable', 'string', 'min:8'],
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email'),
            ],
        ]);

        $email = $data['email'] ?? $cliente->correo;

        $emailTaken = User::where('email', $email)->exists();
        if ($emailTaken) {
            return back()->withErrors(['portal' => 'El correo ya está en uso por otro usuario del sistema.']);
        }

        $plainPassword = $data['password'] ?: Str::password(10);

        $user = User::create([
            'nombre' => $cliente->nombre_completo,
            'email' => $email,
            'password' => $plainPassword,
            'rol' => 'cliente',
            'permisos' => null,
            'cliente_id' => $cliente->id,
            'estado' => true,
        ]);

        return redirect()
            ->route('clientes.show', $cliente->id)
            ->with('portal_credenciales', [
                'email' => $user->email,
                'password' => $plainPassword,
            ])
            ->with('success', 'Acceso al portal creado. Anota la contraseña: solo se muestra una vez.');
    }

    public function resetPassword(Request $request, $id)
    {
        $cliente = Cliente::with('usuarioPortal')->findOrFail($id);
        $user = $cliente->usuarioPortal;

        if (! $user) {
            return back()->withErrors(['portal' => 'Este cliente aún no tiene acceso al portal.']);
        }

        $data = $request->validate([
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $plainPassword = $data['password'] ?: Str::password(10);
        $user->password = $plainPassword;
        $user->estado = true;
        $user->save();

        return redirect()
            ->route('clientes.show', $cliente->id)
            ->with('portal_credenciales', [
                'email' => $user->email,
                'password' => $plainPassword,
            ])
            ->with('success', 'Contraseña del portal restablecida. Anótala: solo se muestra una vez.');
    }

    public function toggle($id)
    {
        $cliente = Cliente::with('usuarioPortal')->findOrFail($id);
        $user = $cliente->usuarioPortal;

        if (! $user) {
            return back()->withErrors(['portal' => 'Este cliente aún no tiene acceso al portal.']);
        }

        $user->estado = ! $user->estado;
        $user->save();

        $msg = $user->estado
            ? 'Acceso al portal reactivado.'
            : 'Acceso al portal desactivado.';

        return redirect()
            ->route('clientes.show', $cliente->id)
            ->with('success', $msg);
    }
}
