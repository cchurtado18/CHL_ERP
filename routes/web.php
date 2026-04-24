<?php

use App\Http\Controllers\AgendaEventoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContabilidadAsientoController;
use App\Http\Controllers\ContabilidadCobroController;
use App\Http\Controllers\ContabilidadCuentaController;
use App\Http\Controllers\ContabilidadCxcController;
use App\Http\Controllers\ContabilidadDashboardController;
use App\Http\Controllers\ContabilidadPeriodoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DestinatarioController;
use App\Http\Controllers\EncomiendaController;
use App\Http\Controllers\EncomiendaEstadoController;
use App\Http\Controllers\FacturacionController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LogInventarioController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\RemitenteController;
use App\Http\Controllers\TarifaClienteController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\UserController;
use App\Models\Cliente;
use App\Models\Inventario;
use Illuminate\Support\Facades\Route;

// Login
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Todas las rutas accesibles sin autenticación ni roles
// RUTA PRINCIPAL ÚNICA PARA DASHBOARD
Route::middleware(['auth', 'role:admin'])->get('/', function () {
    $totalClientes = \App\Models\Cliente::count();
    $totalUsuarios = \App\Models\User::count();
    $totalFacturas = \App\Models\Facturacion::count();
    $totalPaquetes = \App\Models\Inventario::count();
    $ultimosPaquetes = \App\Models\Inventario::with(['cliente', 'servicio'])
        ->latest('fecha_ingreso')
        ->take(5)
        ->get();

    // Clientes para el select
    $clientes = \App\Models\Cliente::orderBy('nombre_completo')->get();
    // Datos de paquetes e ingresos por cliente en el mes actual
    $clientesData = [];
    $inicioMes = \Carbon\Carbon::now()->startOfMonth()->toDateString();
    $finMes = \Carbon\Carbon::now()->endOfMonth()->toDateString();

    // Función para normalizar el tipo de servicio (minúsculas y sin acentos)
    function normalizarServicio($tipo)
    {
        $tipo = strtolower($tipo ?? '');
        $tipo = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $tipo);

        return $tipo;
    }

    foreach ($clientes as $cliente) {
        $paquetes = \App\Models\Inventario::with('servicio')
            ->where('cliente_id', $cliente->id)
            ->get();

        $paquetesAereo = $paquetes->filter(function ($p) {
            return normalizarServicio($p->servicio->tipo_servicio ?? '') === 'aereo';
        });
        $paquetesMaritimo = $paquetes->filter(function ($p) {
            return normalizarServicio($p->servicio->tipo_servicio ?? '') === 'maritimo';
        });
        $paquetesPieCubico = $paquetes->filter(function ($p) {
            return normalizarServicio($p->servicio->tipo_servicio ?? '') === 'pie_cubico';
        });

        $clientesData[$cliente->id] = [
            'paquetes_aereo' => $paquetesAereo->count(),
            'paquetes_maritimo' => $paquetesMaritimo->count(),
            'paquetes_pie_cubico' => $paquetesPieCubico->count(),
            'ingresos_aereo' => $paquetesAereo->sum('monto_calculado'),
            'ingresos_maritimo' => $paquetesMaritimo->sum('monto_calculado'),
            'ingresos_pie_cubico' => $paquetesPieCubico->sum('monto_calculado'),
            'libras_aereo' => $paquetesAereo->sum('peso_lb'),
            'libras_maritimo' => $paquetesMaritimo->sum('peso_lb'),
            'libras_pie_cubico' => $paquetesPieCubico->sum('peso_lb'),
        ];
    }
    // Gráfico de pastel: paquetes del mes agrupados por tipo de servicio
    $serviciosMes = \App\Models\Inventario::with('servicio')
        ->whereBetween('fecha_ingreso', [$inicioMes, $finMes])
        ->get();
    $serviciosPieData = $serviciosMes->groupBy(function ($item) {
        return $item->servicio->tipo_servicio ?? 'Sin tipo';
    })->map(function ($group) {
        return $group->count();
    });

    return view('welcome', compact('totalClientes', 'totalUsuarios', 'totalFacturas', 'totalPaquetes', 'ultimosPaquetes', 'clientes', 'clientesData', 'serviciosPieData'));
})->name('welcome');

// Rutas para usuarios
Route::prefix('usuarios')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('usuarios.index');
    Route::get('/crear', [UserController::class, 'create'])->name('usuarios.create');
    Route::post('/', [UserController::class, 'store'])->name('usuarios.store');
    Route::get('/{id}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
    Route::put('/{id}', [UserController::class, 'update'])->name('usuarios.update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');
});

