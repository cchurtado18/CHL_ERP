<?php

use App\Http\Controllers\FacturacionController;
use Illuminate\Support\Facades\Route;

/*
 * Misma sesión web que el panel (facturación usa jQuery GET a /api/clientes/{id}).
 * Requiere autenticación + permiso de facturación (o admin).
 */
Route::middleware(['web', 'auth', 'permiso:facturacion'])->group(function () {
    Route::get('/facturacion/cliente-detalle/{clienteId}', [FacturacionController::class, 'clienteDetalle']);
    Route::get('/clientes/{id}', [FacturacionController::class, 'clienteDetalle']);
});
