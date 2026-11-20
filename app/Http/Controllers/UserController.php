<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

        $this->ensureCanAssignRole($data['rol']);

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
        $this->ensureCanManage($usuario);
        $modulos = config('permisos.modulos', []);

        return view('usuarios.edit', compact('usuario', 'modulos'));
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        $this->ensureCanManage($usuario);
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

        $this->ensureCanAssignRole($data['rol']);

        $actor = Auth::user();
        $nuevos = array_unique((array) ($data['permisos'] ?? []));
        $actuales = array_unique((array) ($usuario->permisos ?? []));
        sort($nuevos);
        sort($actuales);
        if ($actor->is($usuario) && ! $actor->esAdmin()
            && ($data['rol'] !== $usuario->rol || $nuevos !== $actuales)) {
            throw ValidationException::withMessages([
                'rol' => 'No puedes cambiar tu propio rol ni tus módulos de acceso.',
            ]);
        }

        // No dejar el sistema sin administradores activos.
        $dejaDeSerAdminActivo = $usuario->esAdmin() && $usuario->estado
            && ($data['rol'] !== 'admin' || ! $request->boolean('estado'));
        if ($dejaDeSerAdminActivo && $this->adminsActivos() <= 1) {
            throw ValidationException::withMessages([
                'rol' => 'Debe quedar al menos un administrador activo.',
            ]);
        }

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
        $this->ensureCanManage($usuario);

        if (Auth::user()->is($usuario)) {
            return back()->withErrors(['usuario' => 'No puedes eliminar tu propia cuenta.']);
        }

        if ($usuario->esAdmin() && $usuario->estado && $this->adminsActivos() <= 1) {
            return back()->withErrors(['usuario' => 'No se puede eliminar al último administrador activo.']);
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }

    /** Solo un admin puede otorgar el rol admin. */
    private function ensureCanAssignRole(string $rol): void
    {
        if ($rol === 'admin' && ! Auth::user()->esAdmin()) {
            throw ValidationException::withMessages([
                'rol' => 'Solo un administrador puede asignar el rol Administrador.',
            ]);
        }
    }

    /** Un usuario no admin no puede ver, editar ni borrar cuentas de administrador. */
    private function ensureCanManage(User $usuario): void
    {
        if ($usuario->esAdmin() && ! Auth::user()->esAdmin()) {
            abort(403, 'Solo un administrador puede gestionar cuentas de administrador.');
        }
    }

    private function adminsActivos(): int
    {
        return User::where('rol', 'admin')->where('estado', true)->count();
    }
}
