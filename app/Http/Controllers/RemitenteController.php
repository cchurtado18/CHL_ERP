<?php

namespace App\Http\Controllers;

use App\Models\Remitente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RemitenteController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->input('busqueda');
        $query = Remitente::query();

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre_completo', 'like', "%{$busqueda}%")
                    ->orWhere('telefono', 'like', "%{$busqueda}%")
                    ->orWhere('correo', 'like', "%{$busqueda}%");
            });
        }

        $remitentes = $query->orderBy('nombre_completo')->paginate(12)->appends($request->all());

        return view('remitentes.index', compact('remitentes', 'busqueda'));
    }

    public function create()
    {
        return view('remitentes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono' => 'required|string|max:30',
            'correo' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:120',
            'estado' => 'nullable|string|max:120',
            'identificacion' => 'nullable|string|max:120',
        ]);

        $validated['created_by'] = Auth::id();
        Remitente::create($validated);

        return redirect()->route('remitentes.index')->with('success', 'Remitente creado correctamente.');
    }

    public function show($id)
    {
        $remitente = Remitente::findOrFail($id);

        return view('remitentes.show', compact('remitente'));
    }

    public function edit($id)
    {
        $remitente = Remitente::findOrFail($id);

        return view('remitentes.edit', compact('remitente'));
    }

    public function update(Request $request, $id)
    {
        $remitente = Remitente::findOrFail($id);

        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono' => 'required|string|max:30',
            'correo' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:120',
            'estado' => 'nullable|string|max:120',
            'identificacion' => 'nullable|string|max:120',
        ]);

        $validated['updated_by'] = Auth::id();
        $remitente->update($validated);

        return redirect()->route('remitentes.index')->with('success', 'Remitente actualizado correctamente.');
    }

    public function destroy($id)
    {
        $remitente = Remitente::findOrFail($id);
        $remitente->delete();

        return redirect()->route('remitentes.index')->with('success', 'Remitente eliminado correctamente.');
    }

    public function buscar(Request $request)
    {
        $q = trim((string) $request->input('q'));
        if ($q === '') {
            return response()->json([]);
        }

        $items = Remitente::query()
            ->where('nombre_completo', 'like', "%{$q}%")
            ->orWhere('telefono', 'like', "%{$q}%")
            ->orderBy('nombre_completo')
            ->limit(10)
            ->get(['id', 'nombre_completo', 'telefono', 'correo', 'direccion', 'ciudad', 'estado']);

        return response()->json($items);
    }
}
