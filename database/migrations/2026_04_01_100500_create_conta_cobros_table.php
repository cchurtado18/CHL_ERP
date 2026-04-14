<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conta_cobros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('factura_id');
            $table->date('fecha_pago');
            $table->decimal('monto', 14, 2);
            $table->enum('moneda', ['USD', 'NIO'])->default('USD');
            $table->decimal('tasa_cambio', 12, 4)->nullable();
            $table->string('metodo', 100);
            $table->unsignedBigInteger('cuenta_banco_caja_id');
            $table->string('referencia', 120)->nullable();
            $table->decimal('comision', 12, 2)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['fecha_pago', 'metodo']);
            $table->foreign('factura_id')->references('id')->on('facturacion')->cascadeOnDelete();
            $table->foreign('cuenta_banco_caja_id')->references('id')->on('conta_cuentas')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conta_cobros');
    }
};