// Rutas para clientes
Route::prefix('clientes')->group(function () {
    Route::get('/', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/crear', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/', [ClienteController::class, 'store'])->name('clientes.store');
    Route::get('/{id}/editar', [ClienteController::class, 'edit'])->name('clientes.edit');
    Route::put('/{id}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::delete('/{id}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
    Route::get('/{id}', [ClienteController::class, 'show'])->name('clientes.show');
});

// Rutas para facturación
Route::middleware(['auth', 'role:admin,agente'])->prefix('facturacion')->group(function () {
    Route::get('/', [FacturacionController::class, 'index'])->name('facturacion.index');
    Route::get('/crear', [FacturacionController::class, 'create'])->name('facturacion.create');
    Route::post('/', [FacturacionController::class, 'store'])->name('facturacion.store');
    Route::get('/encomiendas-disponibles', [FacturacionController::class, 'encomiendasDisponibles'])->name('facturacion.encomiendas-disponibles');
    Route::get('/encomienda-items/{id}', [FacturacionController::class, 'encomiendaItems'])->name('facturacion.encomienda-items');
    Route::get('/{factura}', [FacturacionController::class, 'show'])->name('facturacion.show');
    Route::get('/{id}/editar', [FacturacionController::class, 'edit'])->name('facturacion.edit');
    Route::put('/{id}', [FacturacionController::class, 'update'])->name('facturacion.update');
    Route::delete('/{id}', [FacturacionController::class, 'destroy'])->name('facturacion.destroy');
    Route::get('/{id}/pdf', [FacturacionController::class, 'descargarPDF'])->name('facturacion.pdf');
    Route::get('/{id}/preview', [FacturacionController::class, 'previsualizarPDF'])->name('facturacion.preview');
    Route::post('/preview-live', [FacturacionController::class, 'previewLivePDF'])->name('facturacion.preview-live');
    Route::get('/paquetes-por-cliente/{cliente}', [FacturacionController::class, 'paquetesPorCliente'])->name('facturacion.paquetes-por-cliente');
    Route::post('/{id}/cambiar-estado', [FacturacionController::class, 'cambiarEstado'])->name('facturacion.cambiar-estado');
    Route::post('/{id}/contabilidad-marcar-verificada', [FacturacionController::class, 'marcarContabilidadVerificada'])->name('facturacion.contabilidad-marcar-verificada');
    Route::post('/{id}/anular', [FacturacionController::class, 'anular'])->name('facturacion.anular');
    Route::post('/{id}/enviar-correo', [FacturacionController::class, 'enviarCorreo'])->name('facturacion.enviar-correo');
    Route::post('/validar-numero-acta', [FacturacionController::class, 'validarNumeroActa'])->name('facturacion.validar-numero-acta');
});

// Rutas para encomiendas familiares
Route::middleware(['auth', 'role:admin,agente,basico'])->prefix('encomiendas')->group(function () {
    Route::get('/', [EncomiendaController::class, 'index'])->name('encomiendas.index');
    Route::get('/crear', [EncomiendaController::class, 'create'])->name('encomiendas.create');
    Route::post('/', [EncomiendaController::class, 'store'])->name('encomiendas.store');
    Route::get('/{encomienda}/items/{item}/foto/{index}', [EncomiendaController::class, 'itemFoto'])
        ->whereNumber('index')
        ->name('encomiendas.item-foto');
    Route::get('/{id}', [EncomiendaController::class, 'show'])->name('encomiendas.show');
    Route::get('/{id}/editar', [EncomiendaController::class, 'edit'])->name('encomiendas.edit');
    Route::put('/{id}', [EncomiendaController::class, 'update'])->name('encomiendas.update');
    Route::delete('/{id}', [EncomiendaController::class, 'destroy'])->name('encomiendas.destroy');
    Route::post('/{id}/estados', [EncomiendaEstadoController::class, 'store'])->name('encomiendas.estados.store');
});

// Rutas para remitentes
Route::middleware(['auth', 'role:admin,agente,basico'])->prefix('remitentes')->group(function () {
    Route::get('/', [RemitenteController::class, 'index'])->name('remitentes.index');
    Route::get('/crear', [RemitenteController::class, 'create'])->name('remitentes.create');
    Route::post('/', [RemitenteController::class, 'store'])->name('remitentes.store');
    Route::get('/buscar', [RemitenteController::class, 'buscar'])->name('remitentes.buscar');
    Route::get('/{id}', [RemitenteController::class, 'show'])->name('remitentes.show');
    Route::get('/{id}/editar', [RemitenteController::class, 'edit'])->name('remitentes.edit');
    Route::put('/{id}', [RemitenteController::class, 'update'])->name('remitentes.update');
    Route::delete('/{id}', [RemitenteController::class, 'destroy'])->name('remitentes.destroy');
});

// Rutas para destinatarios
Route::middleware(['auth', 'role:admin,agente,basico'])->prefix('destinatarios')->group(function () {
    Route::get('/', [DestinatarioController::class, 'index'])->name('destinatarios.index');
    Route::get('/crear', [DestinatarioController::class, 'create'])->name('destinatarios.create');
    Route::post('/', [DestinatarioController::class, 'store'])->name('destinatarios.store');
    Route::get('/buscar', [DestinatarioController::class, 'buscar'])->name('destinatarios.buscar');
    Route::get('/{id}', [DestinatarioController::class, 'show'])->name('destinatarios.show');
    Route::get('/{id}/editar', [DestinatarioController::class, 'edit'])->name('destinatarios.edit');
    Route::put('/{id}', [DestinatarioController::class, 'update'])->name('destinatarios.update');
    Route::delete('/{id}', [DestinatarioController::class, 'destroy'])->name('destinatarios.destroy');
});

// Rutas para inventario
Route::middleware(['auth', 'role:admin,agente,basico'])->prefix('inventario')->group(function () {
    Route::get('/', [InventarioController::class, 'index'])->name('inventario.index');
    Route::get('/crear', [InventarioController::class, 'create'])->name('inventario.create');
    Route::post('/', [InventarioController::class, 'store'])->name('inventario.store');
    Route::get('/export-excel', [InventarioController::class, 'exportExcel'])->name('inventario.export-excel');
    Route::get('/{id}/editar', [InventarioController::class, 'edit'])->name('inventario.edit');
    Route::put('/{id}', [InventarioController::class, 'update'])->name('inventario.update');
    Route::get('/{id}', [InventarioController::class, 'show'])->name('inventario.show');
    Route::post('obtener-tarifa', [InventarioController::class, 'obtenerTarifa'])->name('inventario.obtener-tarifa');
    Route::delete('/{id}', [InventarioController::class, 'destroy'])->name('inventario.destroy');
    Route::post('inventario/validar-numero-guia', [App\Http\Controllers\InventarioController::class, 'validarNumeroGuia'])->name('inventario.validar-numero-guia');
});

// Rutas para notificaciones
Route::middleware(['auth', 'role:admin,agente,basico'])->prefix('notificaciones')->group(function () {
    Route::get('/', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::get('/crear', [NotificacionController::class, 'create'])->name('notificaciones.create');
    Route::post('/', [NotificacionController::class, 'store'])->name('notificaciones.store');
    Route::get('/{notificacion}', [NotificacionController::class, 'show'])->name('notificaciones.show');
    Route::get('/{notificacion}/editar', [NotificacionController::class, 'edit'])->name('notificaciones.edit');
    Route::put('/{notificacion}', [NotificacionController::class, 'update'])->name('notificaciones.update');
    Route::delete('/{notificacion}', [NotificacionController::class, 'destroy'])->name('notificaciones.destroy');
    Route::patch('/{notificacion}/marcar-leida', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.marcar-leida');
    Route::get('/no-leidas', [NotificacionController::class, 'noLeidas'])->name('notificaciones.no-leidas');
    Route::patch('/marcar-todas-leidas', [NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.marcar-todas-leidas');
});

// Rutas para leads (CRM comercial)
Route::middleware(['auth', 'role:admin,agente,basico'])->prefix('leads')->group(function () {
    Route::get('/', [LeadController::class, 'calendar'])->name('leads.calendar');
    Route::get('/lista', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/crear', [LeadController::class, 'create'])->name('leads.create');
    Route::post('/', [LeadController::class, 'store'])->name('leads.store');
    Route::post('/agenda-eventos', [AgendaEventoController::class, 'store'])->name('leads.agenda-eventos.store');
    Route::delete('/agenda-eventos/{agendaEvento}', [AgendaEventoController::class, 'destroy'])->name('leads.agenda-eventos.destroy');
    Route::get('/{id}', [LeadController::class, 'show'])->name('leads.show');
    Route::get('/{id}/editar', [LeadController::class, 'edit'])->name('leads.edit');
    Route::put('/{id}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/{id}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('/{id}/interacciones', [LeadController::class, 'storeInteraccion'])->name('leads.interacciones.store');
    Route::patch('/{id}/etapa', [LeadController::class, 'cambiarEtapa'])->name('leads.cambiar-etapa');
    Route::patch('/{id}/contactado-rapido', [LeadController::class, 'marcarContactadoRapido'])->name('leads.contactado-rapido');
});

// Rutas para contabilidad
Route::middleware(['auth', 'role:admin'])->prefix('contabilidad')->group(function () {
    Route::get('/', [ContabilidadDashboardController::class, 'index'])->name('contabilidad.dashboard');

    Route::get('/cuentas', [ContabilidadCuentaController::class, 'index'])->name('contabilidad.cuentas.index');
    Route::get('/cuentas/crear', [ContabilidadCuentaController::class, 'create'])->name('contabilidad.cuentas.create');
    Route::post('/cuentas', [ContabilidadCuentaController::class, 'store'])->name('contabilidad.cuentas.store');

    Route::get('/asientos', [ContabilidadAsientoController::class, 'index'])->name('contabilidad.asientos.index');
    Route::get('/asientos/crear', [ContabilidadAsientoController::class, 'create'])->name('contabilidad.asientos.create');
    Route::get('/asientos/{id}', [ContabilidadAsientoController::class, 'show'])->name('contabilidad.asientos.show');
    Route::post('/asientos', [ContabilidadAsientoController::class, 'store'])->name('contabilidad.asientos.store');

    Route::get('/cxc', [ContabilidadCxcController::class, 'index'])->name('contabilidad.cxc.index');
    Route::get('/cxc/{facturaId}', [ContabilidadCxcController::class, 'show'])->name('contabilidad.cxc.show');

    Route::get('/cobros', [ContabilidadCobroController::class, 'index'])->name('contabilidad.cobros.index');
    Route::get('/cobros/crear', [ContabilidadCobroController::class, 'create'])->name('contabilidad.cobros.create');
    Route::get('/cobros/{id}', [ContabilidadCobroController::class, 'show'])->name('contabilidad.cobros.show');
    Route::post('/cobros', [ContabilidadCobroController::class, 'store'])->name('contabilidad.cobros.store');

    Route::get('/periodos', [ContabilidadPeriodoController::class, 'index'])->name('contabilidad.periodos.index');
    Route::patch('/periodos/{id}/toggle', [ContabilidadPeriodoController::class, 'toggleEstado'])->name('contabilidad.periodos.toggle');
});

// Rutas para tracking
Route::prefix('tracking')->group(function () {
    Route::get('/', [TrackingController::class, 'index'])->name('tracking.index');
    Route::get('/dashboard', [TrackingController::class, 'dashboard'])->name('tracking.dashboard');
    Route::get('/crear', [TrackingController::class, 'create'])->name('tracking.create');
    Route::post('/', [TrackingController::class, 'store'])->name('tracking.store');
    Route::get('/{tracking}', [TrackingController::class, 'show'])->name('tracking.show');
    Route::get('/{tracking}/editar', [TrackingController::class, 'edit'])->name('tracking.edit');
    Route::put('/{tracking}', [TrackingController::class, 'update'])->name('tracking.update');
    Route::delete('/{tracking}', [TrackingController::class, 'destroy'])->name('tracking.destroy');
    Route::post('/{tracking}/actualizar-estado', [TrackingController::class, 'actualizarEstado'])->name('tracking.actualizar-estado');
    Route::get('/buscar', [TrackingController::class, 'buscarPorCodigo'])->name('tracking.buscar');
    Route::get('/proximos-vencer', [TrackingController::class, 'proximosVencer'])->name('tracking.proximos-vencer');
    Route::get('/verificar-recordatorios', [TrackingController::class, 'verificarRecordatorios'])->name('tracking.verificar-recordatorios');
    Route::post('/{id}/completar', [TrackingController::class, 'completar'])->name('tracking.completar');
    Route::get('/vencidos/count', [TrackingController::class, 'countVencidos'])->name('tracking.vencidos.count');
});

// Tarifas
Route::post('tarifas-clientes', [TarifaClienteController::class, 'store'])->name('tarifas-clientes.store');
Route::delete('tarifas-clientes/{id}', [TarifaClienteController::class, 'destroy'])->name('tarifas-clientes.destroy');

// Historial de inventario
Route::get('logs-inventario', [LogInventarioController::class, 'index'])->name('logs_inventario.index');

// API AJAX para dashboard: estadísticas de paquetes
Route::middleware(['auth'])->get('/dashboard/estadisticas-paquetes', [DashboardController::class, 'estadisticasPaquetes'])->name('dashboard.estadisticas-paquetes');

// API AJAX para estadísticas por cliente con filtro de fechas y tipo de servicio
Route::middleware(['auth', 'role:admin,agente'])->get('/dashboard/estadisticas-paquetes-cliente', [App\Http\Controllers\DashboardController::class, 'estadisticasPaquetesCliente'])->name('dashboard.estadisticas-paquetes-cliente');
