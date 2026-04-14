<?php

use App\Http\Controllers\FacturacionController;
use Illuminate\Support\Facades\Route;

/*
 * Misma sesión web que el panel (facturación usa jQuery GET a /api/clientes/{id}).
 * Solo admin y agente, alineado con las rutas web de facturación.
 */
Route::middleware(['web', 'auth', 'role:admin,agente'])->group(function () {
    Route::get('/facturacion/cliente-detalle/{clienteId}', [FacturacionController::class, 'clienteDetalle']);
    Route::get('/clientes/{id}', [FacturacionController::class, 'clienteDetalle']);
});