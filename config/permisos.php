<?php

/**
 * Catálogo de módulos del ERP.
 * Cada clave se guarda en users.permisos (JSON).
 * El rol "admin" siempre tiene acceso total (ignora esta lista).
 */
return [
    'modulos' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'descripcion' => 'Panel principal con estadísticas',
            'icono' => 'fa-tachometer-alt',
        ],
        'clientes' => [
            'label' => 'Clientes',
            'descripcion' => 'Altas, ediciones y consulta de clientes',
            'icono' => 'fa-users',
        ],
        'inventario' => [
            'label' => 'Inventario',
            'descripcion' => 'Paquetes, guías y salidas',
            'icono' => 'fa-box',
        ],
        'encomiendas' => [
            'label' => 'Encomiendas',
            'descripcion' => 'Encomiendas familiares y estados',
            'icono' => 'fa-people-carry-box',
        ],
        'remitentes' => [
            'label' => 'Remitentes',
            'descripcion' => 'Gestión de remitentes',
            'icono' => 'fa-user',
        ],
        'destinatarios' => [
            'label' => 'Destinatarios',
            'descripcion' => 'Gestión de destinatarios',
            'icono' => 'fa-map-marker-alt',
        ],
        'leads' => [
            'label' => 'Leads',
            'descripcion' => 'Agenda comercial y seguimiento',
            'icono' => 'fa-bullseye',
        ],
        'facturacion' => [
            'label' => 'Facturación',
            'descripcion' => 'Crear y administrar facturas',
            'icono' => 'fa-file-invoice',
        ],
        'tracking' => [
            'label' => 'Tracking',
            'descripcion' => 'Seguimiento de paquetes',
            'icono' => 'fa-search',
        ],
        'notificaciones' => [
            'label' => 'Notificaciones',
            'descripcion' => 'Alertas del sistema',
            'icono' => 'fa-bell',
        ],
        'usuarios' => [
            'label' => 'Usuarios',
            'descripcion' => 'Alta y permisos de usuarios',
            'icono' => 'fa-user-cog',
        ],
        'logs_inventario' => [
            'label' => 'Historial de inventario',
            'descripcion' => 'Auditoría de cambios en inventario',
            'icono' => 'fa-history',
        ],
        'contabilidad' => [
            'label' => 'Contabilidad (completa)',
            'descripcion' => 'Dashboard, CxC, asientos, cuentas y períodos',
            'icono' => 'fa-calculator',
        ],
        'contabilidad.cobros' => [
            'label' => 'Registrar cobros',
            'descripcion' => 'Solo registrar y consultar cobros (sin ser admin)',
            'icono' => 'fa-hand-holding-usd',
        ],
        'contabilidad.reportes' => [
            'label' => 'Reportes financieros',
            'descripcion' => 'Rentabilidad, gastos, parámetros y reporte ejecutivo',
            'icono' => 'fa-chart-line',
        ],
    ],

    /**
     * Permisos iniciales al migrar usuarios existentes según su rol.
     * admin no necesita lista: tiene acceso total.
     */
    'defaults_por_rol' => [
        'agente' => [
            'clientes', 'inventario', 'encomiendas', 'remitentes', 'destinatarios',
            'leads', 'facturacion', 'tracking', 'notificaciones',
        ],
        'auditor' => [
            'clientes', 'inventario', 'encomiendas', 'facturacion', 'tracking',
            'notificaciones', 'logs_inventario',
        ],
        'basico' => [
            'inventario', 'encomiendas', 'leads', 'notificaciones',
        ],
    ],
];
