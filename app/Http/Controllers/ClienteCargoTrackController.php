<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Services\Tracking\TrackingSyncService;
use Illuminate\Http\Request;

class ClienteCargoTrackController extends Controller
{
    public function updateCredentials(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $data = $request->validate([
            'cargotrack_username' => ['nullable', 'string', 'max:150'],
            'cargotrack_password' => ['nullable', 'string', 'max:255'],
            'cargotrack_sync_enabled' => ['nullable', 'boolean'],
            'clear_password' => ['nullable', 'boolean'],
        ]);

        $cliente->cargotrack_username = trim((string) ($data['cargotrack_username'] ?? '')) ?: null;
        $cliente->cargotrack_sync_enabled = $request->boolean('cargotrack_sync_enabled');

        if (! empty($data['clear_password'])) {
            $cliente->cargotrack_password = null;
        } elseif (array_key_exists('cargotrack_password', $data)
            && $data['cargotrack_password'] !== null
            && $data['cargotrack_password'] !== '') {
            $cliente->cargotrack_password = $data['cargotrack_password'];
        }

        $cliente->save();

        return redirect()
            ->route('clientes.show', $cliente->id)
            ->with('success', 'Credenciales CargoTrack guardadas.');
    }

    public function sync(Request $request, $id, TrackingSyncService $sync)
    {
        $cliente = Cliente::findOrFail($id);

        if (! $cliente->cargotrack_username || ! $cliente->cargotrack_password) {
            return back()->withErrors(['cargotrack' => 'Guarda usuario y contraseña de CargoTrack antes de sincronizar.']);
        }

        if (! $cliente->cargotrack_sync_enabled) {
            return back()->withErrors(['cargotrack' => 'Activa la sincronización CargoTrack antes de ejecutarla.']);
        }

        $stats = $sync->syncCliente($cliente);

        $msg = sprintf(
            'Sync CargoTrack: %d creados, %d actualizados, %d omitidos, %d conflictos.',
            $stats['created'],
            $stats['updated'],
            $stats['skipped'],
            $stats['conflicts']
        );

        if ($stats['errors'] !== []) {
            return redirect()
                ->route('clientes.show', $cliente->id)
                ->with('success', $msg)
                ->withErrors(['cargotrack' => implode(' ', $stats['errors'])]);
        }

        return redirect()
            ->route('clientes.show', $cliente->id)
            ->with('success', $msg);
    }
}
