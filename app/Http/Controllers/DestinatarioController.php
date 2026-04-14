<?php

namespace App\Http\Controllers;

use App\Models\Destinatario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DestinatarioController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->input('busqueda');
        $query = Destinatario::query();

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre_completo', 'like', "%{$busqueda}%")
                    ->orWhere('telefono_1', 'like', "%{$busqueda}%")
                    ->orWhere('telefono_2', 'like', "%{$busqueda}%")
                    ->orWhere('direccion', 'like', "%{$busqueda}%");
            });
        }

        $destinatarios = $query->orderBy('nombre_completo')->paginate(12)->appends($request->all());

        return view('destinatarios.index', compact('destinatarios', 'busqueda'));
    }

    public function create()
    {
        return view('destinatarios.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono_1' => 'required|string|max:30',
            'telefono_2' => 'nullable|string|max:30',
            'direccion' => 'required|string|max:255',
            'referencias' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:120',
            'departamento' => 'nullable|string|max:120',
            'cedula' => 'nullable|string|max:120',
            'autorizado_para_recibir' => 'nullable|boolean',
        ]);

        $validated['autorizado_para_recibir'] = $request->boolean('autorizado_para_recibir', true);
        $validated['created_by'] = Auth::id();
        Destinatario::create($validated);

        return redirect()->route('destinatarios.index')->with('success', 'Destinatario creado correctamente.');
    }

    public function show($id)
    {
        $destinatario = Destinatario::findOrFail($id);

        return view('destinatarios.show', compact('destinatario'));
    }

    public function edit($id)
    {
        $destinatario = Destinatario::findOrFail($id);

        return view('destinatarios.edit', compact('destinatario'));
    }

    public function update(Request $request, $id)
    {
        $destinatario = Destinatario::findOrFail($id);

        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono_1' => 'required|string|max:30',
            'telefono_2' => 'nullable|string|max:30',
            'direccion' => 'required|string|max:255',
            'referencias' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:120',
            'departamento' => 'nullable|string|max:120',
            'cedula' => 'nullable|string|max:120',
            'autorizado_para_recibir' => 'nullable|boolean',
        ]);

        $validated['autorizado_para_recibir'] = $request->boolean('autorizado_para_recibir', false);
        $validated['updated_by'] = Auth::id();
        $destinatario->update($validated);

        return redirect()->route('destinatarios.index')->with('success', 'Destinatario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $destinatario = Destinatario::findOrFail($id);
        $destinatario->delete();

        return redirect()->route('destinatarios.index')->with('success', 'Destinatario eliminado correctamente.');
    }

    public function buscar(Request $request)
    {
        $q = trim((string) $request->input('q'));
        if ($q === '') {
            return response()->json([]);
        }

        $items = Destinatario::query()
            ->where('nombre_completo', 'like', "%{$q}%")
            ->orWhere('telefono_1', 'like', "%{$q}%")
            ->orWhere('telefono_2', 'like', "%{$q}%")
            ->orderBy('nombre_completo')
            ->limit(10)
            ->get(['id', 'nombre_completo', 'telefono_1', 'telefono_2', 'direccion', 'ciudad', 'departamento']);

        return response()->json($items);
    }
}
