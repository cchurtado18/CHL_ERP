<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::all();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $modulos = config('permisos.modulos', []);

        return view('usuarios.create', compact('modulos'));
    }

    public function store(Request $request)
    {
        $modulosValidos = array_keys(config('permisos.modulos', []));

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'rol' => 'required|in:admin,agente,auditor,basico',
            'estado' => 'nullable|boolean',
            'permisos' => 'nullable|array',
            'permisos.*' => ['string', Rule::in($modulosValidos)],
        ]);

        $permisos = $data['rol'] === 'admin'
            ? null
            : array_values(array_unique($data['permisos'] ?? []));

        // Si no marcaron módulos, aplicar defaults del rol para evitar usuarios sin acceso.
        if ($data['rol'] !== 'admin' && $permisos === []) {
            $permisos = array_values(config('permisos.defaults_por_rol.'.$data['rol'], []));
        }

        if ($data['rol'] !== 'admin' && $permisos === []) {
            return back()
                ->withErrors(['permisos' => 'Selecciona al menos un módulo de acceso para este usuario.'])
                ->withInput();
        }

        User::create([
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'password' => $data['password'],
            'rol' => $data['rol'],
            'permisos' => $permisos,
            'estado' => $request->boolean('estado'),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        $modulos = config('permisos.modulos', []);

        return view('usuarios.edit', compact('usuario', 'modulos'));
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        $modulosValidos = array_keys(config('permisos.modulos', []));

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'rol' => 'required|in:admin,agente,auditor,basico',
            'estado' => 'nullable|boolean',
            'permisos' => 'nullable|array',
            'permisos.*' => ['string', Rule::in($modulosValidos)],
            'password' => 'nullable|min:6',
        ]);

        $permisos = $data['rol'] === 'admin'
            ? null
            : array_values(array_unique($data['permisos'] ?? []));

        if ($data['rol'] !== 'admin' && $permisos === []) {
            $permisos = array_values(config('permisos.defaults_por_rol.'.$data['rol'], []));
        }

        if ($data['rol'] !== 'admin' && $permisos === []) {
            return back()
                ->withErrors(['permisos' => 'Selecciona al menos un módulo de acceso para este usuario.'])
                ->withInput();
        }

        $payload = [
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'rol' => $data['rol'],
            'permisos' => $permisos,
            'estado' => $request->boolean('estado'),
        ];

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $usuario->update($payload);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
