<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índices para las búsquedas más frecuentes (paquetes por cliente, por guía,
 * por fecha y facturas por cliente/estado). En MySQL las FK ya crean algunos;
 * por eso se comprueba antes de crear cada uno.
 */
return new class extends Migration
{
    private array $indexes = [
        'inventario' => [
            'inventario_cliente_id_index' => ['cliente_id'],
            'inventario_tracking_codigo_index' => ['tracking_codigo'],
            'inventario_numero_guia_index' => ['numero_guia'],
            'inventario_fecha_ingreso_index' => ['fecha_ingreso'],
            'inventario_estado_index' => ['estado'],
            'inventario_factura_id_index' => ['factura_id'],
        ],
        'facturacion' => [
            'facturacion_cliente_id_index' => ['cliente_id'],
            'facturacion_estado_pago_index' => ['estado_pago'],
            'facturacion_fecha_factura_index' => ['fecha_factura'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (Schema::hasIndex($table, $columns) || Schema::hasIndex($table, $name)) {
                    continue;
                }
                Schema::table($table, fn (Blueprint $t) => $t->index($columns, $name));
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
                }
            }
        }
    }
};
