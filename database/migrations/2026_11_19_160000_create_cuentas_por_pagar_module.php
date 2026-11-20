<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 180);
            $table->string('ruc', 40)->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('correo', 120)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('nombre');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('conta_cxp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proveedor_id');
            $table->string('numero_documento', 80)->nullable();
            $table->string('descripcion', 500);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->unsignedSmallInteger('dias_credito')->default(0);
            $table->decimal('monto_original', 14, 2)->default(0);
            $table->decimal('saldo_actual', 14, 2)->default(0);
            $table->string('moneda', 3)->default('USD');
            $table->decimal('tasa_cambio', 12, 4)->nullable();
            $table->unsignedBigInteger('cuenta_gasto_id');
            $table->string('estado_pago', 30)->default('al_dia');
            $table->unsignedBigInteger('asiento_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['proveedor_id', 'estado_pago']);
            $table->index('fecha_emision');
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->restrictOnDelete();
            $table->foreign('cuenta_gasto_id')->references('id')->on('conta_cuentas')->restrictOnDelete();
            $table->foreign('asiento_id')->references('id')->on('conta_asientos')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('conta_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 30)->unique();
            $table->string('tipo', 20);
            $table->date('fecha');
            $table->decimal('monto', 14, 2);
            $table->string('moneda', 3)->default('USD');
            $table->string('tercero_tipo', 20)->nullable();
            $table->unsignedBigInteger('tercero_id')->nullable();
            $table->string('tercero_nombre', 180);
            $table->string('concepto', 500);
            $table->string('metodo', 100)->nullable();
            $table->unsignedBigInteger('cuenta_banco_caja_id')->nullable();
            $table->string('referencia_tipo', 40)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->string('referencia', 120)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'fecha']);
            $table->index(['referencia_tipo', 'referencia_id']);
            $table->foreign('cuenta_banco_caja_id')->references('id')->on('conta_cuentas')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('conta_pagos_proveedor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cxp_id');
            $table->date('fecha_pago');
            $table->decimal('monto', 14, 2);
            $table->string('moneda', 3)->default('USD');
            $table->decimal('tasa_cambio', 12, 4)->nullable();
            $table->string('metodo', 100);
            $table->unsignedBigInteger('cuenta_banco_caja_id');
            $table->string('referencia', 120)->nullable();
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->unsignedBigInteger('asiento_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('cxp_id');
            $table->foreign('cxp_id')->references('id')->on('conta_cxp')->cascadeOnDelete();
            $table->foreign('cuenta_banco_caja_id')->references('id')->on('conta_cuentas')->restrictOnDelete();
            $table->foreign('voucher_id')->references('id')->on('conta_vouchers')->nullOnDelete();
            $table->foreign('asiento_id')->references('id')->on('conta_asientos')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        if (Schema::hasTable('conta_cobros') && ! Schema::hasColumn('conta_cobros', 'voucher_id')) {
            Schema::table('conta_cobros', function (Blueprint $table) {
                $table->unsignedBigInteger('voucher_id')->nullable()->after('comision');
                $table->foreign('voucher_id')->references('id')->on('conta_vouchers')->nullOnDelete();
            });
        }

        if (Schema::hasTable('conta_cuentas')) {
            $now = now();
            $exists = DB::table('conta_cuentas')->where('codigo', '2.1.02')->exists();
            if (! $exists) {
                DB::table('conta_cuentas')->insert([
                    'codigo' => '2.1.02',
                    'nombre' => 'Cuentas por Pagar Proveedores',
                    'tipo' => 'pasivo',
                    'subtipo' => 'cxp',
                    'acepta_movimiento' => 1,
                    'activa' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('conta_cobros') && Schema::hasColumn('conta_cobros', 'voucher_id')) {
            Schema::table('conta_cobros', function (Blueprint $table) {
                $table->dropForeign(['voucher_id']);
                $table->dropColumn('voucher_id');
            });
        }
        Schema::dropIfExists('conta_pagos_proveedor');
        Schema::dropIfExists('conta_vouchers');
        Schema::dropIfExists('conta_cxp');
        Schema::dropIfExists('proveedores');
    }
};
