<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventario')) {
            Schema::table('inventario', function (Blueprint $table) {
                if (! Schema::hasColumn('inventario', 'proveedor_id')) {
                    $table->unsignedBigInteger('proveedor_id')->nullable()->after('factura_id');
                }
                if (! Schema::hasColumn('inventario', 'factura_proveedor_codigo')) {
                    $table->string('factura_proveedor_codigo', 80)->nullable()->after('proveedor_id');
                }
                if (! Schema::hasColumn('inventario', 'cxp_id')) {
                    $table->unsignedBigInteger('cxp_id')->nullable()->after('factura_proveedor_codigo');
                }
                if (! Schema::hasColumn('inventario', 'proveedor_pagado_at')) {
                    $table->timestamp('proveedor_pagado_at')->nullable()->after('cxp_id');
                }
            });

            Schema::table('inventario', function (Blueprint $table) {
                $table->index(['proveedor_id', 'factura_proveedor_codigo'], 'inventario_proveedor_factura_idx');
                $table->index('cxp_id');
                if (Schema::hasTable('proveedores')) {
                    $table->foreign('proveedor_id')->references('id')->on('proveedores')->nullOnDelete();
                }
                if (Schema::hasTable('conta_cxp')) {
                    $table->foreign('cxp_id')->references('id')->on('conta_cxp')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('conta_cxp') && ! Schema::hasColumn('conta_cxp', 'origen')) {
            Schema::table('conta_cxp', function (Blueprint $table) {
                $table->string('origen', 30)->default('manual')->after('estado_pago');
            });
        }

        if (Schema::hasTable('conta_pagos_proveedor') && ! Schema::hasColumn('conta_pagos_proveedor', 'comprobante_path')) {
            Schema::table('conta_pagos_proveedor', function (Blueprint $table) {
                $table->string('comprobante_path', 255)->nullable()->after('referencia');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inventario')) {
            Schema::table('inventario', function (Blueprint $table) {
                if (Schema::hasColumn('inventario', 'cxp_id')) {
                    $table->dropForeign(['cxp_id']);
                }
                if (Schema::hasColumn('inventario', 'proveedor_id')) {
                    $table->dropForeign(['proveedor_id']);
                }
            });
            Schema::table('inventario', function (Blueprint $table) {
                foreach (['proveedor_pagado_at', 'cxp_id', 'factura_proveedor_codigo', 'proveedor_id'] as $col) {
                    if (Schema::hasColumn('inventario', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
        if (Schema::hasTable('conta_cxp') && Schema::hasColumn('conta_cxp', 'origen')) {
            Schema::table('conta_cxp', function (Blueprint $table) {
                $table->dropColumn('origen');
            });
        }
        if (Schema::hasTable('conta_pagos_proveedor') && Schema::hasColumn('conta_pagos_proveedor', 'comprobante_path')) {
            Schema::table('conta_pagos_proveedor', function (Blueprint $table) {
                $table->dropColumn('comprobante_path');
            });
        }
    }
};
